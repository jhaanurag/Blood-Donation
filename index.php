<?php
include_once 'includes/header.php';
include_once 'includes/auth.php';
?>

<!-- Hero Section -->
<section class="bg-red-600 dark:bg-red-800 text-white py-12 md:py-16 px-4 md:px-8 rounded-lg shadow-lg mb-8">
    <div class="container mx-auto">
        <div class="flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 mb-8 md:mb-0 md:pr-6">
                <div class="typewriter mb-4">
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold" id="typewriter-text"></h1>
                </div>
                <p class="text-lg md:text-xl mb-6 max-w-xl">Your blood donation can save up to 3 lives. Join our community of donors today and make a difference.</p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="<?php echo $base_url; ?>register.php" class="bg-white dark:bg-red-900 text-red-600 dark:text-white font-bold px-6 py-3 rounded-md hover:bg-gray-100 dark:hover:bg-red-800 transition text-center shadow-md">Register as Donor</a>
                    <a href="<?php echo $base_url; ?>request.php" class="border-2 border-white dark:border-gray-300 text-white font-semibold px-6 py-3 rounded-md hover:bg-red-700/80 dark:hover:bg-red-900 transition text-center shadow-md">Request Blood</a>
                </div>
            </div>
            <div class="md:w-1/2">
                <img src="phot2.jpg" alt="Blood Donation" class="rounded-lg shadow-xl w-full h-auto max-w-md mx-auto">
            </div>
        </div>
    </div>
</section>

<style>
.typewriter {
  max-width: 100%;
  display: block;
}
.typewriter h1 {
  overflow: hidden;
  border-right: .1em solid orange;
  white-space: nowrap;
  margin: 0;
  letter-spacing: .02em;
  width: fit-content;
  min-height: 3rem;
  animation: blink-caret .75s step-end infinite;
}
@keyframes blink-caret {
  from, to { border-color: transparent }
  50% { border-color: orange; }
}

@media (max-width: 640px) {
  .typewriter h1 {
    font-size: 1.875rem; /* 30px */
    min-height: 2.5rem;
  }
}

/* Added animation styles for statistics */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}

/* Stats section specific styles */
.stats-section .glow-icon-container {
  width: 5rem;
  height: 5rem;
  border-radius: 9999px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.5rem;
  background-color: #fde7e7; /* Light pink in light mode */
  animation: glow-pulse 1.2s infinite ease-in-out;
}

.dark .stats-section .glow-icon-container {
  background-color: #374151; /* Dark gray in dark mode */
}

.stats-section .glow-icon {
  color: #E53E3E; /* Primary red color */
  font-size: 1.875rem;
}

@keyframes glow-pulse {
  0%, 100% {
    opacity: 1;
    box-shadow: 0 0 0 rgba(239, 68, 68, 0);
  }
  50% {
    opacity: 0.8;
    box-shadow: 0 0 20px rgba(239, 68, 68, 0.3);
  }
}

/* Regular icon container for non-stats sections */
.glow-icon-container {
  width: 5rem;
  height: 5rem;
  border-radius: 9999px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.5rem;
  background-color: #fde7e7; /* Light pink in light mode */
}

.dark .glow-icon-container {
  background-color: #374151; /* Dark gray in dark mode */
}

.glow-icon {
  color: #E53E3E; /* Primary red color */
  font-size: 1.875rem;
}

/* No hover effect for specific sections */
.no-hover-effect {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transition: none;
}

.no-hover-effect:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
  transform: none !important;
}

/* Transparent background classes */
.bg-transparent-section {
  background-color: transparent !important;
}

/* Card styles and animation effects */
.card {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.08);
  padding: 1.5rem;
  position: relative;
  overflow: hidden;
  z-index: 1;
  transition: transform 0.5s ease, box-shadow 0.3s ease, background-color 0.3s ease;
}

/* Dark mode for the card */
.dark .card {
  background: linear-gradient(135deg, #232336 80%, #181825 100%);
  box-shadow: 0 2px 16px rgba(0, 0, 0, 0.25);
  border: 1px solid #232336;
}

/* Card hover effect */
.card:hover {
  transform: translateY(-8px);
  box-shadow: 0 8px 25px rgba(239, 68, 68, 0.2);
}

/* Gradient overlay effect */
.card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, transparent 50%);
  z-index: -1;
  transition: opacity 0.5s ease;
  opacity: 0;
}

