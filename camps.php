<?php
include_once 'includes/header.php';
include_once 'includes/auth.php';
include_once 'includes/db.php';

// Get all upcoming blood camps
$query = "SELECT * FROM blood_camps WHERE date >= CURDATE() ORDER BY date ASC";
$result = mysqli_query($conn, $query);
?>

<div class="bg-gray-100 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold text-center mb-10">Upcoming Blood Donation Camps</h1>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while ($camp = mysqli_fetch_assoc($result)): ?>
                    <?php $date = date("F j, Y", strtotime($camp['date'])); ?>
                    
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="p-6">
                            <h3 class="font-bold text-xl mb-2"><?= htmlspecialchars($camp['title']) ?></h3>
                            <div class="mb-4 flex items-center text-sm text-gray-600">
                                <i class="far fa-calendar mr-2"></i><?= $date ?>
                            </div>
                            <div class="mb-4 flex items-start text-sm text-gray-600">
                                <i class="fas fa-map-marker-alt mr-2 mt-1"></i>
                                <span>
                                    <?= htmlspecialchars($camp['location']) ?><br>
                                    <?= htmlspecialchars($camp['city']) ?>, <?= htmlspecialchars($camp['state']) ?>
                                </span>
                            </div>
                            <p class="text-gray-700 mb-4"><?= htmlspecialchars($camp['description']) ?></p>
                            
                            <?php if (is_donor_logged_in()): ?>
                                <a href="/dashboard/appointments.php?camp_id=<?= $camp['id'] ?>" class="block text-center bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700 transition">
                                    Book Appointment
                                </a>
                            <?php else: ?>
                                <a href="/login.php" class="block text-center bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700 transition">
                                    Login to Book Appointment
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-6 rounded text-center">
                <p class="text-lg">No upcoming blood donation camps at the moment.</p>
                <p>Please check back later or <a href="/register.php" class="text-red-600 hover:underline">register</a> to be notified when new camps are scheduled.</p>
            </div>
        <?php endif; ?>
        
        <div class="mt-10 bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-4">What to Expect at a Blood Donation Camp</h2>
            
            <div class="space-y-4">
                <div class="flex items-start">
                    <div class="bg-red-100 text-red-600 p-3 rounded-full mr-4">
                        <span class="font-bold">1</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg">Registration</h3>
                        <p class="text-gray-700">You'll need to fill out a form and show identification. Your basic information will be recorded.</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="bg-red-100 text-red-600 p-3 rounded-full mr-4">
                        <span class="font-bold">2</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg">Health Screening</h3>
                        <p class="text-gray-700">A healthcare professional will check your pulse, blood pressure, temperature, and hemoglobin levels.</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="bg-red-100 text-red-600 p-3 rounded-full mr-4">
                        <span class="font-bold">3</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg">Donation Process</h3>
                        <p class="text-gray-700">The actual donation takes only about 8-10 minutes, during which approximately 450ml of blood is collected.</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="bg-red-100 text-red-600 p-3 rounded-full mr-4">
                        <span class="font-bold">4</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg">Recovery</h3>
                        <p class="text-gray-700">After donating, you'll be given refreshments and asked to rest for 15 minutes before leaving.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>