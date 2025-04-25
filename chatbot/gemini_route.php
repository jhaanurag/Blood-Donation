<?php

use Google\Cloud\Core\Exception\BadRequestException;
use Google\Cloud\Core\Exception\ServiceException;
use Google\Cloud\VertexAI\VertexAIClient;

// Check if autoload.php exists and include it
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    // If Composer dependencies are not installed in this directory, check parent directory
    $parentAutoloadPath = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($parentAutoloadPath)) {
        require_once $parentAutoloadPath;
    } else {
        // If no autoload is available, exit with an error
        http_response_code(500);
        echo json_encode(['error' => 'Composer dependencies not installed. Please run "composer require google/cloud-vertexai" in the project root.']);
        exit;
    }
}

// Load environment variables and configuration
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../includes/db.php';

// Configuration - Adapt these based on your environment
$geminiApiKey = GEMINI_API_KEY; // Already defined in config.php
$projectId = getenv('GOOGLE_CLOUD_PROJECT') ?? 'your-google-cloud-project-id';
$location = getenv('GOOGLE_CLOUD_LOCATION') ?? 'us-central1';
$modelName = getenv('GEMINI_MODEL') ?? 'gemini-pro';

// Helper function to sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Function to create a specialized prompt
function createSpecializedPrompt($userLastMessage, $enhancedSystemPrompt) {
    // Blood donation specific prompt specialization
    $bloodDonationTerms = ['blood', 'donation', 'donor', 'hemoglobin', 'plasma', 'platelets',
                          'transfusion', 'eligibility', 'anemia', 'iron', 'type', 'donate'];
                          
    $containsBloodDonationTerms = false;
    foreach ($bloodDonationTerms as $term) {
        if (stripos($userLastMessage, $term) !== false) {
            $containsBloodDonationTerms = true;
            break;
        }
    }
    
    if ($containsBloodDonationTerms) {
        $enhancedSystemPrompt .= "\n\nThe user is asking about blood donation. Provide detailed and medically accurate information.";
    } 
    
    // Check for eligibility question patterns
    if (preg_match('/(can|eligible|allow).+(donate|give)/i', $userLastMessage) ||
        preg_match('/(requirements|criteria|qualification)/i', $userLastMessage)) {
        $enhancedSystemPrompt .= "\n\nThe user is asking about blood donation eligibility requirements. Be thorough but clear about general eligibility criteria.";
    }
    
    return $enhancedSystemPrompt . "\n\nUser Query: " . $userLastMessage;
}

// Function to fetch user medications from database
function getUserMedications($userEmail) {
    global $conn;
    
    // Using prepared statements to prevent SQL injection
    $medications = [];
    
    try {
        $stmt = $conn->prepare("SELECT m.name, m.dosage, m.frequency, m.purpose 
                               FROM user_medications m 
                               JOIN users u ON m.user_id = u.id 
                               WHERE u.email = ?");
        
        if ($stmt) {
            $stmt->bind_param("s", $userEmail);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $medications[] = $row;
            }
            
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("Database error in getUserMedications: " . $e->getMessage());
    }
    
    return $medications;
}

// Main API endpoint logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. Get and Validate Input
        $input = json_decode(file_get_contents('php://input'), true);
        $messages = $input['messages'] ?? [];
        if (empty($messages)) {
            http_response_code(400);
            echo json_encode(['error' => 'No messages provided.']);
            exit;
        }

        $userLastMessage = sanitizeInput(end($messages)['content'] ?? ''); 

        // 2. Session/Authentication
        // Using existing auth system in Blood-Donation project
        session_start();
        $userSession = [];
        if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {
            $userSession['user']['email'] = $_SESSION['email'];
        } elseif (isset($_COOKIE['user_email'])) {
            // Fallback to cookie if session not available
            $userSession['user']['email'] = sanitizeInput($_COOKIE['user_email']);
        }

        // 3. System Prompt & Context
        $enhancedSystemPrompt = "You are a helpful AI assistant specializing in providing information related to blood donation. You should be informative and answer the user's questions directly. If the user is asking about medications, and the information is not in the context, do not provide medication advice unless specifically asked to do so.";

        if (isset($userSession['user']['email'])) {
            $userEmail = $userSession['user']['email'];

            // Get user medications
            $medications = getUserMedications($userEmail);

            if (!empty($medications)) {
                $medicationList = "";
                foreach ($medications as $med) {
                    $medicationList .= "- " . $med['name'] . " (" . $med['dosage'] . ", " . $med['frequency'] . ")" . (isset($med['purpose']) ? " for " . $med['purpose'] : "") . "\n";
                }
                $enhancedSystemPrompt .= "\n\nThe user is currently taking the following medications:\n" . $medicationList . "\n\nYou may reference this information when relevant, but do not share this list unless specifically asked.";
            }
        }

        // 4. Create Specialized Prompt
        $specializedPrompt = createSpecializedPrompt($userLastMessage, $enhancedSystemPrompt);

        // Check if we have the necessary library
        if (!class_exists('Google\Cloud\VertexAI\VertexAIClient')) {
            // Use existing chatbot system as fallback
            require_once 'gemini_handler.php';
            $response = getGeminiResponse($userLastMessage);
            echo $response;
            exit;
        }

        // 5. Initialize Vertex AI Client
        $vertexAi = new VertexAIClient([
            'key' => $geminiApiKey,
            'projectId' => $projectId,
            'location' => $location,
        ]);

        // 6. Prepare Gemini Model
        $model = $vertexAi->initModel($modelName);

        // 7. Call the Gemini API
        try {
            $response = $model->generateContent([
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $specializedPrompt],
                        ],
                    ],
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $userLastMessage],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.4, // Lower temperature for more factual responses
                    'maxOutputTokens' => 2000,
                ],
            ]);

            $stream = $response->stream();

            // 8. Stream the Response
            header('Content-Type: text/plain; charset=utf-8');
            header('Transfer-Encoding: chunked'); // Enable chunked transfer encoding

            // Disable output buffering
            ob_end_flush(); // Flush any buffered output
            ob_implicit_flush(true); // Enable implicit flushing

            foreach ($stream as $chunk) {
                if (isset($chunk['text'])) {
                    echo $chunk['text'];
                    echo "\n"; // Add a newline character to the end of each chunk
                    flush();  // Flush the output buffer after each chunk
                }
            }

        } catch (ServiceException $e) {
            error_log("Gemini API error: " . $e->getMessage());

            if (strpos($e->getMessage(), 'API key not valid') !== false) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid API key. Please check your GEMINI_API_KEY.']);
            } elseif (strpos($e->getMessage(), 'model not found') !== false ||
                      strpos($e->getMessage(), 'not supported') !== false ||
                      strpos($e->getMessage(), 'not available') !== false) {
                http_response_code(400);
                echo json_encode(['error' => 'The selected model is not available. Please try again with a different model.']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'An error occurred processing your request. Please try again later.']);
            }
        } catch (BadRequestException $e) {
            error_log("Gemini API error: " . $e->getMessage());
            http_response_code(400);
            echo json_encode(['error' => 'Bad Request from Gemini API. Check your prompt and input.']);
        } catch (Exception $e) {
            error_log("General error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'An unexpected error occurred. Please try again later.']);
        }

    } catch (Exception $e) {
        error_log("API error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage() ?? "An error occurred processing your request."]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed. Only POST requests are supported.']);
}