.card:hover::before {
  opacity: 1;
}

.animate-on-scroll {
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0.6s ease, transform 0.6s ease;
}

.animate-on-scroll.visible {
  opacity: 1;
  transform: translateY(0);
}

/* Additional styling for blood facts animation */
.blood-facts-wrapper {
    position: relative;
    overflow: hidden;
}

.blood-facts-list {
    position: relative;
}

.blood-fact {
    opacity: 0;
    transform: translateY(20px);
    position: relative;
    width: 100%;
    left: 0;
    transition: opacity 0.8s ease, transform 0.8s ease;
}

.blood-fact.active {
    opacity: 1;
    transform: translateY(0);
}

/* No hover effect for specific sections */
.no-hover {
  transform: none !important;
  transition: none !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.08) !important;
}

.no-hover:hover {
  transform: none !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.08) !important;
}

/* Blood usage stats section specific no-hover styles */
.blood-usage-stats {
  transform: none !important;
  transition: none !important;
}

.blood-usage-stats:hover {
  transform: none !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.08) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const text = "Give the Gift of Life";
  const el = document.getElementById('typewriter-text');
  let i = 0;
  let typing = true;

  function typeWriter() {
    if (!el) return; // Safety check
    
    if (typing) {
      if (i <= text.length) {
        el.textContent = text.substring(0, i);
        i++;
        setTimeout(typeWriter, 80);
      } else {
        typing = false;
        setTimeout(typeWriter, 1500);
      }
    } else {
      if (i >= 0) {
        el.textContent = text.substring(0, i);
        i--;
        setTimeout(typeWriter, 30);
      } else {
        typing = true;
        setTimeout(typeWriter, 500);
      }
    }
  }
  
  typeWriter();
  
  // Add counter animation for statistics
  function animateCounters() {
    const counters = document.querySelectorAll('.counter');
    const speed = 120; // The lower the value, the faster the animation
    
    counters.forEach(counter => {
      const target = +counter.getAttribute('data-target');
      const format = counter.getAttribute('data-format') || '';
      let count = 0;
      
      const updateCount = () => {
        const increment = Math.ceil(target / speed);
        
        if (count < target) {
          count += increment;
          if (count > target) count = target;
          
          if (format === 'comma') {
            counter.innerText = count.toLocaleString();
          } else if (format === 'plus') {
            counter.innerText = count.toLocaleString() + '+';
          } else {
            counter.innerText = count;
          }
          
          setTimeout(updateCount, 1);
        }
      };
      
      updateCount();
    });
  }
  
  // Trigger the animation when the element is in view
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCounters();
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });
  
  const statsSection = document.querySelector('.stats-section');
  if (statsSection) {
    observer.observe(statsSection);
  }

  // Add scroll animation for cards
  const cards = document.querySelectorAll('.animate-on-scroll');
  const cardObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        cardObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  cards.forEach(card => {
    cardObserver.observe(card);
  });

  // Animation for cards with staggered delay
  const animateCards = () => {
    // Intersection Observer configuration
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1 // Use a more common threshold like 0.1
    };
    
    // Select all cards with animate-on-scroll class
    const cards = document.querySelectorAll('.animate-on-scroll');
    let delay = 0;
    
    // Create the observer
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // When card comes into view
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            } else {
                // Optional: Reset if needed when scrolling out of view
                // entry.target.style.opacity = '0';
                // entry.target.style.transform = 'translateY(30px)';
            }
        });
    }, observerOptions);
    
    // Set initial styles and observe each card
    cards.forEach(card => {
        // Set initial hidden state
        card.style.opacity = '0'; // Correct initial opacity for fade-in
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'all 0.7s cubic-bezier(0.4, 0, 0.2, 1)';
        card.style.transitionDelay = `${delay}ms`;
        
        // Add hover effects but ensure we don't duplicate them
        // Tailwind classes like hover:scale-105 should ideally be in the HTML/CSS,
        // but adding dynamically is possible if needed.
        // if (!card.classList.contains('hover:scale-105')) {
        //     card.classList.add('hover:scale-105');
        // }
        
        // Observe the card
        observer.observe(card);
        
        // Increment delay for staggered animation
        delay += 100; // Increased delay for better visual staggering
    });
  };

  // Run the card animations
  animateCards();
});

