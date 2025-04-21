<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once 'gemini_handler.php';
include_once '../includes/header.php';

// Initialize or load the chat history from session
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

// Define common FAQs for blood donation
$faqs = [
    'Who can donate blood?' => 'Generally, donors must be at least 17 years old, weigh at least 110 pounds, and be in good health.',
    'How often can I donate?' => 'Whole blood donation can be done every 56 days (8 weeks).',
    'What blood types are most needed?' => 'All blood types are needed, but O negative is the universal donor type and always in high demand. AB positive is the universal recipient type.',
    'How long does donation take?' => 'The actual blood donation takes only 8-10 minutes, but the entire process including registration, mini-physical, and refreshments takes about an hour.',
    'Does blood donation hurt?' => 'Most people feel only a brief sting from the needle insertion. The donation process itself is typically painless.',
    'How much blood is taken?' => 'A typical whole blood donation is approximately one pint (about 470 ml).',
    'Are there any side effects?' => 'Some donors might experience mild side effects like lightheadedness, dizziness, or bruising at the needle site.',
    'What happens to my blood after donation?' => 'Your blood is tested, processed, and separated into components (red cells, platelets, plasma), then distributed to hospitals.',
    'Can I donate if I have a cold?' => 'No, you should be feeling well on the day of donation.',
    'Is it safe to donate during COVID-19?' => 'Yes, blood donation centers have implemented enhanced safety protocols to protect donors and staff.',
    'Can I donate if I have high blood pressure?' => 'You may be eligible if your blood pressure is within acceptable limits at the time of donation.',
    'Can I donate if I have diabetes?' => 'Yes, if your diabetes is well-controlled and you feel healthy.',
    'Can I donate if I have tattoos or piercings?' => 'Policies vary by location, but generally if the tattoo was done in a licensed facility and is fully healed, you can donate.',
    'What should I eat before donating blood?' => 'Have a healthy meal and drink plenty of fluids before donating.',
    'Will donating blood affect my athletic performance?' => 'It may temporarily impact intense exercise performance, so it\'s best to donate on a rest day.',
    'How quickly does my body replace donated blood?' => 'Your body replaces plasma within 24 hours. Red blood cells are replaced within 4-6 weeks.',
    'What is the difference between whole blood donation and platelet donation?' => 'Whole blood donation collects all blood components, while platelet donation specifically collects platelets through a process called apheresis.',
    'Can I donate if I\'ve recently traveled abroad?' => 'Travel to certain countries may temporarily defer donation due to risk of infections like malaria.',
    'What medications prevent blood donation?' => 'Blood thinners, certain acne medications like isotretinoin, and some other prescriptions may prevent donation.',
    'How will I feel after donating?' => 'Most people feel fine after donating. It\'s recommended to have a snack, drink extra fluids, and avoid strenuous activity for the rest of the day.'
];

// Process the message if form submitted
$response = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $user_message = trim($_POST['message']);
    
    if (!empty($user_message)) {
        // First check against FAQs
        $faq_response = matchFAQ($user_message, $faqs);
        
        if ($faq_response) {
            $response = $faq_response;
        } else {
            // If no FAQ match, use the new Gemini 2.0 API
            $response = getGeminiResponse($user_message);
        }
        
        // Add to history
        $_SESSION['chat_history'][] = [
            'user' => $user_message,
            'bot' => $response
        ];
    }
}
?>

<div class="container mx-auto px-4 py-8 max-w-6xl">
    <h1 class="text-3xl font-bold text-red-700 mb-6">Blood Donation Assistant</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Chatbot Section -->
        <div class="md:col-span-2">
            <!-- Chat Box - Simple Fixed Height Approach -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-4">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Chat with our Blood Donation Assistant</h2>
                
                <div class="chat-container bg-gray-50 dark:bg-gray-900 rounded-lg p-4 mb-4" style="height: 400px; overflow-y: auto;">
                    <?php if (empty($_SESSION['chat_history'])): ?>
                        <div class="text-center text-gray-500 dark:text-gray-400 py-20">
                            <p>Welcome to the Blood Donation Assistant!</p>
                            <p>Ask me anything about blood donation.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($_SESSION['chat_history'] as $chat): ?>
                            <!-- User Message -->
                            <div class="mb-4">
                                <div class="flex">
                                    <span class="inline-block bg-gray-100 dark:bg-gray-700 rounded-full p-1 mr-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </span>
                                    <div class="bg-gray-200 dark:bg-gray-700 rounded-lg px-4 py-2 max-w-[75%]">
                                        <p class="break-words whitespace-pre-wrap"><?= htmlspecialchars($chat['user']) ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bot Message -->
                            <div class="mb-4">
                                <div class="flex justify-end">
                                    <div class="bg-gray-300 dark:bg-gray-600 rounded-lg px-4 py-2 max-w-[75%]">
                                        <p class="break-words whitespace-pre-wrap"><?= nl2br(htmlspecialchars($chat['bot'])) ?></p>
                                    </div>
                                    <span class="inline-block bg-blue-500 rounded-full p-1 ml-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Message Form (Outside the main box) -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4">
                <form method="post" action="" id="chat-form">
                    <div class="flex">
                        <input 
                            type="text" 
                            name="message" 
                            id="message-input" 
                            class="flex-grow border rounded-l-lg px-4 py-2 focus:border-gray-400 focus:outline-none"
                            placeholder="Ask a question about blood donation..."
                            required
                        >
                        <button 
                            type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-r-lg transition duration-200"
                        >
                            Send
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Clear Chat Button -->
            <div class="mt-2 text-right">
                <form method="post" action="clear_chat.php">
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                        Clear Conversation
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Common Questions Section -->
        <div class="md:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Frequently Asked Questions</h2>
                <ul class="space-y-2">
                    <li><a href="#" class="faq-question text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100">Who can donate blood?</a></li>
                    <li><a href="#" class="faq-question text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100">How often can I donate?</a></li>
                    <li><a href="#" class="faq-question text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100">What blood types are most needed?</a></li>
                    <li><a href="#" class="faq-question text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100">How long does donation take?</a></li>
                    <li><a href="#" class="faq-question text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100">Does blood donation hurt?</a></li>
                    <li><a href="#" class="faq-question text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100">Is it safe to donate during COVID-19?</a></li>
                </ul>
                
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mt-6 mb-3">Need more help?</h3>
                <p class="text-gray-600 dark:text-gray-300">Ask our AI assistant about any blood donation topic, or try the <a href="eligibility.php" class="text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100">Eligibility Predictor</a> to check if you can donate.</p>
            </div>
        </div>
    </div>
