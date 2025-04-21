<?php
require_once '../includes/header.php';
require_once '../includes/auth.php';

// Require donor login to view this page
require_donor_login();

// Utility function to safely fetch data from external APIs
function fetchAPIData($url, $fallbackData = []) {
    try {
        // Set timeout to prevent long waits
        $context = stream_context_create([
            'http' => [
                'timeout' => 5 // 5 seconds timeout
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            // If API request fails, return fallback data
            return $fallbackData;
        }
        
        $data = json_decode($response, true);
        return $data ?: $fallbackData;
    } catch (Exception $e) {
        // Return fallback data if any error occurs
        return $fallbackData;
    }
}

// Get donor information
$donor_id = $_SESSION['donor_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$result = $stmt->get_result();
$donor = $result->fetch_assoc();

// 1. Blood Type Distribution Data
$blood_type_query = "SELECT blood_group, COUNT(*) as count FROM users GROUP BY blood_group ORDER BY blood_group";
$blood_types = $conn->query($blood_type_query);
$blood_type_data = [];
$blood_type_labels = [];
$blood_type_counts = [];
while ($row = $blood_types->fetch_assoc()) {
    $blood_type_labels[] = $row['blood_group'];
    $blood_type_counts[] = $row['count'];
}

// 2. Monthly Donations Data (last 12 months)
$monthly_donations_query = "
    SELECT 
        DATE_FORMAT(appointment_date, '%Y-%m') as month,
        COUNT(*) as count
    FROM 
        appointments 
    WHERE 
        status = 'completed' 
        AND appointment_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY 
        DATE_FORMAT(appointment_date, '%Y-%m')
    ORDER BY 
        month
";
$monthly_donations = $conn->query($monthly_donations_query);
$monthly_labels = [];
$monthly_data = [];
while ($row = $monthly_donations->fetch_assoc()) {
    $display_date = date('M Y', strtotime($row['month'] . '-01'));
    $monthly_labels[] = $display_date;
    $monthly_data[] = $row['count'];
}

// 3. Geographic Distribution
$location_query = "SELECT city, state, COUNT(*) as count FROM users GROUP BY city, state ORDER BY count DESC LIMIT 10";
$locations = $conn->query($location_query);
$location_labels = [];
$location_data = [];
while ($row = $locations->fetch_assoc()) {
    $location_labels[] = $row['city'] . ', ' . $row['state'];
    $location_data[] = $row['count'];
}

// 4. User-specific donation history - for the current user
$user_history_query = "
    SELECT 
        appointment_date,
        status
    FROM 
        appointments 
    WHERE 
        user_id = ? 
    ORDER BY 
        appointment_date
";
$user_stmt = $conn->prepare($user_history_query);
$user_stmt->bind_param("i", $donor_id);
$user_stmt->execute();
$user_history = $user_stmt->get_result();
$user_dates = [];
$user_status = [];
while ($row = $user_history->fetch_assoc()) {
    $user_dates[] = date('M j, Y', strtotime($row['appointment_date']));
    $user_status[] = $row['status'];
}

// 5. Current blood inventory levels (simulation based on donor counts and donation rates)
$blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
$inventory_data = [];
$recent_donation_query = "
    SELECT 
        u.blood_group, 
        COUNT(*) as count 
    FROM 
        appointments a
    JOIN 
        users u ON a.user_id = u.id 
    WHERE 
        a.status = 'completed' 
        AND a.appointment_date >= DATE_SUB(NOW(), INTERVAL 42 DAY) 
    GROUP BY 
        u.blood_group
";
$recent_donations = $conn->query($recent_donation_query);
$recent_donation_counts = [];
while ($row = $recent_donations->fetch_assoc()) {
    $recent_donation_counts[$row['blood_group']] = $row['count'];
}

// Create inventory data (simulated)
foreach ($blood_groups as $group) {
    $inventory_data[$group] = isset($recent_donation_counts[$group]) ? $recent_donation_counts[$group] * 3 : 0; // Each donation is about 3 units
}

// 6. Supply vs Demand Analysis
$demand_query = "
    SELECT 
        blood_group,
        COUNT(*) as count 
    FROM 
        requests 
    WHERE 
        created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY 
        blood_group
";
$demand_result = $conn->query($demand_query);
$demand_data = [];
foreach ($blood_groups as $group) {
    $demand_data[$group] = 0;
}
while ($row = $demand_result->fetch_assoc()) {
    $demand_data[$row['blood_group']] = $row['count'];
}

// Calculate critical shortage
$shortage_data = [];
foreach ($blood_groups as $group) {
    $supply = isset($inventory_data[$group]) ? $inventory_data[$group] : 0;
    $demand = isset($demand_data[$group]) ? $demand_data[$group] * 3 : 0; // Assume each request needs 3 units
    
    // If demand is higher than supply, it's in shortage
    $shortage_data[$group] = $supply > 0 ? ($supply - $demand) : 0;
}

// Calculate donor impact (lives potentially saved)
// Assuming each donation can save up to 3 lives
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
$lives_saved = $impact_row['count'] * 3;

// Calculate achievement rate (gamification)
$achievement_query = "
    SELECT 
        COUNT(DISTINCT badge_id) as earned_count,
        (SELECT COUNT(*) FROM badges) as total_count
    FROM 
        user_badges 
    WHERE 
        user_id = ?
";
$achievement_stmt = $conn->prepare($achievement_query);
if ($achievement_stmt) {
    $achievement_stmt->bind_param("i", $donor_id);
    $achievement_stmt->execute();
    $achievement_result = $achievement_stmt->get_result();
    $achievement_row = $achievement_result->fetch_assoc();
    $achievement_rate = $achievement_row ? ($achievement_row['earned_count'] / $achievement_row['total_count'] * 100) : 0;
} else {
    // If the badges table doesn't exist yet
    $achievement_rate = 0;
}

// 7. Global blood donation trends - Fetch from WHO Global Health Observatory API
// Using fallback data in case API isn't accessible
$global_donation_fallback = [
    'years' => ['2018', '2019', '2020', '2021', '2022'],
    'donation_rates' => [32.1, 33.4, 31.2, 32.6, 33.8],
    'voluntary_percentage' => [62, 65, 64, 68, 72]
];

// API endpoint URL (example - would use real WHO API in production)
$who_api_url = "https://ghoapi.azureedge.net/api/blood-safety";
$global_donation_data = fetchAPIData($who_api_url, $global_donation_fallback);

// Format data for chart
$global_years = $global_donation_data['years'] ?? $global_donation_fallback['years'];
$global_donation_rates = $global_donation_data['donation_rates'] ?? $global_donation_fallback['donation_rates'];
$voluntary_percentages = $global_donation_data['voluntary_percentage'] ?? $global_donation_fallback['voluntary_percentage'];

// 8. Donation forecast - predicts donation needs for next 6 months
// Using historical data to create a forecast model
$forecast_months = [];
$forecast_data = [];
$current_month = date('n'); // Current month as number (1-12)
$current_year = date('Y');

// Generate next 6 months for forecast
for ($i = 0; $i < 6; $i++) {
    $forecast_month_num = ($current_month + $i) % 12;
    $forecast_month_num = $forecast_month_num == 0 ? 12 : $forecast_month_num;
    $forecast_year = $current_year + floor(($current_month + $i - 1) / 12);
    $month_name = date('M Y', mktime(0, 0, 0, $forecast_month_num, 1, $forecast_year));
    $forecast_months[] = $month_name;
    
    // Simple forecast model based on historical data and seasonal factors
    // In a real-world scenario, this would use more sophisticated algorithms
    $seasonal_factor = 1 + (($forecast_month_num == 12 || $forecast_month_num == 1) ? 0.2 : 
                          ($forecast_month_num >= 5 && $forecast_month_num <= 8 ? -0.1 : 0));
    
    // Base prediction on the average of monthly_data plus seasonal adjustment
    $base_prediction = empty($monthly_data) ? 15 : array_sum($monthly_data) / count($monthly_data);
    $forecast_data[] = round($base_prediction * $seasonal_factor * (1 + $i * 0.05));
}

// 9. Blood supply chain visualization data
// This represents the journey of blood from donation to use
$supply_chain_data = [
    'donation' => [
        'label' => 'Collection',
        'count' => array_sum($monthly_data),
        'color' => 'rgba(220, 38, 38, 0.8)'
    ],
    'testing' => [
        'label' => 'Testing',
        'count' => round(array_sum($monthly_data) * 0.98), // 98% pass testing
        'color' => 'rgba(217, 119, 6, 0.8)'
    ],
    'processing' => [
        'label' => 'Processing',
        'count' => round(array_sum($monthly_data) * 0.98 * 0.96), // 96% are processed successfully
        'color' => 'rgba(5, 150, 105, 0.8)'
    ],
    'storage' => [
        'label' => 'Storage',
        'count' => round(array_sum($monthly_data) * 0.98 * 0.96 * 0.99), // 99% are stored successfully
        'color' => 'rgba(79, 70, 229, 0.8)'
    ],
    'distribution' => [
        'label' => 'Distribution',
        'count' => round(array_sum($monthly_data) * 0.98 * 0.96 * 0.99 * 0.98), // 98% are distributed properly
        'color' => 'rgba(124, 58, 237, 0.8)'
    ],
    'transfusion' => [
        'label' => 'Transfusion',
        'count' => round(array_sum($monthly_data) * 0.98 * 0.96 * 0.99 * 0.98 * 0.99), // 99% are transfused successfully
        'color' => 'rgba(236, 72, 153, 0.8)'
    ]
];

// Dashboard page content starts here
$dashboard_url = $base_url . "dashboard";
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 dark:text-white">Blood Donation Analytics Dashboard</h1>
    
    <!-- Personal Impact Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
            <h2 class="text-xl font-semibold mb-2 dark:text-white">Your Donations</h2>
            <div class="text-4xl font-bold text-red-600 dark:text-red-400"><?php echo $impact_row['count']; ?></div>
            <p class="text-gray-600 dark:text-gray-300">Total donations completed</p>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
            <h2 class="text-xl font-semibold mb-2 dark:text-white">Lives Potentially Saved</h2>
            <div class="text-4xl font-bold text-red-600 dark:text-red-400"><?php echo $lives_saved; ?></div>
            <p class="text-gray-600 dark:text-gray-300">Each donation can save up to 3 lives</p>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
            <h2 class="text-xl font-semibold mb-2 dark:text-white">Achievement Progress</h2>
            <div class="text-4xl font-bold text-red-600 dark:text-red-400"><?php echo round($achievement_rate); ?>%</div>
            <p class="text-gray-600 dark:text-gray-300">Of available badges earned</p>
        </div>
    </div>
    
    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Blood Type Distribution Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 dark:text-white">Blood Type Distribution</h2>
            <div style="position: relative; height:250px;">
                <canvas id="bloodTypeChart"></canvas>
            </div>
        </div>
        
        <!-- Monthly Donations Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 dark:text-white">Monthly Donations</h2>
            <div style="position: relative; height:250px;">
                <canvas id="monthlyDonationsChart"></canvas>
            </div>
        </div>
        
        <!-- Geographic Distribution -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 dark:text-white">Top Donor Locations</h2>
            <div style="position: relative; height:250px;">
                <canvas id="locationChart"></canvas>
            </div>
        </div>
        
        <!-- Your Donation History -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4 dark:text-white">Your Donation Timeline</h2>
            <?php if (count($user_dates) > 0): ?>
                <div style="position: relative; height:250px;">
                    <canvas id="donorHistoryChart"></canvas>
                </div>
            <?php else: ?>
                <div class="text-center py-10">
                    <p class="text-gray-500 dark:text-gray-400">No donation history found.</p>
                    <a href="<?php echo $dashboard_url; ?>/appointments.php" class="mt-4 inline-block bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded">Schedule Your First Donation</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Blood Inventory Section -->
    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4 dark:text-white">Blood Inventory Levels</h2>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div style="position: relative; height:250px;">
                <canvas id="inventoryChart"></canvas>
            </div>
            <div class="mt-4">
                <h3 class="text-lg font-semibold mb-2 dark:text-white">Supply vs Demand Analysis</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-2">
                    <?php foreach($blood_groups as $group): ?>
                        <?php 
                        $supply = isset($inventory_data[$group]) ? $inventory_data[$group] : 0;
                        $demand = isset($demand_data[$group]) ? $demand_data[$group] * 3 : 0;
                        $shortage = $supply - $demand;
                        $criticalClass = $shortage < 0 ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200';
                        ?>
                        <div class="p-3 rounded <?php echo $criticalClass; ?>">
                            <div class="text-xl font-bold"><?php echo $group; ?></div>
                            <div class="text-sm">
                                Supply: <?php echo $supply; ?> units<br>
                                Demand: <?php echo $demand; ?> units
                            </div>
                            <?php if ($shortage < 0): ?>
                                <div class="mt-1 text-xs font-semibold uppercase">Critical Shortage</div>
                            <?php else: ?>
                                <div class="mt-1 text-xs font-semibold uppercase">Adequately Stocked</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Global Blood Donation Trends -->
    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4 dark:text-white">Global Blood Donation Trends</h2>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div style="position: relative; height:250px;">
                <canvas id="globalDonationChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Donation Forecast -->
    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4 dark:text-white">Donation Forecast</h2>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div style="position: relative; height:250px;">
                <canvas id="forecastChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Blood Supply Chain Visualization -->
    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4 dark:text-white">Blood Supply Chain</h2>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div style="position: relative; height:250px;">
                <canvas id="supplyChainChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Blood Type Compatibility Guide -->
    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4 dark:text-white">Blood Type Compatibility Guide</h2>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">Blood Type</th>
                            <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">Can Donate To</th>
                            <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">Can Receive From</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        <?php 
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
                        
                        foreach ($compatibility as $type => $info):
                        ?>
                        <tr class="<?php echo ($type === $donor['blood_group']) ? 'bg-red-50 dark:bg-red-900/30' : 'dark:bg-gray-800'; ?>">
                            <td class="px-4 py-3">
                                <span class="font-semibold <?php echo ($type === $donor['blood_group']) ? 'bg-red-100 dark:bg-red-800' : 'bg-gray-100 dark:bg-gray-700'; ?> text-red-800 dark:text-red-200 px-2 py-1 rounded">
                                    <?php echo $type; ?>
                                    <?php if ($type === $donor['blood_group']): ?>
                                        <span class="ml-2 text-xs text-red-600 dark:text-red-300">(Your Type)</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 dark:text-gray-200"><?php echo implode(', ', $info['donate_to']); ?></td>
                            <td class="px-4 py-3 dark:text-gray-200"><?php echo implode(', ', $info['receive_from']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Educational Notes -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4 dark:text-white">Blood Donation Facts</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="border-l-4 border-red-500 pl-4 py-2">
                <p class="text-gray-700 dark:text-gray-300">The shelf life of donated red blood cells is 42 days.</p>
            </div>
            <div class="border-l-4 border-red-500 pl-4 py-2">
                <p class="text-gray-700 dark:text-gray-300">Platelets must be used within just 5 days of donation.</p>
            </div>
            <div class="border-l-4 border-red-500 pl-4 py-2">
                <p class="text-gray-700 dark:text-gray-300">Every 2 seconds someone in the world needs blood.</p>
            </div>
            <div class="border-l-4 border-red-500 pl-4 py-2">
                <p class="text-gray-700 dark:text-gray-300">A single car accident victim can require up to 100 units of blood.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug message to check if script is loading
    console.log('Analytics script loaded');
    
    try {
        // Check if Chart is defined
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded properly');
            document.body.insertAdjacentHTML('afterbegin', '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert"><strong>Error:</strong> Chart.js library not loaded. Please check your internet connection.</div>');
            return;
        } else {
            console.log('Chart.js is loaded successfully');
        }
        
        // Existing chart initialization code...
        // Blood Type Distribution Chart
        const bloodTypeCtx = document.getElementById('bloodTypeChart');
        const bloodTypeChart = new Chart(bloodTypeCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($blood_type_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($blood_type_counts); ?>,
                    backgroundColor: [
                        '#e53e3e', '#f56565', '#fc8181', '#feb2b2', 
                        '#9c4221', '#dd6b20', '#f6ad55', '#fbd38d'
                    ],
                    borderColor: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                        'rgba(45, 55, 72, 1)' : 'rgba(255, 255, 255, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                'rgba(255, 255, 255, 0.8)' : 'rgba(0, 0, 0, 0.8)'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value * 100) / total).toFixed(1);
                                return `${label}: ${value} donors (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        
        // Monthly Donations Chart
        const monthlyCtx = document.getElementById('monthlyDonationsChart');
        if (monthlyCtx) {
            const monthlyChart = new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($monthly_labels); ?>,
                    datasets: [{
                        label: 'Donations',
                        data: <?php echo json_encode($monthly_data); ?>,
                        backgroundColor: 'rgba(220, 38, 38, 0.1)',
                        borderColor: 'rgba(220, 38, 38, 1)',
                        borderWidth: 3,
                        tension: 0.3,
                        fill: true,
                        pointBackgroundColor: 'rgba(220, 38, 38, 1)',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.7)'
                            },
                            grid: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.7)'
                            },
                            grid: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.8)' : 'rgba(0, 0, 0, 0.8)'
                            }
                        }
                    }
                }
            });
        }
        
        // Location Chart
        const locationCtx = document.getElementById('locationChart');
        if (locationCtx) {
            const locationChart = new Chart(locationCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($location_labels); ?>,
                    datasets: [{
                        label: 'Donors',
                        data: <?php echo json_encode($location_data); ?>,
                        backgroundColor: 'rgba(220, 38, 38, 0.7)',
                        borderColor: 'rgba(220, 38, 38, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.7)'
                            },
                            grid: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        y: {
                            ticks: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.7)'
                            },
                            grid: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.8)' : 'rgba(0, 0, 0, 0.8)'
                            }
                        }
                    }
                }
            });
        }
        
        // Donor History Timeline Chart
        const historyCtx = document.getElementById('donorHistoryChart');
        if (historyCtx) {
            const statusColors = {
                'pending': 'rgba(251, 211, 141, 0.8)',   // Orange
                'approved': 'rgba(104, 211, 145, 0.8)',  // Green
                'completed': 'rgba(104, 211, 145, 0.8)'  // Green
            };
            
            const statusData = <?php echo json_encode($user_status); ?>;
            const backgroundColors = statusData.map(status => statusColors[status] || 'rgba(160, 174, 192, 0.8)');
            
            const historyChart = new Chart(historyCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($user_dates); ?>,
                    datasets: [{
                        label: 'Donation History',
                        data: statusData.map(() => 1), // Each date gets a value of 1
                        backgroundColor: backgroundColors,
                        borderColor: backgroundColors.map(color => color.replace('0.8', '1')),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            display: false,
                            beginAtZero: true,
                            max: 1.5
                        },
                        x: {
                            ticks: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.7)'
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const status = statusData[context.dataIndex];
                                    return `Status: ${status}`;
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Inventory Chart
        const inventoryCtx = document.getElementById('inventoryChart');
        if (inventoryCtx) {
            const bloodGroups = <?php echo json_encode($blood_groups); ?>;
            const inventoryValues = bloodGroups.map(group => 
                <?php echo json_encode($inventory_data); ?>[group] || 0
            );
            
            const inventoryChart = new Chart(inventoryCtx, {
                type: 'bar',
                data: {
                    labels: bloodGroups,
                    datasets: [{
                        label: 'Available Units',
                        data: inventoryValues,
                        backgroundColor: 'rgba(220, 38, 38, 0.7)',
                        borderColor: 'rgba(220, 38, 38, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Units of Blood',
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.7)'
                                },
                            ticks: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.7)'
                            },
                            grid: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.7)'
                            },
                            grid: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.8)' : 'rgba(0, 0, 0, 0.8)'
                            }
                        }
                    }
                }
            });
        }
        
        // Global Donation Chart
        const globalCtx = document.getElementById('globalDonationChart');
        if (globalCtx) {
            const globalChart = new Chart(globalCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($global_years); ?>,
                    datasets: [
                        {
                            label: 'Donation Rates (%)',
                            data: <?php echo json_encode($global_donation_rates); ?>,
                            backgroundColor: 'rgba(220, 38, 38, 0.1)',
                            borderColor: 'rgba(220, 38, 38, 1)',
                            borderWidth: 3,
                            tension: 0.3,
                            fill: true,
                            pointBackgroundColor: 'rgba(220, 38, 38, 1)',
                            pointRadius: 4
                        },
                        {
                            label: 'Voluntary Donations (%)',
                            data: <?php echo json_encode($voluntary_percentages); ?>,
                            backgroundColor: 'rgba(5, 150, 105, 0.1)',
                            borderColor: 'rgba(5, 150, 105, 1)',
                            borderWidth: 3,
                            tension: 0.3,
                            fill: true,
                            pointBackgroundColor: 'rgba(5, 150, 105, 1)',
                            pointRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.7)'
                            },
                            grid: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.7)'
                            },
                            grid: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.8)' : 'rgba(0, 0, 0, 0.8)'
                            }
                        }
                    }
                }
            });
        }
        
        // Forecast Chart
        const forecastCtx = document.getElementById('forecastChart');
        if (forecastCtx) {
            const forecastChart = new Chart(forecastCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($forecast_months); ?>,
                    datasets: [{
                        label: 'Forecasted Donations',
                        data: <?php echo json_encode($forecast_data); ?>,
                        backgroundColor: 'rgba(124, 58, 237, 0.1)',
                        borderColor: 'rgba(124, 58, 237, 1)',
                        borderWidth: 3,
                        tension: 0.3,
                        fill: true,
                        pointBackgroundColor: 'rgba(124, 58, 237, 1)',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.7)'
                            },
                            grid: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.7)'
                            },
                            grid: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.8)' : 'rgba(0, 0, 0, 0.8)'
                            }
                        }
                    }
                }
            });
        }
        
        // Supply Chain Chart
        const supplyChainCtx = document.getElementById('supplyChainChart');
        if (supplyChainCtx) {
            const supplyChainChart = new Chart(supplyChainCtx, {
                type: 'bar',
                data: {
                    labels: Object.values(<?php echo json_encode($supply_chain_data); ?>).map(stage => stage.label),
                    datasets: [{
                        label: 'Units',
                        data: Object.values(<?php echo json_encode($supply_chain_data); ?>).map(stage => stage.count),
                        backgroundColor: Object.values(<?php echo json_encode($supply_chain_data); ?>).map(stage => stage.color),
                        borderColor: Object.values(<?php echo json_encode($supply_chain_data); ?>).map(stage => stage.color.replace('0.8', '1')),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.7)'
                            },
                            grid: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.7)'
                            },
                            grid: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 
                                    'rgba(255, 255, 255, 0.8)' : 'rgba(0, 0, 0, 0.8)'
                            }
                        }
                    }
                }
            });
        }
        
        // Listen for dark mode changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            // Reload the page to update charts with appropriate colors
            window.location.reload();
        });
    } catch (error) {
        console.error('An error occurred while initializing charts:', error);
    }
});
</script>

<?php include_once '../includes/footer.php'; ?>