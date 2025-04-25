<?php
// Define benefits - limit to 5
$benefits = [
    ["img" => "assets/benefits/ben1.png", "title" => "Saves Lives", "description" => "Save up to three lives."],
    ["img" => "assets/benefits/ben2.png", "title" => "Health Check-up", "description" => "Get a mini-health check"],
    ["img" => "assets/benefits/ben3.png", "title" => "Boosts Heart Health", "description" => "Helps regulate iron."],
    ["img" => "assets/benefits/ben4.png", "title" => "Feels Good to Help Others", "description" => "Increases happiness."],
    ["img" => "assets/benefits/ben5.png", "title" => "Stimulates Blood Cell Production", "description" => "Body makes fresh blood cells."],
];
$num_benefits = count($benefits); // This will be 5

// Base URL for sharing
// Assuming BASE_URL is defined elsewhere, e.g., in config.php if it were included
// Define it here if necessary for standalone operation
if (!defined('BASE_URL')) {
    // Basic guess for BASE_URL if not defined
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    // Assuming the script is in the root of Blood-Donation
    $script_path = dirname($_SERVER['SCRIPT_NAME']); // Gets /Blood-Donation or similar
    // Ensure it doesn't end with a slash unless it's the root
    $base_path = rtrim($script_path, '/');
    define('BASE_URL', $protocol . $host . $base_path);
}
$share_url = BASE_URL . '/share.php';
$share_title = urlencode("Discover the Benefits of Blood Donation & Join LifeFlow!");
$share_text = urlencode("Learn how donating blood saves lives and benefits your health. Join the LifeFlow community today! " . $share_url);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share the Benefits of Blood Donation</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.cdnfonts.com/css/ica-rubrik-black');
        @import url('https://fonts.cdnfonts.com/css/poppins');

        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body{
            background-color: #fde8e8; /* Light red/pink background */
            background-image:
            repeating-linear-gradient(
                to right, transparent 0 100px,
                #e5737322 100px 101px /* Subtle red grid lines */
            ),
            repeating-linear-gradient(
                to bottom, transparent 0 100px,
                #e5737322 100px 101px /* Subtle red grid lines */
            );
            color: #333; /* Default text color */
        }


        .banner{
            width: 100%;
            min-height: 80vh; /* Adjust height */
            text-align: center;
            overflow: hidden;
            position: relative;
            padding-top: 5vh; /* Add padding */
        }
        .banner .slider{
            position: absolute;
            width: 200px;
            height: 250px;
            top: 10%;
            left: calc(50% - 100px);
            transform-style: preserve-3d;
            transform: perspective(1000px);
            animation: autoRun 25s linear infinite; /* Slightly slower animation */
            z-index: 2;
        }
        @keyframes autoRun{
            from{
                transform: perspective(1000px) rotateX(-16deg) rotateY(0deg);
            }to{
                transform: perspective(1000px) rotateX(-16deg) rotateY(360deg);
            }
        }

        .banner .slider .item{
            position: absolute;
            inset: 0 0 0 0;
            transform:
                rotateY(calc( (var(--position) - 1) * (360 / var(--quantity)) * 1deg))
                translateZ(450px); /* Adjusted distance for 5 items */
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px;
        }
        .banner .slider .item img{
            width: 100%;
            height: 180px; /* Fixed image height */
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        /* Added styles for title/description inside item */
        .banner .slider .item h3 {
            font-family: Poppins, sans-serif;
            font-size: 1rem;
            font-weight: 600;
            color: #c62828; /* Red title */
            margin-bottom: 5px;
        }
        .banner .slider .item p {
            font-family: Poppins, sans-serif;
            font-size: 0.8rem;
            color: #666;
            text-align: center;
            padding: 0 5px;
        }

        .banner .content{
            position: relative; /* Changed from absolute */
            /* bottom: 0; */ /* Removed */
            /* left: 50%; */ /* Removed */
            /* transform: translateX(-50%); */ /* Removed */
            width: min(1400px, 95vw); /* Adjusted width */
            margin: 60vh auto 0 auto; /* Position below slider */
            height: max-content;
            padding-bottom: 50px; /* Reduced padding */
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            z-index: 1;
        }
        .banner .content h1{
            font-family: 'ICA Rubrik', sans-serif;
            font-size: clamp(4em, 15vw, 12em); /* Responsive font size */
            line-height: 1em;
            color: #c62828; /* Strong red color */
            position: relative;
            width: 100%; /* Make H1 take full width */
            text-align: center; /* Center H1 */
            margin-bottom: 20px; /* Add space below H1 */
        }
        .banner .content h1::after{
            position: absolute;
            inset: 0 0 0 0;
            content: attr(data-content);
            z-index: 2;
            -webkit-text-stroke: 2px #fde8e8; /* Light red/pink stroke */
            color: transparent;
        }
        .banner .content .author{
            font-family: Poppins, sans-serif;
            text-align: center; /* Center author text */
            width: 100%; /* Make author take full width */
            max-width: 400px; /* Limit width */
            margin: 0 auto; /* Center block */
            color: #8a0000; /* Dark red text */
        }
        .banner .content .author h2{
            font-size: 2em; /* Adjusted size */
            margin-bottom: 5px;
        }
         .banner .content .author p {
            font-size: 0.9em;
            line-height: 1.4;
         }

        /* Removed .banner .content .model rule referencing images/model.png */
        .banner .content .model{
            display: none; /* Hide the model div */
        }

        /* Share Section Styles */
        .share-section {
            text-align: center;
            padding: 3rem 1rem;
            background-color: #fff0f0; /* Lighter red background */
            border-radius: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin: 2rem auto;
            max-width: 900px; /* Limit width */
        }

        .share-section h2 {
            font-family: Poppins, sans-serif;
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #c62828; /* Red color */
        }
        .share-section .share-intro {
             font-family: Poppins, sans-serif;
             margin-bottom: 1.5rem;
             color: #555;
        }

        .share-buttons a {
            font-family: Poppins, sans-serif;
            display: inline-flex; /* Use inline-flex for alignment */
            align-items: center; /* Center icon and text vertically */
            justify-content: center; /* Center content horizontally */
            margin: 0.5rem;
            padding: 0.8rem 1.2rem;
            border-radius: 2rem; /* Pill shape */
            color: white;
            font-weight: 500;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            min-width: 120px; /* Ensure buttons have minimum width */
        }

        .share-buttons a:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .share-buttons a i {
            margin-right: 0.5rem; /* Space between icon and text */
            font-size: 1.2rem; /* Icon size */
        }

        /* Keeping original button colors as they are brand colors */
        .share-facebook { background-color: #1877F2; }
        .share-twitter { background-color: #1DA1F2; }
        .share-whatsapp { background-color: #25D366; }
        .share-linkedin { background-color: #0A66C2; }
        .share-email { background-color: #7f8c8d; } /* Neutral color for email */
        .share-copy { background-color: #f39c12; } /* Orange for copy link */

        #copy-status {
            font-family: Poppins, sans-serif;
            color: #2e7d32; /* Green color for success */
        }

        /* Responsive adjustments */
        @media screen and (max-width: 1023px) {
            .banner .slider{
                width: 160px;
                height: 200px;
                left: calc(50% - 80px);
            }
            .banner .slider .item{
                transform:
                    rotateY(calc( (var(--position) - 1) * (360 / var(--quantity)) * 1deg))
                    translateZ(300px);
            }
             .banner .slider .item img {
                height: 130px;
            }
            .banner .content{
                 margin-top: 55vh;
            }
            .banner .content h1{
                font-size: clamp(3em, 12vw, 7em);
            }
            .banner .content .author h2{
                font-size: 1.8em;
            }
        }
        @media screen and (max-width: 767px) {
            .banner .slider{
                width: 120px; /* Further reduce size */
                height: 160px;
                left: calc(50% - 60px);
            }
            .banner .slider .item{
                transform:
                    rotateY(calc( (var(--position) - 1) * (360 / var(--quantity)) * 1deg))
                    translateZ(220px); /* Adjust distance */
                 padding: 8px;
            }
             .banner .slider .item img {
                height: 100px; /* Adjust image height */
            }
             .banner .slider .item h3 {
                font-size: 0.9rem;
            }
            .banner .slider .item p {
                font-size: 0.7rem;
            }
             .banner .content{
                 margin-top: 50vh;
                 padding-bottom: 30px;
            }
            .banner .content h1{
                font-size: clamp(2.5em, 10vw, 5em);
            }
             .banner .content .author h2{
                font-size: 1.5em;
            }
            .share-section {
                padding: 2rem 1rem;
            }
            .share-section h2 {
                font-size: 1.5rem;
            }
            .share-buttons a {
                padding: 0.6rem 1rem;
                min-width: 100px;
                margin: 0.3rem;
            }
             .share-buttons a i {
                font-size: 1rem;
             }
        }

    </style>
    <!-- Removed <link rel="stylesheet" href="style.css"> -->
</head>
<body>

    <div class="banner">
        <div class="slider" style="--quantity: <?php echo $num_benefits; ?>"> <!-- Set quantity to 5 -->
            <?php foreach ($benefits as $index => $benefit): ?>
                <div class="item" style="--position: <?php echo $index + 1; ?>">
                    <img src="<?php echo htmlspecialchars($benefit['img']); ?>" alt="<?php echo htmlspecialchars($benefit['title']); ?>">
                    <h3><?php echo htmlspecialchars($benefit['title']); ?></h3>
                    <p><?php echo htmlspecialchars($benefit['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="content">
            <!-- Using placeholder content from your example, themed red -->
            <h1 data-content="DONATE BLOOD">
                DONATE BLOOD
            </h1>
            <div class="author">
                <h2>SAVE LIVES</h2>
                <p><b>Be a Hero</b></p>
                <p>
                    Share this page and encourage others to donate!
                </p>
            </div>
            <div class="model"></div> <!-- This div is now hidden via CSS -->
        </div>
    </div>

    <!-- Share Section -->
    <div class="share-section">
        <h2>Share the Lifesaving Message!</h2>
        <p class="share-intro">Encourage others to learn about blood donation and join our community.</p>
        <div class="share-buttons">
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($share_url); ?>&quote=<?php echo $share_text; ?>" target="_blank" class="share-facebook">
                <i class="fab fa-facebook-f"></i> Facebook
            </a>
            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($share_url); ?>&text=<?php echo $share_text; ?>" target="_blank" class="share-twitter">
                <i class="fab fa-twitter"></i> Twitter
            </a>
            <a href="https://wa.me/?text=<?php echo $share_text; ?>" target="_blank" class="share-whatsapp">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </a>
             <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($share_url); ?>" target="_blank" class="share-linkedin">
                <i class="fab fa-linkedin-in"></i> LinkedIn
            </a>
            <a href="mailto:?subject=<?php echo $share_title; ?>&body=<?php echo $share_text; ?>" class="share-email">
                <i class="fas fa-envelope"></i> Email
            </a>
             <a href="#" onclick="copyLink(event)" class="share-copy">
                <i class="fas fa-link"></i> Copy Link
            </a>
        </div>
         <p id="copy-status" class="text-sm mt-4" style="display: none;">Link copied to clipboard!</p>
    </div>

<script>
// Function to copy the share link to clipboard
function copyLink(event) {
    event.preventDefault(); // Prevent default anchor behavior
    const url = '<?php echo $share_url; ?>';
    navigator.clipboard.writeText(url).then(() => {
        // Success feedback
        const statusElement = document.getElementById('copy-status');
        statusElement.style.display = 'block';
        setTimeout(() => {
            statusElement.style.display = 'none';
        }, 2000); // Hide after 2 seconds
    }).catch(err => {
        console.error('Failed to copy link: ', err);
        // Optional: Show an error message to the user
        alert('Failed to copy link.');
    });
}
</script>

</body>
</html>