</div>

<style>
    /* Remove all hover effects on box borders specifically for chatbot page */
    .rounded-lg:focus, input:focus, button:focus, a:focus, a:hover,
    .bg-white:hover, .shadow-lg:hover, .dark\:bg-gray-800:hover,
    div:hover, .p-6:hover, .p-4:hover, .mb-4:hover,
    .chat-container:hover, form:hover,
    .grid:hover, .md\:col-span-1:hover, .md\:col-span-2:hover {
        outline: none !important;
        box-shadow: none !important;
        border-color: inherit !important;
        --tw-ring-color: transparent !important;
        --tw-ring-shadow: none !important;
        --tw-shadow: none !important;
    }
    
    /* Force remove any hover state styles on containers */
    .bg-white, .bg-gray-50, .dark\:bg-gray-800, .dark\:bg-gray-900,
    .rounded-lg, .shadow-lg, .p-6, .p-4, .mb-4 {
        transition: none !important;
    }
    
    /* Remove hover effects from input and button but preserve their functionality */
    input:hover, button.bg-blue-600:hover {
        box-shadow: none !important;
        border-color: inherit !important;
    }
    
    /* Override any Tailwind hover effects */
    .hover\:shadow:hover,
    .hover\:shadow-md:hover,
    .hover\:shadow-lg:hover,
    .hover\:shadow-xl:hover,
    .hover\:shadow-2xl:hover,
    .hover\:bg-gray-100:hover,
    .hover\:bg-gray-200:hover,
    .hover\:border:hover,
    .hover\:border-gray-300:hover {
        box-shadow: none !important;
        background-color: inherit !important;
        border-color: inherit !important;
    }
    
    /* Ensure proper text wrapping in chat */
    .break-words {
        word-wrap: break-word; 
        overflow-wrap: break-word;
        word-break: break-word;
    }
    
    /* Fixes for Firefox scrolling */
    .chat-container {
        scrollbar-width: thin;
        scrollbar-color: #CBD5E0 transparent;
    }
    
    /* Fixes for Chrome scrolling */
    .chat-container::-webkit-scrollbar {
        width: 6px;
    }
    
    .chat-container::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .chat-container::-webkit-scrollbar-thumb {
        background-color: #CBD5E0;
        border-radius: 20px;
    }
    
    /* Remove border highlight on button click */
    button:active, button:focus {
        outline: none !important;
        box-shadow: none !important; 
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to scroll chat to bottom
    function scrollChatToBottom() {
        const chatContainer = document.querySelector('.chat-container');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    }
    
    // Scroll on page load
    scrollChatToBottom();
    
    // Handle FAQ question clicks
    document.querySelectorAll('.faq-question').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('message-input').value = this.textContent;
            document.getElementById('chat-form').submit();
        });
    });
    
    // Add submit event listener to scroll after form submission
    document.getElementById('chat-form').addEventListener('submit', function() {
        // Use timeout to ensure DOM is updated first
        setTimeout(scrollChatToBottom, 100);
    });
    
    // Modify the event listeners to exclude input fields
    document.querySelectorAll('div, a, button, form').forEach(function(element) {
        element.addEventListener('mouseenter', function() {
            // Explicitly remove any border or shadow effects
            this.style.boxShadow = 'none';
            this.style.outline = 'none';
        });
    });
    
    // Apply the blur only to non-input elements
    document.querySelectorAll('div, a, button, form').forEach(function(element) {
        if (element.tagName !== 'INPUT' && !element.querySelector('input')) {
            element.addEventListener('click', function(e) {
                // Only blur if we're not clicking on an input element
                if (e.target.tagName !== 'INPUT') {
                    this.blur();
                }
            });
        }
    });
    
    // Ensure the input field can maintain focus
    document.getElementById('message-input').addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent the click from bubbling up
        this.focus();
    });
    
    // Override any Tailwind hover classes that might be added
    document.querySelectorAll('[class*="hover\\:"]').forEach(function(element) {
        element.classList.remove('hover:shadow', 'hover:shadow-md', 'hover:shadow-lg');
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>