// Blood Facts Animation Script
document.addEventListener('DOMContentLoaded', function() {
    // Blood facts animation
    const facts = document.querySelectorAll('.blood-fact');
    let currentStartIndex = 0;
    let factsContainer = document.querySelector('.blood-facts-wrapper');
    const factsPerGroup = 3; // Show 3 facts at a time
    
    if (facts.length > 0 && factsContainer) {
        // Set up all facts to be hidden initially
        facts.forEach((fact, index) => {
            fact.style.opacity = '0';
            fact.style.display = 'none';
            fact.style.position = 'absolute';
            fact.style.top = '0';
            fact.style.left = '0';
            fact.style.width = '100%';
        });
        
        // Show initial group of facts
        for (let i = 0; i < factsPerGroup; i++) {
            if (i < facts.length) {
                facts[i].style.opacity = '1';
                facts[i].style.position = 'relative';
                facts[i].style.display = 'block';
                facts[i].style.marginBottom = i < factsPerGroup - 1 ? '1rem' : '0'; // Add margin between facts
            }
        }
        
        // Function to rotate through fact groups
        function rotateFactGroups() {
            // Hide current facts with fade out
            for (let i = 0; i < factsPerGroup; i++) {
                const factIndex = (currentStartIndex + i) % facts.length;
                facts[factIndex].style.opacity = '0';
                facts[factIndex].style.transform = 'translateY(20px)';
            }
            
            setTimeout(() => {
                // After fade out, change display and position
                for (let i = 0; i < factsPerGroup; i++) {
                    const factIndex = (currentStartIndex + i) % facts.length;
                    facts[factIndex].style.display = 'none';
                    facts[factIndex].style.position = 'absolute';
                }
                
                // Move to next group of facts
                currentStartIndex = (currentStartIndex + factsPerGroup) % facts.length;
                
                // Show next group of facts with fade in
                for (let i = 0; i < factsPerGroup; i++) {
                    const factIndex = (currentStartIndex + i) % facts.length;
                    if (factIndex < facts.length) {
                        facts[factIndex].style.display = 'block';
                        facts[factIndex].style.position = 'relative';
                        facts[factIndex].style.marginBottom = i < factsPerGroup - 1 ? '1rem' : '0';
                        
                        setTimeout(() => {
                            facts[factIndex].style.opacity = '1';
                            facts[factIndex].style.transform = 'translateY(0)';
                        }, 50);
                    }
                }
            }, 800);
        }
        
        // Start rotation with interval
        setInterval(rotateFactGroups, 6000); // Slightly longer interval to give readers more time
    }
});
</script>

<!-- Statistics Section -->
<section class="py-12 md:py-16 bg-transparent-section stats-section">
  <div class="container mx-auto px-4">
    <div class="flex flex-wrap justify-center text-center">
      <div class="w-full md:w-1/3 p-4 md:p-6">
        <div class="flex justify-center">
          <div class="glow-icon-container">
            <i class="fas fa-tint glow-icon"></i>
          </div>
        </div>
        <div class="counter text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-2" data-target="50842" data-format="comma">0</div>
        <p class="text-gray-700 dark:text-gray-300 font-medium">Total Donations</p>
      </div>
      
      <div class="w-full md:w-1/3 p-4 md:p-6">
        <div class="flex justify-center">
          <div class="glow-icon-container">
            <i class="fas fa-heartbeat glow-icon"></i>
          </div>
        </div>
        <div class="counter text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-2" data-target="152526" data-format="comma">0</div>
        <p class="text-gray-700 dark:text-gray-300 font-medium">Lives Saved</p>
      </div>
      
      <div class="w-full md:w-1/3 p-4 md:p-6">
        <div class="flex justify-center">
          <div class="glow-icon-container">
            <i class="fas fa-hospital glow-icon"></i>
          </div>
        </div>
        <div class="counter text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-2" data-target="12" data-format="plus">0</div>
        <p class="text-gray-700 dark:text-gray-300 font-medium">Donation Centers</p>
      </div>
    </div>
  </div>
</section>

