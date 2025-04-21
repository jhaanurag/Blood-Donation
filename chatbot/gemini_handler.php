<?php
require_once 'config.php';
require_once dirname(__DIR__) . '/includes/db.php';

/**
 * Handles API requests to Gemini 2.0 API
 * 
 * @param string $prompt The user query
 * @return string The AI response
 */
function getGeminiResponse($prompt) {
    global $conn;
    
    // Check cache if enabled
    if (CACHE_ENABLED) {
        $cache_key = md5($prompt);
        $stmt = $conn->prepare("SELECT response, created_at FROM ai_response_cache WHERE cache_key = ?");
        $stmt->bind_param("s", $cache_key);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $cache_time = strtotime($row['created_at']);
            
            // Return cached response if not expired
            if ((time() - $cache_time) < CACHE_EXPIRY) {
                return $row['response'];
            }
        }
    }

    // Prepare request body for Gemini 2.0
    $request_body = [
        'contents' => [
            [
                'role' => 'user',
                'parts' => [
                    [
                        'text' => $prompt
                    ]
                ]
            ]
        ],
        'systemInstruction' => [
            'parts' => [
                [
                    'text' => 'You are a blood donation chatbot. Help users with answers related to blood donation topics. Only provide information directly related to blood donation, donor eligibility, donation process, benefits, and associated medical information. Do not answer questions unrelated to blood donation or healthcare.'
                ]
            ]
        ],
        'generationConfig' => [
            'responseMimeType' => 'text/plain'
        ]
    ];

    // Set API parameters
    $model_id = 'gemini-2.0-flash-lite'; // Using the latest Gemini model
    $generate_api = 'streamGenerateContent';  // Using streaming endpoint

    // Create the API URL with the API key
    $api_url = "https://generativelanguage.googleapis.com/v1beta/models/{$model_id}:{$generate_api}?key=" . GEMINI_API_KEY;

    // Initialize cURL session
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_body));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    // Execute the request
    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // Handle errors
    if ($http_status != 200 || !$response) {
        error_log("Blood Donation Chatbot API Error: Status: $http_status, Error: $curl_error, Response: $response");
        
        if (GEMINI_API_KEY == 'YOUR_API_KEY_HERE') {
            return "The chatbot is not fully configured. Please contact the administrator to set up the API key.";
        }
        
        if ($http_status == 401 || $http_status == 403) {
            return "Authentication error with the AI service. Please contact the administrator to check the API key.";
        }
        
        if ($http_status == 429) {
            return "The AI service is currently busy. Please try asking your question again in a few moments.";
        }
        
        return "Sorry, I couldn't generate a response at the moment. Please try asking a different question.";
    }

    // Process the streaming response
    $complete_response = '';
    $response_chunks = explode("\n", $response);
    
    foreach ($response_chunks as $chunk) {
        if (empty($chunk)) continue;
        
        // Clean the chunk (remove "data: " prefix if present)
        if (strpos($chunk, 'data: ') === 0) {
            $chunk = substr($chunk, 6);
        }
        
        try {
            $json_chunk = json_decode($chunk, true);
            
            // Extract text from chunk if available
            if (isset($json_chunk['candidates'][0]['content']['parts'][0]['text'])) {
                $complete_response .= $json_chunk['candidates'][0]['content']['parts'][0]['text'];
            }
        } catch (Exception $e) {
            error_log("Error parsing JSON chunk: " . $e->getMessage());
        }
    }

    // If we couldn't extract any response, return an error message
    if (empty($complete_response)) {
        error_log("Failed to extract response from API: " . $response);
        return "I'm having trouble understanding your request. Please try phrasing your question differently.";
    }

    // Cache the response if caching is enabled
    if (CACHE_ENABLED) {
        $stmt = $conn->prepare("INSERT INTO ai_response_cache (cache_key, prompt, response) VALUES (?, ?, ?) 
                              ON DUPLICATE KEY UPDATE response = ?, created_at = CURRENT_TIMESTAMP");
        $stmt->bind_param("ssss", $cache_key, $prompt, $complete_response, $complete_response);
        $stmt->execute();
    }

    return $complete_response;
}

/**
 * Checks if a query matches any FAQ
 * 
 * @param string $query User query
 * @param array $faqs Array of FAQs
 * @return string|null The answer if found, null otherwise
 */
function matchFAQ($query, $faqs) {
    $query = strtolower(trim($query));
    foreach ($faqs as $question => $answer) {
        $similarity = similar_text(strtolower($question), $query, $percent);
        if ($percent > 80) {
            return $answer;
        }
        
        // Also check for keyword matches
        $keywords = explode(' ', strtolower($question));
        $matches = 0;
        foreach ($keywords as $keyword) {
            if (strlen($keyword) > 3 && strpos($query, $keyword) !== false) {
                $matches++;
            }
        }
        
        if ($matches >= 2) {
            return $answer;
        }
    }
    
    return null;
}
?>