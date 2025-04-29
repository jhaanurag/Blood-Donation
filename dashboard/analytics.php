<?php
require_once '../includes/header.php';
require_once '../includes/auth.php';

// Require donor login to view this page
require_donor_login();

// Database connection should be available as $conn from header.php

// --- Get Current Donor Information ---
$donor_id = $_SESSION['donor_id'];
$query_donor = "SELECT id, name, email, blood_group, age FROM users WHERE id = ?";
$stmt_donor = $conn->prepare($query_donor);
if (!$stmt_donor) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error); // Basic error handling
}
$stmt_donor->bind_param("i", $donor_id);
$stmt_donor->execute();
$result_donor = $stmt_donor->get_result();
$donor = $result_donor->fetch_assoc();
$stmt_donor->close();

if (!$donor) {
    // Handle case where donor data couldn't be fetched
    die("Error fetching donor information.");
}

// --- Personal Impact Metrics ---
// Calculate total completed donations for the current user
$completed_donations_query = "
    SELECT COUNT(*) as count
    FROM appointments
    WHERE user_id = ? AND status = 'completed'
";
$impact_stmt = $conn->prepare($completed_donations_query);
$impact_stmt->bind_param("i", $donor_id);
$impact_stmt->execute();
$impact_result = $impact_stmt->get_result();
$impact_row = $impact_result->fetch_assoc();
$total_user_donations = $impact_row['count'];
$lives_saved = $total_user_donations * 3; // Assuming each donation saves up to 3 lives
$impact_stmt->close();


// --- 1. Donor Demographic Analytics ---

// Age Distribution
$age_distribution_query = "
    SELECT
        CASE
            WHEN age BETWEEN 18 AND 25 THEN '18-25'
            WHEN age BETWEEN 26 AND 35 THEN '26-35'
            WHEN age BETWEEN 36 AND 45 THEN '36-45'
            WHEN age BETWEEN 46 AND 55 THEN '46-55'
            WHEN age > 55 THEN '56+'
            ELSE 'Unknown/Invalid'
        END AS age_group,
        COUNT(*) as count
    FROM
        users
    WHERE
        age IS NOT NULL
    GROUP BY
        age_group
    ORDER BY
        FIELD(age_group, '18-25', '26-35', '36-45', '46-55', '56+', 'Unknown/Invalid')
";
$age_result = $conn->query($age_distribution_query);
$age_labels = [];
$age_data = [];
if ($age_result) {
    while ($row = $age_result->fetch_assoc()) {
        $age_labels[] = $row['age_group'];
        $age_data[] = $row['count'];
    }
}

// Gender Distribution
// First, check if the gender column exists in the users table
$check_column_query = "SHOW COLUMNS FROM users LIKE 'gender'";
$column_check = $conn->query($check_column_query);
$gender_column_exists = ($column_check && $column_check->num_rows > 0);

if ($gender_column_exists) {
    $gender_distribution_query = "
        SELECT gender, COUNT(*) as count
        FROM users
        WHERE role = 'donor' AND gender IS NOT NULL AND gender != ''
        GROUP BY gender
        ORDER BY gender
    ";
    $gender_result = $conn->query($gender_distribution_query);
} else {
    // If the gender column doesn't exist, set the result to null
    $gender_result = null;
}
$gender_labels = [];
$gender_data = [];
if ($gender_result) {
    while ($row = $gender_result->fetch_assoc()) {
        // Optional: Capitalize gender for display
        $gender_labels[] = ucfirst($row['gender']);
        $gender_data[] = $row['count'];
    }
}

// First-time vs. Returning Donors (Monthly Trend - Last 12 Months)
$first_time_query = "
    WITH FirstDonation AS (
        SELECT user_id, MIN(appointment_date) as first_date
        FROM appointments
        WHERE status = 'completed'
        GROUP BY user_id
    )
    SELECT DATE_FORMAT(first_date, '%Y-%m') as month, COUNT(*) as new_donors
    FROM FirstDonation
    WHERE first_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY month
    ORDER BY month;
";
$first_time_result = $conn->query($first_time_query);
$monthly_new_donors = [];
if ($first_time_result) {
    while ($row = $first_time_result->fetch_assoc()) {
        $monthly_new_donors[$row['month']] = $row['new_donors'];
    }
}

// Get Total Monthly Donations (from original code, slightly adapted)
$monthly_donations_query = "
    SELECT
        DATE_FORMAT(appointment_date, '%Y-%m') as month,
        COUNT(*) as count
    FROM
        appointments
    WHERE
        status = 'completed'
        AND appointment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY
        DATE_FORMAT(appointment_date, '%Y-%m')
    ORDER BY
        month
