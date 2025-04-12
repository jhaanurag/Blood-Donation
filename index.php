<?php
include_once 'includes/header.php';
include_once 'includes/auth.php';
?>

<!-- Hero Section -->
<section class="bg-red-600 text-white py-16">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 mb-8 md:mb-0">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Give the Gift of Life</h1>
                <p class="text-xl mb-6">Your blood donation can save up to 3 lives. Join our community of donors today and make a difference.</p>
                <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                    <a href="/register.php" class="bg-white text-red-600 font-semibold px-6 py-3 rounded-md hover:bg-gray-100 transition text-center">Register as Donor</a>
                    <a href="/request.php" class="border-2 border-white text-white font-semibold px-6 py-3 rounded-md hover:bg-red-700 transition text-center">Request Blood</a>
                </div>
            </div>
            <div class="md:w-1/2">
                <img src="https://placehold.co/600x400/red/white?text=Blood+Donation" alt="Blood Donation" class="rounded-lg shadow-xl">
            </div>
        </div>
    </div>
</section>

<!-- Blood Type Info Section -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Blood Types & Compatibility</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
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
                echo '<div class="bg-white p-6 rounded-lg shadow-md">';
                echo '<div class="text-center mb-4">';
                echo '<span class="inline-block bg-red-200 text-red-800 text-2xl font-bold px-4 py-2 rounded">' . $type . '</span>';
                echo '</div>';
                echo '<p class="mb-2 font-semibold">' . $info[0] . '</p>';
                echo '<p>' . $info[1] . '</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Why Donate Section -->
<section class="py-12 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Why Donate Blood?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="text-red-600 text-4xl mb-4 text-center">
                    <i class="fas fa-heart"></i>
                </div>
                <h3 class="text-xl font-semibold mb-3 text-center">Save Lives</h3>
                <p class="text-gray-700">One donation can save up to 3 lives. Blood is needed every 2 seconds for emergencies and regular treatments.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="text-red-600 text-4xl mb-4 text-center">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h3 class="text-xl font-semibold mb-3 text-center">Health Benefits</h3>
                <p class="text-gray-700">Regular blood donation can reduce the risk of heart disease and reveal potential health issues through free mini check-ups.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="text-red-600 text-4xl mb-4 text-center">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
                <h3 class="text-xl font-semibold mb-3 text-center">Community Support</h3>
                <p class="text-gray-700">Your donation helps patients fighting cancer, chronic diseases, and traumatic injuries in your local community.</p>
            </div>
        </div>
    </div>
</section>

<!-- Upcoming Camps Preview -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold">Upcoming Blood Camps</h2>
            <a href="/camps.php" class="text-red-600 hover:underline font-semibold">View All <i class="fas fa-arrow-right ml-1"></i></a>
        </div>
        
        <?php
        // Get upcoming blood camps (limit to 3)
        $query = "SELECT * FROM blood_camps WHERE date >= CURDATE() ORDER BY date ASC LIMIT 3";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) > 0) {
            echo '<div class="grid grid-cols-1 md:grid-cols-3 gap-6">';
            
            while ($camp = mysqli_fetch_assoc($result)) {
                $date = date("F j, Y", strtotime($camp['date']));
                
                echo '<div class="bg-white rounded-lg shadow-md overflow-hidden">';
                echo '<div class="p-6">';
                echo '<h3 class="font-bold text-xl mb-2">' . htmlspecialchars($camp['title']) . '</h3>';
                echo '<div class="mb-4 flex items-center text-sm text-gray-600">';
                echo '<i class="far fa-calendar mr-2"></i>' . $date;
                echo '</div>';
                echo '<div class="mb-4 flex items-start text-sm text-gray-600">';
                echo '<i class="fas fa-map-marker-alt mr-2 mt-1"></i>';
                echo '<span>' . htmlspecialchars($camp['location']) . '<br>' . htmlspecialchars($camp['city']) . ', ' . htmlspecialchars($camp['state']) . '</span>';
                echo '</div>';
                echo '<p class="text-gray-700 mb-4">' . htmlspecialchars($camp['description']) . '</p>';
                echo '</div>';
                echo '</div>';
            }
            
            echo '</div>';
        } else {
            echo '<div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded">';
            echo '<p class="text-center">No upcoming blood donation camps at the moment. Please check back later.</p>';
            echo '</div>';
        }
        ?>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?>