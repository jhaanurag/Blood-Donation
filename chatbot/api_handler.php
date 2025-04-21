<?php
require_once 'config.php';
require_once dirname(__DIR__) . '/includes/db.php';

/**
 * Handles API requests to AI services (OpenAI/Gemini)
 * 
 * @param string $prompt The user query
 * @param string $context Additional context for the AI
 * @return string The AI response
 */
function getAIResponse($prompt, $context = '') {
    global $conn;
    
    // Check cache if enabled
    if (CACHE_ENABLED) {
        $cache_key = md5($prompt . $context);
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

    // Format the messages for the API
    $blood_donation_context = "You are a helpful blood donation assistant. Provide accurate information about blood donation. " .
                              "Keep answers concise and medically accurate. " . $context;

    $data = [];
    
    // Format based on API type (OpenAI or Gemini)
    if (strpos(AI_ENDPOINT, 'openai.com') !== false) {
        $data = [
            'model' => AI_MODEL,
            'messages' => [
                ['role' => 'system', 'content' => $blood_donation_context],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => MAX_TOKENS,
            'temperature' => 0.7
        ];
    } else {
        // Format for Gemini API
        $data = [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $blood_donation_context . "\n\nUser question: " . $prompt]]]
            ],
            'generationConfig' => [
                'maxOutputTokens' => MAX_TOKENS,
                'temperature' => 0.7
            ]
        ];
    }

    // API call
    $api_url = AI_ENDPOINT;
    
    // For Gemini API, append the key as a query parameter instead of using a bearer token
    if (strpos(AI_ENDPOINT, 'generativelanguage.googleapis.com') !== false) {
        $api_url = AI_ENDPOINT . '?key=' . AI_API_KEY;
        
        $headers = [
            'Content-Type: application/json'
        ];
    } else {
        // For OpenAI, use bearer token authentication
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . AI_API_KEY
        ];
    }
    
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // Improved error handling
    if ($http_status != 200 || !$response) {
        // Log the error for administrators
        error_log("Blood Donation Chatbot API Error: Status: $http_status, Error: $curl_error, Response: $response");
        
        // Provide a more helpful message to users based on error
        if (AI_API_KEY == 'YOUR_API_KEY_HERE') {
            return "The chatbot is not fully configured. Please contact the administrator to set up the API key for non-FAQ questions.";
        }
        
        if ($http_status == 401) {
            return "Authentication error with the AI service. Please contact the administrator to check the API key.";
        }
        
        if ($http_status == 429) {
            return "The AI service is currently busy. Please try asking your question again in a few moments, or check our FAQ section.";
        }
        
        return "Sorry, I couldn't generate a response at the moment. Please try asking a different question or check our FAQ section.";
    }

    $response_data = json_decode($response, true);
    $ai_response = '';

    // Add error checking for JSON parsing
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Blood Donation Chatbot JSON Parse Error: " . json_last_error_msg());
        return "Sorry, there was an error processing the response. Please try again later.";
    }
    
    // Extract response based on API type with better error checking
    try {
        if (strpos(AI_ENDPOINT, 'openai.com') !== false) {
            if (isset($response_data['choices'][0]['message']['content'])) {
                $ai_response = $response_data['choices'][0]['message']['content'];
            } else {
                throw new Exception("Invalid response structure from OpenAI");
            }
        } else {
            if (isset($response_data['candidates'][0]['content']['parts'][0]['text'])) {
                $ai_response = $response_data['candidates'][0]['content']['parts'][0]['text'];
            } else {
                throw new Exception("Invalid response structure from Gemini");
            }
        }
    } catch (Exception $e) {
        error_log("Blood Donation Chatbot Response Error: " . $e->getMessage());
        return "I encountered an issue understanding the response. Please try a different question.";
    }

    // Cache the response if caching is enabled
    if (CACHE_ENABLED) {
        $stmt = $conn->prepare("INSERT INTO ai_response_cache (cache_key, prompt, response) VALUES (?, ?, ?) 
                              ON DUPLICATE KEY UPDATE response = ?, created_at = CURRENT_TIMESTAMP");
        $stmt->bind_param("ssss", $cache_key, $prompt, $ai_response, $ai_response);
        $stmt->execute();
    }

    return $ai_response;
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