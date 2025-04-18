<?php
include_once 'includes/header.php';
include_once 'includes/auth.php';
include_once 'includes/db.php';

$query = "SELECT * FROM blood_camps WHERE date >= CURDATE() ORDER BY date ASC";
$result = mysqli_query($conn, $query);
?>

<div class="bg-gray-100 dark:bg-gray-800 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold text-center mb-10 dark:text-white">Upcoming Blood Donation Camps</h1>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while ($camp = mysqli_fetch_assoc($result)): ?>
                    <?php $date = date("F j, Y", strtotime($camp['date'])); ?>
                    
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden">
                        <div class="p-6">
                            <h3 class="font-bold text-xl mb-2 dark:text-white"><?= htmlspecialchars($camp['title']) ?></h3>
                            <div class="mb-4 flex items-center text-sm text-gray-600 dark:text-gray-300">
                                <i class="far fa-calendar mr-2"></i><?= $date ?>
                            </div>
                            <div class="mb-4 flex items-start text-sm text-gray-600 dark:text-gray-300">
                                <i class="fas fa-map-marker-alt mr-2 mt-1"></i>
                                <span>
                                    <?= htmlspecialchars($camp['location']) ?><br>
                                    <?= htmlspecialchars($camp['city']) ?>, <?= htmlspecialchars($camp['state']) ?>
                                </span>
                            </div>
                            <p class="text-gray-700 dark:text-gray-200 mb-4"><?= htmlspecialchars($camp['description']) ?></p>

                            <div class="mb-4">
                                <iframe
                                    width="100%"
                                    height="200"
                                    style="border:0"
                                    loading="lazy"
                                    allowfullscreen
                                    referrerpolicy="no-referrer-when-downgrade"
                                    src="https://maps.google.com/maps?q=<?= urlencode($camp['location'] . ', ' . $camp['city'] . ', ' . $camp['state']) ?>&output=embed&z=15">
                                </iframe>
                            </div>

                            <?php if (is_donor_logged_in()): ?>
                                <a href="<?php echo BASE_URL; ?>/dashboard/appointments.php?camp_id=<?= $camp['id'] ?>" class="block text-center bg-red-600 dark:bg-red-700 text-white py-2 px-4 rounded hover:bg-red-700 dark:hover:bg-red-800 transition">
                                    Book Appointment
                                </a>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>/login.php" class="block text-center bg-red-600 dark:bg-red-700 text-white py-2 px-4 rounded hover:bg-red-700 dark:hover:bg-red-800 transition">
                                    Login to Book Appointment
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 text-yellow-800 dark:text-yellow-200 p-6 rounded text-center">
                <p class="text-lg">No upcoming blood donation camps at the moment.</p>
                <p>Please check back later or <a href="<?php echo BASE_URL; ?>/register.php" class="text-red-600 dark:text-red-400 hover:underline">register</a> to be notified when new camps are scheduled.</p>
            </div>
        <?php endif; ?>
        
        <div class="mt-10 bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-4 dark:text-white">What to Expect at a Blood Donation Camp</h2>
            
            <div class="space-y-4">
                <div class="flex items-start">
                    <div class="bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-200 p-3 rounded-full mr-4">
                        <span class="font-bold">1</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg dark:text-white">Registration</h3>
                        <p class="text-gray-700 dark:text-gray-200">You'll need to fill out a form and show identification. Your basic information will be recorded.</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-200 p-3 rounded-full mr-4">
                        <span class="font-bold">2</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg dark:text-white">Health Screening</h3>
                        <p class="text-gray-700 dark:text-gray-200">A healthcare professional will check your pulse, blood pressure, temperature, and hemoglobin levels.</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-200 p-3 rounded-full mr-4">
                        <span class="font-bold">3</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg dark:text-white">Donation Process</h3>
                        <p class="text-gray-700 dark:text-gray-200">The actual donation takes only about 8-10 minutes, during which approximately 450ml of blood is collected.</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-200 p-3 rounded-full mr-4">
                        <span class="font-bold">4</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg dark:text-white">Recovery</h3>
                        <p class="text-gray-700 dark:text-gray-200">After donating, you'll be given refreshments and asked to rest for 15 minutes before leaving.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>