<!-- How It Works Section -->
<section class="py-12 md:py-16 bg-gray-50 dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12 dark:text-white">How It Works</h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-8">
            <!-- Step 1: Register -->
            <div class="text-center animate-on-scroll">
                <div class="flex justify-center mb-4">
                    <div class="glow-icon-container">
                        <i class="fas fa-user glow-icon"></i>
                    </div>
                </div>
                <h3 class="text-xl font-bold mb-2 dark:text-white">Register</h3>
                <p class="text-gray-600 dark:text-gray-300">Create your donor profile with your blood type and contact information.</p>
            </div>
            
            <!-- Step 2: Eligibility Check -->
            <div class="text-center animate-on-scroll">
                <div class="flex justify-center mb-4">
                    <div class="glow-icon-container">
                        <i class="fas fa-clipboard-check glow-icon"></i>
                    </div>
                </div>
                <h3 class="text-xl font-bold mb-2 dark:text-white">Eligibility Check</h3>
                <p class="text-gray-600 dark:text-gray-300">Complete a health questionnaire to ensure you're eligible to donate.</p>
            </div>
            
            <!-- Step 3: Schedule -->
            <div class="text-center animate-on-scroll">
                <div class="flex justify-center mb-4">
                    <div class="glow-icon-container">
                        <i class="fas fa-calendar-alt glow-icon"></i>
                    </div>
                </div>
                <h3 class="text-xl font-bold mb-2 dark:text-white">Schedule</h3>
                <p class="text-gray-600 dark:text-gray-300">Book an appointment at your preferred donation center and time.</p>
            </div>
            
            <!-- Step 4: Pre-Donation -->
            <div class="text-center animate-on-scroll">
                <div class="flex justify-center mb-4">
                    <div class="glow-icon-container">
                        <i class="fas fa-clock glow-icon"></i>
                    </div>
                </div>
                <h3 class="text-xl font-bold mb-2 dark:text-white">Pre-Donation</h3>
                <p class="text-gray-600 dark:text-gray-300">Arrive at the center, register, and undergo a mini health check.</p>
            </div>
            
            <!-- Step 5: Donate -->
            <div class="text-center animate-on-scroll">
                <div class="flex justify-center mb-4">
                    <div class="glow-icon-container">
                        <i class="fas fa-tint glow-icon"></i>
                    </div>
                </div>
                <h3 class="text-xl font-bold mb-2 dark:text-white">Donate</h3>
                <p class="text-gray-600 dark:text-gray-300">Complete your donation, which typically takes about 10-15 minutes.</p>
            </div>
            
            <!-- Step 6: Recovery -->
            <div class="text-center animate-on-scroll">
                <div class="flex justify-center mb-4">
                    <div class="glow-icon-container">
                        <i class="fas fa-check-circle glow-icon"></i>
                    </div>
                </div>
                <h3 class="text-xl font-bold mb-2 dark:text-white">Recovery</h3>
                <p class="text-gray-600 dark:text-gray-300">Rest and enjoy refreshments for 15 minutes after your donation.</p>
            </div>
            
            <!-- Step 7: Save Lives -->
            <div class="text-center animate-on-scroll">
                <div class="flex justify-center mb-4">
                    <div class="glow-icon-container">
                        <i class="fas fa-heart glow-icon"></i>
                    </div>
                </div>
                <h3 class="text-xl font-bold mb-2 dark:text-white">Save Lives</h3>
                <p class="text-gray-600 dark:text-gray-300">Your donation helps save lives and contributes to community health.</p>
            </div>
        </div>
    </div>
</section>

