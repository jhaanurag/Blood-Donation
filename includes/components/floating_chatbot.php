<!-- Floating Chatbot Component -->
<div id="floating-chatbot-container" style="position: fixed !important; bottom: 30px !important; right: 30px !important; z-index: 99999 !important; margin: 0 !important; padding: 0 !important; transform: none !important;">
    <!-- Chat Button -->
    <button id="chatbot-toggle-button" class="bg-red-600 hover:bg-red-700 text-white rounded-full w-16 h-16 flex items-center justify-center shadow-xl transition-all duration-300 focus:outline-none border-2 border-white dark:border-gray-700">
        <i class="fas fa-comment-dots text-2xl"></i>
    </button>

    <!-- Chat Panel (Hidden by Default) -->
    <div id="floating-chat-panel" class="hidden absolute bottom-20 right-0 w-80 sm:w-96 bg-white dark:bg-gray-800 rounded-lg shadow-2xl overflow-hidden transition-all duration-300 transform scale-95 opacity-0 border border-gray-200 dark:border-gray-700">
        <!-- Chat Header -->
        <div class="bg-gradient-to-r from-red-600 to-red-700 dark:from-red-700 dark:to-red-800 text-white p-4 flex justify-between items-center">
            <div>
                <h3 class="font-bold">Blood Donation Assistant</h3>
                <p class="text-xs text-red-100">Ask me anything about blood donation</p>
            </div>
            <button id="close-chat-button" class="text-white hover:text-red-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Chat Messages -->
        <div id="floating-chat-messages" class="bg-gray-50 dark:bg-gray-900 p-4 h-72 overflow-y-auto">
            <!-- Welcome Message -->
            <div class="flex mb-4">
                <span class="inline-block bg-red-500 rounded-full p-1 mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </span>
                <div class="bg-gray-300 dark:bg-gray-600 rounded-lg px-4 py-2 max-w-[75%]">
                    <p>Welcome to the Blood Donation Assistant! How can I help you today?</p>
                </div>
            </div>
        </div>
        
        <!-- Chat Input -->
        <form id="floating-chat-form" class="bg-white dark:bg-gray-800 p-4 border-t dark:border-gray-700">
            <div class="flex">
                <input 
                    type="text" 
                    id="floating-message-input" 
                    class="flex-grow border dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-l-lg px-4 py-2 focus:border-gray-400 focus:outline-none"
                    placeholder="Type your question..."
                    required
                >
                <button 
                    type="submit" 
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-r-lg transition duration-200"
                >
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Ensure chatbot container is always visible and positioned at the lower right */
    #floating-chatbot-container {
        position: fixed !important;
        bottom: 30px !important;
        right: 30px !important;
        z-index: 99999 !important;
        margin: 0 !important;
        padding: 0 !important;
        transform: none !important;
        pointer-events: auto !important;
    }
    
    /* Animations for the typing indicator */
    @keyframes blink {
        0% { opacity: 0.2; }
        20% { opacity: 1; }
        100% { opacity: 0.2; }
    }
    
    .typing-indicator .dot-1,
    .typing-indicator .dot-2,
    .typing-indicator .dot-3 {
        animation: blink 1.4s infinite;
        animation-fill-mode: both;
    }
    
    .typing-indicator .dot-2 { animation-delay: 0.2s; }
    .typing-indicator .dot-3 { animation-delay: 0.4s; }
    
    /* Transition for the chat panel */
    #floating-chat-panel {
        transition: all 0.3s ease;
    }
    
    /* Add a pulsing effect to the chat button to draw attention */
    @keyframes pulse {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(220, 38, 38, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
    }
    
    #chatbot-toggle-button {
        animation: pulse 2s infinite;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15), 0 0 0 2px rgba(255, 255, 255, 0.2);
    }
    
    /* Enhanced shadow for dark mode */
    html.dark #chatbot-toggle-button {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25), 0 0 0 2px rgba(255, 0, 0, 0.2);
    }
    
    /* Hide animation once the user has interacted with the button */
    #chatbot-toggle-button:hover,
    #chatbot-toggle-button:focus {
        animation: none;
        transform: scale(1.1);
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Floating Chatbot Functionality
        const chatToggleBtn = document.getElementById("chatbot-toggle-button");
        const closeBtn = document.getElementById("close-chat-button");
        const chatPanel = document.getElementById("floating-chat-panel");
        const chatForm = document.getElementById("floating-chat-form");
        const messageInput = document.getElementById("floating-message-input");
        const chatMessages = document.getElementById("floating-chat-messages");
        
        // Toggle chat panel visibility
        chatToggleBtn.addEventListener("click", function() {
            chatPanel.classList.toggle("hidden");
            
            // If showing the panel, add animation classes
            if (!chatPanel.classList.contains("hidden")) {
                // Use setTimeout to ensure the transition works
                setTimeout(() => {
                    chatPanel.classList.add("opacity-100", "scale-100");
                    chatPanel.classList.remove("opacity-0", "scale-95");
                    messageInput.focus(); // Focus on the input field
                }, 10);
            }
        });
        
        // Close the chat panel
        closeBtn.addEventListener("click", function() {
            chatPanel.classList.add("opacity-0", "scale-95");
            chatPanel.classList.remove("opacity-100", "scale-100");
            
            // Use setTimeout to match the transition duration
            setTimeout(() => {
                chatPanel.classList.add("hidden");
            }, 300);
        });
        
        // Handle form submission
        chatForm.addEventListener("submit", function(e) {
            e.preventDefault();
            const message = messageInput.value.trim();
            
            if (message) {
                // Add user message to the chat
                addMessageToChat("user", message);
                
                // Clear input
                messageInput.value = "";
                
                // Send to backend for processing
                sendMessage(message);
            }
        });
        
        // Add a message to the chat UI
        function addMessageToChat(sender, message) {
            const messageDiv = document.createElement("div");
            messageDiv.className = "mb-4";
            
            if (sender === "user") {
                messageDiv.innerHTML = `
                    <div class="flex justify-end">
                        <div class="bg-gray-200 dark:bg-gray-700 rounded-lg px-4 py-2 max-w-[75%]">
                            <p class="break-words whitespace-pre-wrap">${escapeHTML(message)}</p>
                        </div>
                        <span class="inline-block bg-gray-100 dark:bg-gray-700 rounded-full p-1 ml-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </span>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = `
                    <div class="flex">
                        <span class="inline-block bg-blue-500 rounded-full p-1 mr-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                        </span>
                        <div class="bg-gray-300 dark:bg-gray-600 rounded-lg px-4 py-2 max-w-[75%]">
                            <p class="break-words whitespace-pre-wrap">${message}</p>
                        </div>
                    </div>
                `;
            }
            
            chatMessages.appendChild(messageDiv);
            
            // Scroll to the bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        // Send message to the backend
        function sendMessage(message) {
            // Show typing indicator
            const typingDiv = document.createElement("div");
            typingDiv.className = "flex mb-4 typing-indicator";
            typingDiv.innerHTML = `
                <span class="inline-block bg-blue-500 rounded-full p-1 mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </span>
                <div class="bg-gray-300 dark:bg-gray-600 rounded-lg px-4 py-2">
                    <p>Typing<span class="dot-1">.</span><span class="dot-2">.</span><span class="dot-3">.</span></p>
                </div>
            `;
            chatMessages.appendChild(typingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            // Make the AJAX request
            fetch("<?php echo $base_url; ?>chatbot/ajax_chat_handler.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "message=" + encodeURIComponent(message)
            })
            .then(response => response.json())
            .then(data => {
                // Remove typing indicator
                const typingIndicator = document.querySelector(".typing-indicator");
                if (typingIndicator) {
                    typingIndicator.remove();
                }
                
                // Add bot response
                addMessageToChat("bot", data.response);
            })
            .catch(error => {
                // Remove typing indicator
                const typingIndicator = document.querySelector(".typing-indicator");
                if (typingIndicator) {
                    typingIndicator.remove();
                }
                
                // Show error message
                addMessageToChat("bot", "Sorry, I encountered an error. Please try again.");
                console.error("Error:", error);
            });
        }
        
        // Helper function to escape HTML and prevent XSS
        function escapeHTML(str) {
            return str.replace(/[&<>"']/g, function(match) {
                return {
                    "&": "&amp;",
                    "<": "&lt;",
                    ">": "&gt;",
                    '"': "&quot;",
                    "'": "&#39;"
                }[match];
            });
        }
    });
</script>