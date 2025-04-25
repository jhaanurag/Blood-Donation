<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4>Enhanced Blood Donation Chat Assistant</h4>
                </div>
                <div class="card-body">
                    <div id="chat-container" class="mb-3" style="height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
                        <div class="message system-message">
                            <div class="message-content">
                                <p>Hello! I'm your blood donation assistant. How can I help you today?</p>
                            </div>
                        </div>
                    </div>
                    <div class="input-group">
                        <input type="text" id="user-message" class="form-control" placeholder="Ask something about blood donation...">
                        <div class="input-group-append">
                            <button id="send-button" class="btn btn-danger">
                                <i class="fas fa-paper-plane"></i> Send
                            </button>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">Powered by Google Gemini AI. Type 'clear' to reset the conversation.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .message {
        margin-bottom: 15px;
        max-width: 85%;
        clear: both;
    }
    
    .user-message {
        float: right;
        text-align: right;
    }
    
    .system-message {
        float: left;
    }
    
    .message-content {
        padding: 10px 15px;
        border-radius: 15px;
        display: inline-block;
    }
    
    .user-message .message-content {
        background-color: #dc3545; /* Bootstrap danger color */
        color: white;
    }
    
    .system-message .message-content {
        background-color: #f1f1f1;
        color: #333;
    }
    
    #typing-indicator {
        display: none;
        margin-bottom: 15px;
    }
    
    .typing-dots {
        display: inline-block;
        width: 50px;
        text-align: left;
    }
    
    @keyframes blink {
        50% { opacity: 1; }
    }
    
    .typing-dot {
        animation: blink 1s infinite;
        opacity: 0.2;
    }
    
    .typing-dot:nth-child(2) {
        animation-delay: 0.2s;
    }
    
    .typing-dot:nth-child(3) {
        animation-delay: 0.4s;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatContainer = document.getElementById('chat-container');
        const userMessage = document.getElementById('user-message');
        const sendButton = document.getElementById('send-button');
        let chatMessages = [];
        
        // Function to add a message to the chat display
        function addMessageToChat(content, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = isUser ? 'message user-message' : 'message system-message';
            
            const messageContent = document.createElement('div');
            messageContent.className = 'message-content';
            
            // Handle markdown-like formatting (simple version)
            let formattedContent = content
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/\n/g, '<br>');
            
            messageContent.innerHTML = `<p>${formattedContent}</p>`;
            messageDiv.appendChild(messageContent);
            chatContainer.appendChild(messageDiv);
            
            // Clear float with a div
            const clearDiv = document.createElement('div');
            clearDiv.style.clear = 'both';
            chatContainer.appendChild(clearDiv);
            
            // Scroll to the bottom
            chatContainer.scrollTop = chatContainer.scrollHeight;
            
            // Add to messages array for API
            chatMessages.push({
                role: isUser ? 'user' : 'system',
                content: content
            });
        }
        
        // Function to show typing indicator
        function showTypingIndicator() {
            const indicator = document.createElement('div');
            indicator.id = 'typing-indicator';
            indicator.className = 'message system-message';
            
            const content = document.createElement('div');
            content.className = 'message-content';
            content.innerHTML = `
                <span class="typing-dots">
                    <span class="typing-dot">.</span>
                    <span class="typing-dot">.</span>
                    <span class="typing-dot">.</span>
                </span>
            `;
            
            indicator.appendChild(content);
            chatContainer.appendChild(indicator);
            indicator.style.display = 'block';
            
            // Clear float
            const clearDiv = document.createElement('div');
            clearDiv.style.clear = 'both';
            chatContainer.appendChild(clearDiv);
            
            // Scroll to bottom
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
        
        // Function to hide typing indicator
        function hideTypingIndicator() {
            const indicator = document.getElementById('typing-indicator');
            if (indicator) {
                indicator.remove();
            }
        }
        
        // Function to handle sending a message
        function sendMessage() {
            const message = userMessage.value.trim();
            if (!message) return;
            
            // Clear the input
            userMessage.value = '';
            
            // Handle "clear" command
            if (message.toLowerCase() === 'clear') {
                chatContainer.innerHTML = '';
                chatMessages = [];
                addMessageToChat("Chat history cleared. How can I help you with blood donation today?", false);
                return;
            }
            
            // Add user message to chat
            addMessageToChat(message, true);
            
            // Show typing indicator
            showTypingIndicator();
            
            // Call our API
            fetch('gemini_route.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    messages: chatMessages
                }),
            })
            .then(response => {
                // For text streaming, we need to read the response as a stream
                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let partialResponse = '';
                
                // Hide the typing indicator once we start getting a response
                hideTypingIndicator();
                
                // Create a placeholder for the streaming response
                const responseDiv = document.createElement('div');
                responseDiv.className = 'message system-message';
                
                const responseContent = document.createElement('div');
                responseContent.className = 'message-content';
                responseContent.innerHTML = '<p></p>';
                
                responseDiv.appendChild(responseContent);
                chatContainer.appendChild(responseDiv);
                
                // Add clear div
                const clearDiv = document.createElement('div');
                clearDiv.style.clear = 'both';
                chatContainer.appendChild(clearDiv);
                
                // Process the stream
                function processText({ done, value }) {
                    if (done) {
                        // When done, add the complete message to our chat history
                        chatMessages.push({
                            role: 'system',
                            content: partialResponse
                        });
                        return;
                    }
                    
                    // Decode and append to the partial response
                    const chunk = decoder.decode(value, { stream: true });
                    partialResponse += chunk;
                    
                    // Format the markdown-like syntax
                    let formattedResponse = partialResponse
                        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                        .replace(/\*(.*?)\*/g, '<em>$1</em>')
                        .replace(/\n/g, '<br>');
                    
                    // Update the displayed response
                    responseContent.querySelector('p').innerHTML = formattedResponse;
                    
                    // Scroll to the bottom
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                    
                    // Continue reading
                    return reader.read().then(processText);
                }
                
                // Start processing the stream
                return reader.read().then(processText);
            })
            .catch(error => {
                hideTypingIndicator();
                console.error('Error:', error);
                addMessageToChat("I'm sorry, I encountered an error while processing your request. Please try again later.", false);
            });
        }
        
        // Event listeners
        sendButton.addEventListener('click', sendMessage);
        
        userMessage.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendMessage();
            }
        });
        
        // Focus the input field
        userMessage.focus();
    });
</script>

<?php
require_once '../includes/footer.php';
?>