";
$monthly_donations_result = $conn->query($monthly_donations_query);
$donor_trend_labels = [];
$donor_trend_total_data = [];
$donor_trend_new_data = [];
$donor_trend_returning_data = [];
if ($monthly_donations_result) {
    while ($row = $monthly_donations_result->fetch_assoc()) {
        $month = $row['month'];
        $total_donations = $row['count'];
        $new_donors_this_month = $monthly_new_donors[$month] ?? 0; // Use null coalescing operator
        $returning_donors = $total_donations - $new_donors_this_month;

        $donor_trend_labels[] = date('M Y', strtotime($month . '-01'));
        $donor_trend_total_data[] = $total_donations;
        $donor_trend_new_data[] = $new_donors_this_month;
        // Ensure returning count isn't negative if data is slightly off
        $donor_trend_returning_data[] = max(0, $returning_donors);
    }
}


// --- 2. Donation Success Metrics ---

// Deferral Rate Analysis (Reason Breakdown - checking if 'deferral_reason' column exists)
// First check if the deferral_reason column exists
$check_deferral_column_query = "SHOW COLUMNS FROM appointments LIKE 'deferral_reason'";
$deferral_column_check = $conn->query($check_deferral_column_query);
$deferral_reason_column_exists = ($deferral_column_check && $deferral_column_check->num_rows > 0);

$has_deferral_reasons = false; // Flag to check if we got data
$deferral_reason_labels = [];
$deferral_reason_data = [];

if ($deferral_reason_column_exists) {
    $deferral_reason_query = "
        SELECT deferral_reason, COUNT(*) as count
        FROM appointments
        WHERE status = 'deferred'
          AND deferral_reason IS NOT NULL AND deferral_reason != ''
          AND appointment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) -- Optional: time limit
        GROUP BY deferral_reason
        ORDER BY count DESC
        LIMIT 10; -- Show top reasons
    ";
    $deferral_reason_result = $conn->query($deferral_reason_query);
    
    if ($deferral_reason_result && $deferral_reason_result->num_rows > 0) {
        $has_deferral_reasons = true;
        while ($row = $deferral_reason_result->fetch_assoc()) {
            $deferral_reason_labels[] = $row['deferral_reason'];
            $deferral_reason_data[] = $row['count'];
        }
    }
}

// Deferral Rate Over Time (Alternative if reasons aren't available or needed)
$deferral_rate_query = "
    SELECT
        DATE_FORMAT(appointment_date, '%Y-%m') as month,
        SUM(CASE WHEN status = 'deferred' THEN 1 ELSE 0 END) as deferred_count,
        COUNT(*) as total_scheduled
    FROM appointments
    WHERE appointment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY month
    ORDER BY month;
";
$deferral_rate_result = $conn->query($deferral_rate_query);
$deferral_rate_labels = [];
$deferral_rate_data = [];
if ($deferral_rate_result) {
    while ($row = $deferral_rate_result->fetch_assoc()) {
        $rate = ($row['total_scheduled'] > 0) ? round(($row['deferred_count'] / $row['total_scheduled']) * 100, 1) : 0;
        $deferral_rate_labels[] = date('M Y', strtotime($row['month'] . '-01'));
        $deferral_rate_data[] = $rate;
    }
}


// Donation Conversion Rate (Scheduled -> Completed)
$conversion_rate_query = "
    SELECT
        DATE_FORMAT(appointment_date, '%Y-%m') as month,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
        COUNT(*) as total_scheduled
    FROM appointments
    WHERE appointment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY month
    ORDER BY month;
";
$conversion_rate_result = $conn->query($conversion_rate_query);
$conversion_rate_labels = [];
$conversion_rate_data = [];
if ($conversion_rate_result) {
    while ($row = $conversion_rate_result->fetch_assoc()) {
        $rate = ($row['total_scheduled'] > 0) ? round(($row['completed_count'] / $row['total_scheduled']) * 100, 1) : 0;
        $conversion_rate_labels[] = date('M Y', strtotime($row['month'] . '-01'));
        $conversion_rate_data[] = $rate;
    }
}


// --- 3. Geographic Insights ---

// Top Donor Locations (Using existing query from original code)
$location_query = "SELECT city, state, COUNT(*) as count FROM users WHERE city IS NOT NULL AND state IS NOT NULL GROUP BY city, state ORDER BY count DESC LIMIT 10";
$locations_result = $conn->query($location_query);
$location_labels = [];
$location_data = [];
if ($locations_result) {
    while ($row = $locations_result->fetch_assoc()) {
        $location_labels[] = $row['city'] . ', ' . $row['state'];
        $location_data[] = $row['count'];
    }
}