<!-- Blood Type Info Section -->
<section class="py-10 md:py-12 bg-transparent-section">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl md:text-3xl font-bold text-center mb-8 md:mb-12 dark:text-white">Blood Types & Compatibility</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            <?php
            $bloodTypes = [
                'A+' => ['Can donate to: A+, AB+', 'Can receive from: A+, A-, O+, O-'],
                'A-' => ['Can donate to: A+, A-, AB+, AB-', 'Can receive from: A-, O-'],
                'B+' => ['Can donate to: B+, AB+', 'Can receive from: B+, B-, O+, O-'],
                'B-' => ['Can donate to: B+, B-, AB+, AB-', 'Can receive from: B-, O-'],
                'AB+' => ['Can donate to: AB+', 'Can receive from: All blood types'],
                'AB-' => ['Can donate to: AB+, AB-', 'Can receive from: A-, B-, AB-, O-'],
                'O+' => ['Can donate to: A+, B+, AB+, O+', 'Can receive from: O+, O-'],
                'O-' => ['Can donate to: All blood types', 'Can receive from: O-']
            ];
            
            foreach ($bloodTypes as $type => $info) {
                echo '<div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md">';
                echo '<div class="text-center mb-4">';
                echo '<span class="inline-block bg-red-200 dark:bg-red-900 text-red-800 dark:text-red-200 text-2xl font-bold px-4 py-2 rounded">' . $type . '</span>';
                echo '</div>';
                echo '<p class="mb-2 font-semibold dark:text-white">' . $info[0] . '</p>';
                echo '<p class="dark:text-gray-200">' . $info[1] . '</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Why Donate Section -->
<section id="why-donate" class="py-16 bg-gray-50 dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12 dark:text-white">Why Donate Blood?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="card animate-on-scroll">
                <h3 class="text-xl font-semibold mb-4 dark:text-white">Save Lives</h3>
                <p class="text-gray-600 dark:text-gray-300">
                    A single donation can save up to 3 lives and help patients undergoing surgery, cancer treatment, and trauma care.
                </p>
            </div>
            <div class="card animate-on-scroll">
                <h3 class="text-xl font-semibold mb-4 dark:text-white">Health Benefits</h3>
                <p class="text-gray-600 dark:text-gray-300">
                    Regular blood donation can reduce the risk of heart disease and reveal potential health issues through the mini check-up.
                </p>
            </div>
            <div class="card animate-on-scroll">
                <h3 class="text-xl font-semibold mb-4 dark:text-white">Community Impact</h3>
                <p class="text-gray-600 dark:text-gray-300">
                    Your donation strengthens the community's health infrastructure and ensures blood is available in emergencies.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Blood Usage Statistics Section -->
<section class="py-16 bg-white dark:bg-gray-800">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12 dark:text-white">Blood Usage Statistics</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Blood Usage by Medical Need -->
            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg shadow-md animate-on-scroll blood-usage-stats no-hover">
                <h3 class="text-xl font-bold mb-6 dark:text-white">Blood Usage by Medical Need</h3>
                
                <div class="space-y-5">
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-700 dark:text-gray-300">Cancer Treatment</span>
                            <span class="text-gray-700 dark:text-gray-300 font-medium">34%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                            <div class="bg-red-600 h-2.5 rounded-full" style="width: 34%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-700 dark:text-gray-300">Trauma & Accidents</span>
                            <span class="text-gray-700 dark:text-gray-300 font-medium">25%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                            <div class="bg-red-600 h-2.5 rounded-full" style="width: 25%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-700 dark:text-gray-300">Surgery</span>
                            <span class="text-gray-700 dark:text-gray-300 font-medium">18%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                            <div class="bg-red-600 h-2.5 rounded-full" style="width: 18%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-700 dark:text-gray-300">Blood Disorders</span>
                            <span class="text-gray-700 dark:text-gray-300 font-medium">13%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                            <div class="bg-red-600 h-2.5 rounded-full" style="width: 13%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-700 dark:text-gray-300">Childbirth</span>
                            <span class="text-gray-700 dark:text-gray-300 font-medium">10%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                            <div class="bg-red-600 h-2.5 rounded-full" style="width: 10%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Blood Facts with Animation -->
            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg shadow-md animate-on-scroll blood-usage-stats no-hover relative" id="blood-facts-container">
                <h3 class="text-xl font-bold mb-6 dark:text-white">Blood Facts</h3>
                
                <div class="blood-facts-wrapper h-72 relative overflow-hidden">
                    <div class="blood-facts-list">
                        <div class="blood-fact mb-4">
                            <div class="flex items-start">
                                <div class="text-red-500 mr-3">•</div>
                                <p class="text-gray-700 dark:text-gray-300">Every 2 seconds, someone in the U.S. needs blood.</p>
                            </div>
                        </div>
                        <div class="blood-fact mb-4">
                            <div class="flex items-start">
                                <div class="text-red-500 mr-3">•</div>
                                <p class="text-gray-700 dark:text-gray-300">A single car accident victim can require as many as 100 units of blood.</p>
                            </div>
                        </div>
                        <div class="blood-fact mb-4">
                            <div class="flex items-start">
                                <div class="text-red-500 mr-3">•</div>
                                <p class="text-gray-700 dark:text-gray-300">Less than 38% of the population is eligible to donate blood.</p>
                            </div>
                        </div>
                        <div class="blood-fact mb-4">
                            <div class="flex items-start">
                                <div class="text-red-500 mr-3">•</div>
                                <p class="text-gray-700 dark:text-gray-300">Blood cannot be manufactured – it can only come from generous donors.</p>
                            </div>
                        </div>
                        <div class="blood-fact mb-4">
                            <div class="flex items-start">
                                <div class="text-red-500 mr-3">•</div>
                                <p class="text-gray-700 dark:text-gray-300">One donation can save up to three lives.</p>
                            </div>
                        </div>
                        <div class="blood-fact mb-4">
                            <div class="flex items-start">
                                <div class="text-red-500 mr-3">•</div>
                                <p class="text-gray-700 dark:text-gray-300">The average adult has about 10 pints of blood in their body.</p>
                            </div>
                        </div>
                        <div class="blood-fact mb-4">
                            <div class="flex items-start">
                                <div class="text-red-500 mr-3">•</div>
                                <p class="text-gray-700 dark:text-gray-300">A healthy donor may donate red blood cells every 56 days.</p>
                            </div>
                        </div>
                        <div class="blood-fact mb-4">
                            <div class="flex items-start">
                                <div class="text-red-500 mr-3">•</div>
                                <p class="text-gray-700 dark:text-gray-300">The shelf life of donated red blood cells is 42 days.</p>
                            </div>
                        </div>
                        <div class="blood-fact mb-4">
                            <div class="flex items-start">
                                <div class="text-red-500 mr-3">•</div>
                                <p class="text-gray-700 dark:text-gray-300">Plasma can be frozen and stored for up to one year.</p>
                            </div>
                        </div>
                        <div class="blood-fact mb-4">
                            <div class="flex items-start">
                                <div class="text-red-500 mr-3">•</div>
                                <p class="text-gray-700 dark:text-gray-300">Type O-negative blood is called the universal donor type because it is compatible with any blood type.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Upcoming Camps Preview -->
<section class="py-10 md:py-12 bg-transparent-section">
    <div class="container mx-auto px-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 md:mb-8">
            <h2 class="text-2xl md:text-3xl font-bold dark:text-white mb-2 sm:mb-0">Upcoming Blood Camps</h2>
            <a href="<?php echo $base_url; ?>camps.php" class="text-red-600 dark:text-red-400 hover:underline font-semibold flex items-center">
                View All <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <?php
        // Get upcoming blood camps (limit to 3)
        $query = "SELECT * FROM blood_camps WHERE date >= CURDATE() ORDER BY date ASC LIMIT 3";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) > 0) {
            echo '<div class="grid grid-cols-1 md:grid-cols-3 gap-6">';
            
            while ($camp = mysqli_fetch_assoc($result)) {
                $date = date("F j, Y", strtotime($camp['date']));
                
                echo '<div class="card animate-on-scroll overflow-hidden">';
                echo '<div class="p-6">';
                echo '<h3 class="font-bold text-xl mb-2 dark:text-white">' . htmlspecialchars($camp['title']) . '</h3>';
                echo '<div class="mb-4 flex items-center text-sm text-gray-600 dark:text-gray-300">';
                echo '<i class="far fa-calendar mr-2"></i>' . $date;
                echo '</div>';
                echo '<div class="mb-4 flex items-start text-sm text-gray-600 dark:text-gray-300">';
                echo '<i class="fas fa-map-marker-alt mr-2 mt-1"></i>';
                echo '<span>' . htmlspecialchars($camp['location']) . '<br>' . htmlspecialchars($camp['city']) . ', ' . htmlspecialchars($camp['state']) . '</span>';
                echo '</div>';
                echo '<p class="text-gray-700 dark:text-gray-200 mb-4">' . htmlspecialchars($camp['description']) . '</p>';
                echo '</div>';
                echo '</div>';
            }
            
            echo '</div>';
        } else {
            echo '<div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 text-yellow-800 dark:text-yellow-200 p-4 rounded">';
            echo '<p class="text-center">No upcoming blood donation camps at the moment. Please check back later.</p>';
            echo '</div>';
        }
        ?>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?>