/*
// Center Performance Comparison (Requires 'center_id' in appointments and 'donation_centers' table)
$center_perf_query = "
    SELECT dc.name, COUNT(a.id) as donation_count
    FROM appointments a
    JOIN donation_centers dc ON a.center_id = dc.id
    WHERE a.status = 'completed' AND a.appointment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY dc.id, dc.name
    ORDER BY donation_count DESC
    LIMIT 10;
";
$center_perf_result = $conn->query($center_perf_query);
$center_perf_labels = [];
$center_perf_data = [];
$has_center_data = false;
if ($center_perf_result && $center_perf_result->num_rows > 0) {
    $has_center_data = true;
    while ($row = $center_perf_result->fetch_assoc()) {
        $center_perf_labels[] = $row['name'];
        $center_perf_data[] = $row['donation_count'];
    }
}
*/


// --- 4. Seasonal Analysis ---

// Day of Week Analysis
$day_of_week_query = "
    SELECT DAYNAME(appointment_date) as day_name, COUNT(*) as count
    FROM appointments
    WHERE status = 'completed' AND appointment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY day_name, DAYOFWEEK(appointment_date)
    ORDER BY DAYOFWEEK(appointment_date);
";
$day_of_week_result = $conn->query($day_of_week_query);
$day_labels = [];
$day_data = [];
if ($day_of_week_result) {
    while ($row = $day_of_week_result->fetch_assoc()) {
        $day_labels[] = $row['day_name'];
        $day_data[] = $row['count'];
    }
}

// Monthly Donations Trend (Using data prepared earlier for First-time/Returning chart)
// $donor_trend_labels, $donor_trend_total_data


// --- 5. Blood Inventory Management ---

// Blood Type Supply vs Demand (Using simulated inventory and demand from original code)
$blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
$inventory_data = []; // Supply
$demand_data = [];    // Demand

// Calculate Supply (Simulated based on recent donations)
$recent_donation_query = "
    SELECT u.blood_group, COUNT(*) as count
    FROM appointments a JOIN users u ON a.user_id = u.id
    WHERE a.status = 'completed' AND a.appointment_date >= DATE_SUB(NOW(), INTERVAL 42 DAY)
    GROUP BY u.blood_group;
";
$recent_donations_result = $conn->query($recent_donation_query);
$recent_donation_counts = [];
if ($recent_donations_result) {
    while ($row = $recent_donations_result->fetch_assoc()) {
        $recent_donation_counts[$row['blood_group']] = $row['count'];
    }
}
foreach ($blood_groups as $group) {
    // Assuming each donation yields 1 usable unit for simplicity here
    $inventory_data[$group] = $recent_donation_counts[$group] ?? 0;
}

// Calculate Demand (Simulated based on recent requests - needs a 'requests' table)
$demand_query = "
    SELECT blood_group, COUNT(*) as count
    FROM requests -- ASSUMES a 'requests' table exists
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY blood_group;
";
$demand_result = $conn->query($demand_query); // This might fail if 'requests' table doesn't exist
$raw_demand_counts = [];
if ($demand_result) {
    while ($row = $demand_result->fetch_assoc()) {
        $raw_demand_counts[$row['blood_group']] = $row['count'];
    }
}
foreach ($blood_groups as $group) {
    // Assuming each request is for 1 unit for simplicity
    $demand_data[$group] = $raw_demand_counts[$group] ?? 0;
}

// Prepare data for Radar chart
$supply_demand_labels = $blood_groups;
$supply_data_radar = array_values($inventory_data);
$demand_data_radar = array_values($demand_data);


// --- 6. Impact Visualization ---
// $lives_saved is already calculated


// --- 7. Community Engagement ---
// Social Media Conversion & Referral Network require more complex tracking/data not assumed here.


// --- Blood Type Compatibility Data (from original code) ---
$compatibility = [
    'A+' => ['donate_to' => ['A+', 'AB+'], 'receive_from' => ['A+', 'A-', 'O+', 'O-']],
    'A-' => ['donate_to' => ['A+', 'A-', 'AB+', 'AB-'], 'receive_from' => ['A-', 'O-']],
    'B+' => ['donate_to' => ['B+', 'AB+'], 'receive_from' => ['B+', 'B-', 'O+', 'O-']],
    'B-' => ['donate_to' => ['B+', 'B-', 'AB+', 'AB-'], 'receive_from' => ['B-', 'O-']],
    'AB+' => ['donate_to' => ['AB+'], 'receive_from' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']],
    'AB-' => ['donate_to' => ['AB+', 'AB-'], 'receive_from' => ['A-', 'B-', 'AB-', 'O-']],
    'O+' => ['donate_to' => ['A+', 'B+', 'AB+', 'O+'], 'receive_from' => ['O+', 'O-']],
    'O-' => ['donate_to' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], 'receive_from' => ['O-']],
];

// Dashboard page content starts here
$dashboard_url = $base_url . "dashboard"; // Ensure $base_url is defined
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8 dark:text-white">Blood Donation Analytics Dashboard</h1>

    <!-- Personal Impact Section -->
    <section class="mb-12">
        <h2 class="text-2xl font-semibold mb-4 dark:text-white">Your Personal Impact</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
                <h3 class="text-xl font-semibold mb-2 dark:text-white">Your Donations</h3>
                <div class="text-4xl font-bold text-red-600 dark:text-red-400"><?php echo $total_user_donations; ?></div>
                <p class="text-gray-600 dark:text-gray-300 mt-1">Total successful donations</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
                <h3 class="text-xl font-semibold mb-2 dark:text-white">Lives Potentially Saved</h3>
                <div class="text-4xl font-bold text-red-600 dark:text-red-400"><?php echo $lives_saved; ?></div>
                <p class="text-gray-600 dark:text-gray-300 mt-1">Based on your donations (up to 3 per donation)</p>
            </div>
        </div>
    </section>

    <!-- Donor Demographics Section -->
    <section class="mb-12">
        <h2 class="text-2xl font-semibold mb-4 dark:text-white">Donor Demographics</h2>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Age Distribution -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 lg:col-span-2">
                <h3 class="text-xl font-semibold mb-4 dark:text-white">Donor Age Distribution</h3>
                <div style="position: relative; height:300px;">
                    <canvas id="ageDistributionChart"></canvas>
                </div>
            </div>
            <!-- Gender Distribution -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold mb-4 dark:text-white">Donor Gender</h3>
                <div style="position: relative; height:300px;">
                    <canvas id="genderDistributionChart"></canvas>
                </div>
            </div>
             <!-- First-time vs Returning Donors -->
             <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 lg:col-span-3">
                <h3 class="text-xl font-semibold mb-4 dark:text-white">New vs. Returning Donors Trend (Last 12 Months)</h3>
                <div style="position: relative; height:300px;">
                    <canvas id="donorTrendChart"></canvas>
                </div>
            </div>
        </div>
    </section>

     <!-- Donation Success Metrics Section -->
     <section class="mb-12">
        <h2 class="text-2xl font-semibold mb-4 dark:text-white">Donation Success Metrics</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Deferral Rate Analysis -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <?php if ($has_deferral_reasons): ?>
                    <h3 class="text-xl font-semibold mb-4 dark:text-white">Top Deferral Reasons</h3>
                    <div style="position: relative; height:300px;">
                        <canvas id="deferralReasonChart"></canvas>
                    </div>
                <?php else: ?>
                     <h3 class="text-xl font-semibold mb-4 dark:text-white">Deferral Rate Trend (%)</h3>
                     <div style="position: relative; height:300px;">
                        <canvas id="deferralRateChart"></canvas>
                    </div>
                     <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Note: Showing overall deferral rate. Deferral reason data not available.</p>
                <?php endif; ?>
            </div>
             <!-- Donation Conversion Rate -->
             <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold mb-4 dark:text-white">Donation Conversion Rate Trend (%)</h3>
                 <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">(Scheduled Appointments vs. Completed Donations)</p>
                <div style="position: relative; height:300px;">
                    <canvas id="conversionRateChart"></canvas>
                </div>
            </div>
        </div>
    </section>

    <!-- Geographic & Seasonal Insights Section -->
    <section class="mb-12">
        <h2 class="text-2xl font-semibold mb-4 dark:text-white">Geographic & Seasonal Insights</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Donor Locations -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold mb-4 dark:text-white">Top 10 Donor Locations</h3>
                <div style="position: relative; height:300px;">
                    <canvas id="locationChart"></canvas>
                </div>
            </div>
            <!-- Day of Week Analysis -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold mb-4 dark:text-white">Donations by Day of the Week</h3>
                <div style="position: relative; height:300px;">
                    <canvas id="dayOfWeekChart"></canvas>
                </div>
            </div>
             <!-- Monthly Donations Trend -->
             <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 lg:col-span-2">
                <h3 class="text-xl font-semibold mb-4 dark:text-white">Monthly Donation Volume (Last 12 Months)</h3>
                <div style="position: relative; height:300px;">
                    <canvas id="monthlyDonationsChart"></canvas> <!-- Reusing ID from original for consistency if needed -->
                </div>
            </div>
            <!-- Center Performance (Optional) -->
            <?php /* if ($has_center_data): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold mb-4 dark:text-white">Top Performing Donation Centers</h3>
                <div style="position: relative; height:300px;">
                    <canvas id="centerPerformanceChart"></canvas>
                </div>
            </div>
            <?php endif; */ ?>
        </div>
    </section>

    <!-- Blood Inventory & Compatibility Section -->
    <section class="mb-12">
        <h2 class="text-2xl font-semibold mb-4 dark:text-white">Blood Inventory & Compatibility</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
             <!-- Blood Type Supply vs. Demand -->
             <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold mb-4 dark:text-white">Blood Type Supply vs. Demand</h3>
                 <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">(Simulated based on recent activity)</p>
                <div style="position: relative; height:350px;">
                    <canvas id="supplyDemandChart"></canvas>
                </div>
            </div>
            <!-- Blood Type Compatibility Guide -->
             <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                 <h3 class="text-xl font-semibold mb-1 p-6 dark:text-white">Blood Type Compatibility Guide</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">Blood Type</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">Can Donate To</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">Can Receive From</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            <?php foreach ($compatibility as $type => $info): ?>
                            <tr class="<?php echo ($type === $donor['blood_group']) ? 'bg-red-50 dark:bg-red-900/30' : 'hover:bg-gray-50 dark:hover:bg-gray-700'; ?>">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="font-semibold <?php echo ($type === $donor['blood_group']) ? 'text-red-700 dark:text-red-300' : 'text-gray-800 dark:text-gray-200'; ?>">
                                        <?php echo $type; ?>
                                        <?php if ($type === $donor['blood_group']): ?>
                                            <span class="ml-1 text-xs font-normal text-red-600 dark:text-red-400">(Your Type)</span>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300"><?php echo implode(', ', $info['donate_to']); ?></td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300"><?php echo implode(', ', $info['receive_from']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Educational Notes -->
    <section class="mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 dark:text-white">Blood Donation Facts</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border-l-4 border-red-500 pl-4 py-2">
                    <p class="text-gray-700 dark:text-gray-300">The shelf life of donated red blood cells is typically 42 days.</p>
                </div>
                <div class="border-l-4 border-red-500 pl-4 py-2">
                    <p class="text-gray-700 dark:text-gray-300">Platelets must usually be used within 5 to 7 days of donation.</p>
                </div>
                <div class="border-l-4 border-red-500 pl-4 py-2">
                    <p class="text-gray-700 dark:text-gray-300">Every 2 seconds, someone in the U.S. needs blood or platelets.</p>
                </div>
                <div class="border-l-4 border-red-500 pl-4 py-2">
                    <p class="text-gray-700 dark:text-gray-300">O-negative blood is the universal red cell donor type and is often needed in emergencies.</p>
                </div>
            </div>
             <div class="mt-6 text-center">
                 <a href="<?php echo $dashboard_url; ?>/appointments.php" class="inline-block bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded transition duration-150 ease-in-out">
                     Schedule Your Next Donation
                 </a>
             </div>
        </div>
    </section>

</div> <!-- End Container -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug message
    console.log('Analytics script loaded');

    try {
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded.');
             // Display error message to user
             const errorDiv = document.createElement('div');
             errorDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 container mx-auto';
             errorDiv.setAttribute('role', 'alert');
             errorDiv.innerHTML = '<strong class="font-bold">Error:</strong> Chart library failed to load. Please check your internet connection or contact support.';
             document.body.insertBefore(errorDiv, document.body.firstChild);
            return;
        } else {
            console.log('Chart.js loaded successfully.');
        }

        // Helper function for chart text color based on theme
        const getTextColor = () => window.matchMedia('(prefers-color-scheme: dark)').matches ? 'rgba(255, 255, 255, 0.8)' : 'rgba(0, 0, 0, 0.8)';
        const getGridColor = () => window.matchMedia('(prefers-color-scheme: dark)').matches ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
        const getBorderColor = () => window.matchMedia('(prefers-color-scheme: dark)').matches ? 'rgba(45, 55, 72, 1)' : 'rgba(255, 255, 255, 1)'; // For Pie/Doughnut

        // Chart Colors (Updated to indigo/coral theme)
        const primaryColor = 'rgba(79, 70, 229, <alpha>)'; // Indigo primary
        const secondaryColor = 'rgba(99, 102, 241, <alpha>)'; // Lighter indigo
        const accentColor = 'rgba(251, 113, 133, <alpha>)'; // Coral accent
        const tertiaryColor = 'rgba(244, 114, 182, <alpha>)'; // Lighter coral

        const pieColors = [
            '#4F46E5', '#6366F1', '#818CF8', '#A5B4FC', 
            '#FB7185', '#FDA4AF', '#FEA3B4', '#FECDD3' // Indigo and coral variants
        ];
        const genderColors = [primaryColor.replace('<alpha>', '0.7'), accentColor.replace('<alpha>', '0.7'), tertiaryColor.replace('<alpha>', '0.7')];


        // --- Initialize Charts ---

        // 1. Age Distribution Chart
        const ageDistCtx = document.getElementById('ageDistributionChart')?.getContext('2d');
        if (ageDistCtx) {
            new Chart(ageDistCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($age_labels); ?>,
                    datasets: [{
                        label: 'Number of Donors',
                        data: <?php echo json_encode($age_data); ?>,
                        backgroundColor: primaryColor.replace('<alpha>', '0.7'),
                        borderColor: primaryColor.replace('<alpha>', '1'),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                    scales: {
                        x: { beginAtZero: true, ticks: { color: getTextColor() }, grid: { color: getGridColor() } },
                        y: { ticks: { color: getTextColor() }, grid: { color: getGridColor() } }
                    },
                    plugins: {
                         legend: { display: false }, // Label is clear
                         title: { display: false }, // Already have section title
                         tooltip: { titleFont: { size: 14 }, bodyFont: { size: 12 } }
                    }
                }
            });
        }

        // 2. Gender Distribution Chart
        const genderDistCtx = document.getElementById('genderDistributionChart')?.getContext('2d');
        if (genderDistCtx && <?php echo json_encode(!empty($gender_labels)); ?>) {
            new Chart(genderDistCtx, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($gender_labels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($gender_data); ?>,
                        backgroundColor: genderColors.slice(0, <?php echo count($gender_labels); ?>),
                        borderColor: getBorderColor(),
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: getTextColor() } },
                        title: { display: false },
                         tooltip: { titleFont: { size: 14 }, bodyFont: { size: 12 } }
                    }
                }
            });
        } else if (genderDistCtx) {
            genderDistCtx.canvas.parentNode.innerHTML = '<p class="text-center text-gray-500 dark:text-gray-400 pt-10">Gender data not available.</p>';
        }


        // 3. First-time vs Returning Donors Chart
        const donorTrendCtx = document.getElementById('donorTrendChart')?.getContext('2d');
        if (donorTrendCtx) {
            new Chart(donorTrendCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($donor_trend_labels); ?>,
                    datasets: [
                        {
                            label: 'New Donors',
                            data: <?php echo json_encode($donor_trend_new_data); ?>,
                            borderColor: primaryColor.replace('<alpha>', '1'),
                            backgroundColor: primaryColor.replace('<alpha>', '0.1'),
                            borderWidth: 2, tension: 0.3, fill: true
                        },
                        {
                            label: 'Returning Donors',
                            data: <?php echo json_encode($donor_trend_returning_data); ?>,
                            borderColor: accentColor.replace('<alpha>', '1'),
                            backgroundColor: accentColor.replace('<alpha>', '0.1'),
                            borderWidth: 2, tension: 0.3, fill: true
                        }
                    ]
                },
                 options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { color: getTextColor() }, grid: { color: getGridColor() } },
                        x: { ticks: { color: getTextColor() }, grid: { color: getGridColor() } }
                    },
                    plugins: {
                        legend: { position: 'bottom', labels: { color: getTextColor() } },
                        title: { display: false },
                         tooltip: { mode: 'index', intersect: false, titleFont: { size: 14 }, bodyFont: { size: 12 } }
                    }
                }
            });
        }

        // 4. Deferral Reason Chart (if data exists)
        const deferralReasonCtx = document.getElementById('deferralReasonChart')?.getContext('2d');
        <?php if ($has_deferral_reasons): ?>
        if (deferralReasonCtx) {
            new Chart(deferralReasonCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($deferral_reason_labels); ?>,
                    datasets: [{
                        label: 'Number of Deferrals',
                        data: <?php echo json_encode($deferral_reason_data); ?>,
                         backgroundColor: accentColor.replace('<alpha>', '0.7'),
                         borderColor: accentColor.replace('<alpha>', '1'),
                        borderWidth: 1
                    }]
                },
                 options: {
                    responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                    scales: {
                        x: { beginAtZero: true, ticks: { color: getTextColor() }, grid: { color: getGridColor() } },
                        y: { ticks: { color: getTextColor() }, grid: { display: false } } // Reasons can be long
                    },
                    plugins: {
                         legend: { display: false },
                         title: { display: false },
                         tooltip: { titleFont: { size: 14 }, bodyFont: { size: 12 } }
                    }
                }
            });
        }
        <?php else: ?>
        // 4b. Deferral Rate Trend Chart
        const deferralRateCtx = document.getElementById('deferralRateChart')?.getContext('2d');
        if (deferralRateCtx) {
             new Chart(deferralRateCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($deferral_rate_labels); ?>,
                    datasets: [{
                        label: 'Deferral Rate (%)',
                        data: <?php echo json_encode($deferral_rate_data); ?>,
                        borderColor: accentColor.replace('<alpha>', '1'),
                        backgroundColor: accentColor.replace('<alpha>', '0.1'),
                        borderWidth: 2, tension: 0.3, fill: true
                    }]
                },
                 options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { color: getTextColor(), callback: (val) => val + '%' }, grid: { color: getGridColor() } },
                        x: { ticks: { color: getTextColor() }, grid: { color: getGridColor() } }
                    },
                    plugins: {
                         legend: { display: false },
                         title: { display: false },
                         tooltip: { titleFont: { size: 14 }, bodyFont: { size: 12 }, callbacks: { label: (ctx) => `${ctx.dataset.label}: ${ctx.parsed.y}%` } }
                    }
                }
            });
        }
        <?php endif; ?>


        // 5. Conversion Rate Trend Chart
        const conversionRateCtx = document.getElementById('conversionRateChart')?.getContext('2d');
        if (conversionRateCtx) {
             new Chart(conversionRateCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($conversion_rate_labels); ?>,
                    datasets: [{
                        label: 'Conversion Rate (%)',
                        data: <?php echo json_encode($conversion_rate_data); ?>,
                        borderColor: tertiaryColor.replace('<alpha>', '1'),
                        backgroundColor: tertiaryColor.replace('<alpha>', '0.1'),
                        borderWidth: 2, tension: 0.3, fill: true
                    }]
                },
                 options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, max: 100, ticks: { color: getTextColor(), callback: (val) => val + '%' }, grid: { color: getGridColor() } },
                        x: { ticks: { color: getTextColor() }, grid: { color: getGridColor() } }
                    },
                    plugins: {
                         legend: { display: false },
                         title: { display: false },
                         tooltip: { titleFont: { size: 14 }, bodyFont: { size: 12 }, callbacks: { label: (ctx) => `${ctx.dataset.label}: ${ctx.parsed.y}%` } }
                    }
                }
            });
        }


        // 6. Top Donor Locations Chart
        const locationCtx = document.getElementById('locationChart')?.getContext('2d');
        if (locationCtx) {
            new Chart(locationCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($location_labels); ?>,
                    datasets: [{
                        label: 'Number of Donors',
                        data: <?php echo json_encode($location_data); ?>,
                        backgroundColor: primaryColor.replace('<alpha>', '0.7'),
                        borderColor: primaryColor.replace('<alpha>', '1'),
                        borderWidth: 1
                    }]
                },
                 options: {
                    responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                    scales: {
                        x: { beginAtZero: true, ticks: { color: getTextColor() }, grid: { color: getGridColor() } },
                        y: { ticks: { color: getTextColor() }, grid: { display: false } }
                    },
                    plugins: {
                         legend: { display: false },
                         title: { display: false },
                         tooltip: { titleFont: { size: 14 }, bodyFont: { size: 12 } }
                    }
                }
            });
        }

        // 7. Day of Week Chart
        const dayOfWeekCtx = document.getElementById('dayOfWeekChart')?.getContext('2d');
        if (dayOfWeekCtx) {
            new Chart(dayOfWeekCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($day_labels); ?>,
                    datasets: [{
                        label: 'Completed Donations',
                        data: <?php echo json_encode($day_data); ?>,
                        backgroundColor: secondaryColor.replace('<alpha>', '0.7'),
                        borderColor: secondaryColor.replace('<alpha>', '1'),
                        borderWidth: 1
                    }]
                },
                 options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { color: getTextColor() }, grid: { color: getGridColor() } },
                        x: { ticks: { color: getTextColor() }, grid: { display: false } }
                    },
                    plugins: {
                         legend: { display: false },
                         title: { display: false },
                         tooltip: { titleFont: { size: 14 }, bodyFont: { size: 12 } }
                    }
                }
            });
        }

         // 8. Monthly Donations Chart (Total Volume)
         const monthlyDonationsCtx = document.getElementById('monthlyDonationsChart')?.getContext('2d');
         if (monthlyDonationsCtx) {
             new Chart(monthlyDonationsCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($donor_trend_labels); ?>, // Reuse labels from trend chart
                    datasets: [{
                        label: 'Total Donations',
                        data: <?php echo json_encode($donor_trend_total_data); ?>, // Use total data
                        borderColor: primaryColor.replace('<alpha>', '1'),
                        backgroundColor: primaryColor.replace('<alpha>', '0.1'),
                        borderWidth: 3, tension: 0.3, fill: true,
                        pointBackgroundColor: primaryColor.replace('<alpha>', '1'),
                        pointRadius: 3
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { color: getTextColor() }, grid: { color: getGridColor() } },
                        x: { ticks: { color: getTextColor() }, grid: { color: getGridColor() } }
                    },
                    plugins: {
                        legend: { display: false }, // Only one dataset, label is clear
                        title: { display: false },
                        tooltip: { titleFont: { size: 14 }, bodyFont: { size: 12 } }
                    }
                }
            });
        }

        // 9. Supply vs Demand Radar Chart
        const supplyDemandCtx = document.getElementById('supplyDemandChart')?.getContext('2d');
        if (supplyDemandCtx) {
            new Chart(supplyDemandCtx, {
                type: 'radar',
                data: {
                    labels: <?php echo json_encode($supply_demand_labels); ?>,
                    datasets: [
                        {
                            label: 'Current Supply (Units)',
                            data: <?php echo json_encode($supply_data_radar); ?>,
                            borderColor: primaryColor.replace('<alpha>', '1'),
                            backgroundColor: primaryColor.replace('<alpha>', '0.3'),
                            borderWidth: 2,
                            pointBackgroundColor: primaryColor.replace('<alpha>', '1')
                        },
                        {
                            label: 'Recent Demand (Requests)',
                            data: <?php echo json_encode($demand_data_radar); ?>,
                            borderColor: accentColor.replace('<alpha>', '1'),
                            backgroundColor: accentColor.replace('<alpha>', '0.3'),
                            borderWidth: 2,
                            pointBackgroundColor: accentColor.replace('<alpha>', '1')
                        }
                    ]
                },
                 options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: {
                        r: { // Radial axis
                            beginAtZero: true,
                            angleLines: { color: getGridColor() },
                            grid: { color: getGridColor() },
                            pointLabels: { color: getTextColor(), font: { size: 11 } },
                            ticks: { color: getTextColor(), backdropColor: 'transparent', stepSize: 5 } // Adjust stepSize as needed
                        }
                    },
                    plugins: {
                         legend: { position: 'bottom', labels: { color: getTextColor() } },
                         title: { display: false },
                         tooltip: { titleFont: { size: 14 }, bodyFont: { size: 12 } }
                    }
                }
            });
        }


        // Center Performance Chart (Optional)
        /*
        const centerPerfCtx = document.getElementById('centerPerformanceChart')?.getContext('2d');
        <?php // if ($has_center_data): ?>
        if (centerPerfCtx) {
            new Chart(centerPerfCtx, {
                type: 'bar',
                data: {
                    labels: <?php // echo json_encode($center_perf_labels); ?>,
                    datasets: [{
                        label: 'Completed Donations',
                        data: <?php // echo json_encode($center_perf_data); ?>,
                        backgroundColor: secondaryColor.replace('<alpha>', '0.7'),
                        borderColor: secondaryColor.replace('<alpha>', '1'),
                        borderWidth: 1
                    }]
                },
                 options: { // Similar options to other bar charts }
            });
        }
        <?php // endif; ?>
        */


        // Listener for dark mode changes (optional: reload or update charts)
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            // Simple approach: reload the page to redraw charts with new colors
             window.location.reload();

             // More advanced: Update chart options dynamically (requires storing chart instances)
             // Object.values(charts).forEach(chart => {
             //    chart.options.scales.x.ticks.color = getTextColor();
             //    // ... update other colors ...
             //    chart.update();
             // });
        });

    } catch (error) {
        console.error('An error occurred while initializing charts:', error);
         // Optionally display a generic error message on the page
         const errorDiv = document.createElement('div');
         errorDiv.className = 'bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4 container mx-auto';
         errorDiv.setAttribute('role', 'alert');
         errorDiv.innerHTML = '<strong class="font-bold">Warning:</strong> Some charts might not display correctly due to an unexpected error.';
         document.body.insertBefore(errorDiv, document.body.firstChild);
    }
});
</script>

<?php include_once '../includes/footer.php'; ?>