<?php
// Add this line to include authentication functions
include_once 'includes/auth.php';
include_once 'includes/header.php'; // Header already includes session_start() and db.php

// Get search parameters
$blood_group = $_GET['blood_group'] ?? '';
$city = trim($_GET['city'] ?? '');
$state = trim($_GET['state'] ?? '');
$age_min_param = $_GET['age_min'] ?? '';
$age_max_param = $_GET['age_max'] ?? '';

// Determine if a filter action was explicitly taken (button click or non-empty filter value)
$filter_active = isset($_GET['filter']) || !empty($blood_group) || !empty($city) || !empty($state) || $age_min_param !== '' || $age_max_param !== '';

// Default age values
$age_min = ($age_min_param !== '') ? intval($age_min_param) : 18;
$age_max = ($age_max_param !== '') ? intval($age_max_param) : 65;

// Ensure min/max are valid
if ($age_min < 18) $age_min = 18;
if ($age_max > 65 || $age_max < $age_min) $age_max = 65;

$donors = [];
$search_performed = true; // Always try to display results on this page

// Build the base query for eligible donors
// We only select basic info here; contact details are fetched on the contact page
$query = "SELECT id, name, blood_group, city, state, last_donation_date FROM users
          WHERE (last_donation_date IS NULL OR last_donation_date <= DATE_SUB(CURDATE(), INTERVAL 3 MONTH))";
$params = [];
$types = "";

// Add filters only if they are provided
if (!empty($blood_group)) {
    $query .= " AND blood_group = ?";
    $params[] = $blood_group;
    $types .= "s";
}

if (!empty($city)) {
    $query .= " AND city LIKE ?";
    $params[] = "%$city%";
    $types .= "s";
}

if (!empty($state)) {
    $query .= " AND state LIKE ?";
    $params[] = "%$state%";
    $types .= "s";
}

// Apply age filter only if non-default values were provided or other filters are active
if ($age_min_param !== '' || $age_max_param !== '' || $filter_active) {
     // Use the specified values or defaults if other filters are active
    $current_age_min = ($age_min_param !== '') ? $age_min : 18;
    $current_age_max = ($age_max_param !== '') ? $age_max : 65;

    $query .= " AND age BETWEEN ? AND ?";
    $params[] = $current_age_min;
    $params[] = $current_age_max;
    $types .= "ii";
}
// Note: If no filters are active at all, the age filter is NOT applied by default.


$query .= " ORDER BY last_donation_date ASC, name ASC";

// Prepare and execute the query
$stmt = $conn->prepare($query);

if ($stmt === false) {
    error_log("Failed to prepare statement: " . $conn->error);
    $donors = []; // Ensure donors array is empty
} else {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        // Handle query execution error
        error_log("Failed to execute statement: " . $stmt->error);
        $donors = [];
    } else {
        $result = $stmt->get_result();
        // Clear the array before fetching new results
        $donors = [];
        while ($row = $result->fetch_assoc()) {
            $donors[] = $row; // Append each row
        }
    }
    $stmt->close();
}

// Get unique cities and states for filtering options
$cities_query = "SELECT DISTINCT city FROM users ORDER BY city";
$cities_result = mysqli_query($conn, $cities_query);
$cities = [];
while ($row = mysqli_fetch_assoc($cities_result)) {
    $cities[] = $row['city'];
}

$states_query = "SELECT DISTINCT state FROM users ORDER BY state";
$states_result = mysqli_query($conn, $states_query);
$states = [];
while ($row = mysqli_fetch_assoc($states_result)) {
    $states[] = $row['state'];
}
?>

<div class="bg-gray-100 dark:bg-gray-800 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold text-center mb-8 dark:text-white">Find Blood Donors</h1>

        <!-- Search Form -->
        <div class="bg-white dark:bg-[#252525] rounded-lg shadow-md p-6 mb-8">
            <form method="GET" action="" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2" for="blood_group">Blood Group</label>
                        <select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-light dark:bg-gray-800 dark:text-white" name="blood_group" id="blood_group">
                            <option value="" <?php echo empty($blood_group) ? 'selected' : ''; ?>>Any</option>
                            <option value="A+" <?php echo $blood_group === 'A+' ? 'selected' : ''; ?>>A+</option>
                            <option value="A-" <?php echo $blood_group === 'A-' ? 'selected' : ''; ?>>A-</option>
                            <option value="B+" <?php echo $blood_group === 'B+' ? 'selected' : ''; ?>>B+</option>
                            <option value="B-" <?php echo $blood_group === 'B-' ? 'selected' : ''; ?>>B-</option>
                            <option value="AB+" <?php echo $blood_group === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                            <option value="AB-" <?php echo $blood_group === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                            <option value="O+" <?php echo $blood_group === 'O+' ? 'selected' : ''; ?>>O+</option>
                            <option value="O-" <?php echo $blood_group === 'O-' ? 'selected' : ''; ?>>O-</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2" for="city">City</label>
                        <input class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-light dark:bg-gray-800 dark:text-white"
                               type="text" name="city" id="city" value="<?php echo htmlspecialchars($city); ?>" placeholder="Any City" list="cities">
                        <datalist id="cities">
                            <?php foreach ($cities as $city_option): ?>
                                <option value="<?php echo htmlspecialchars($city_option); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div>
                        <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2" for="state">State</label>
                        <input class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-light dark:bg-gray-800 dark:text-white"
                               type="text" name="state" id="state" value="<?php echo htmlspecialchars($state); ?>" placeholder="Any State" list="states">
                        <datalist id="states">
                            <?php foreach ($states as $state_option): ?>
                                <option value="<?php echo htmlspecialchars($state_option); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div>
                        <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2">Age Range</label>
                        <div class="flex items-center space-x-2">
                            <input class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-light dark:bg-gray-800 dark:text-white"
                                   type="number" name="age_min" min="18" max="65" value="<?php echo htmlspecialchars($age_min_param); // Display the actual parameter value ?>" placeholder="Min (18)">
                            <span class="dark:text-gray-200">to</span>
                            <input class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-light dark:bg-gray-800 dark:text-white"
                                   type="number" name="age_max" min="18" max="65" value="<?php echo htmlspecialchars($age_max_param); // Display the actual parameter value ?>" placeholder="Max (65)">
                        </div>
                    </div>
                </div>

                <div class="flex justify-center">
                    <button type="submit" name="filter" value="1" class="bg-primary-dark hover:bg-primary-light text-white font-semibold py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-light focus:ring-opacity-50 transition duration-200">
                        <i class="fas fa-search mr-2"></i> Filter Donors
                    </button>
                    <a href="search.php" class="ml-4 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition duration-200">Reset Filters</a>
                </div>
            </form>
        </div>

        <!-- Results Table -->
        <?php if ($search_performed): ?>
            <div class="bg-white dark:bg-[#252525] rounded-lg shadow-md">
                <div class="p-4 border-b bg-gray-50 dark:bg-[#1E1E1E] dark:border-gray-600">
                    <h2 class="text-xl font-bold text-primary-dark dark:text-primary-light">Eligible Donors (<?php echo count($donors); ?> found)</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Only donors eligible to donate (last donation over 3 months ago or never) are shown.</p>
                </div>

                <?php if (!empty($donors)): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full donor-search-table">
                            <thead class="bg-gray-50 dark:bg-[#1E1E1E]">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Blood Group</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Donation</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                <?php foreach ($donors as $donor): ?>
                                    <tr class="dark:bg-[#252525] dark:text-white">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php echo htmlspecialchars($donor['name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-light/10 text-primary-dark dark:bg-primary-dark/30 dark:text-primary-light blood-type-label">
                                                <?php echo htmlspecialchars($donor['blood_group']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php echo htmlspecialchars($donor['city']) . ', ' . htmlspecialchars($donor['state']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <?php
                                            if (!empty($donor['last_donation_date'])) {
                                                echo date("M j, Y", strtotime($donor['last_donation_date']));
                                            } else {
                                                echo '<span class="text-gray-500 dark:text-gray-400">Never donated</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php // Check login status to show appropriate link ?>
                                            <?php if (is_donor_logged_in()): ?>
                                                <a href="<?php echo BASE_URL; ?>/contact_donor.php?id=<?php echo $donor['id']; ?>" class="text-accent-dark dark:text-accent-light hover:underline font-semibold">
                                                  <i class="fas fa-phone-alt mr-1"></i> Contact
                                                </a>
                                            <?php else: ?>
                                                <?php // Add redirect back to search after login ?>
                                                <?php $login_url = BASE_URL . "/login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']); ?>
                                                <a href="<?php echo $login_url; ?>" class="text-primary-dark dark:text-primary-light hover:underline">
                                                  <i class="fas fa-sign-in-alt mr-1"></i> Login to Contact
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-6 text-center">
                        <p class="text-gray-500 dark:text-gray-400">No eligible donors found matching your criteria.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Blood Compatibility Information -->
        <div class="mt-8">
            <h2 class="text-xl font-bold mb-4 text-primary-dark dark:text-primary-light">Blood Type Compatibility Guide</h2>
            <div class="bg-white dark:bg-[#252525] rounded-lg shadow-md p-6">
                <div class="overflow-x-auto">
                    <table class="w-full donor-search-table">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">Blood Type</th>
                                <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">Can Donate To</th>
                                <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-200">Can Receive From</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            <tr class="dark:bg-[#252525]">
                                <td class="px-4 py-3"><span class="font-semibold bg-primary-light/10 text-primary-dark dark:bg-primary-dark/30 dark:text-primary-light px-2 py-1 rounded blood-type-label">A+</span></td>
                                <td class="px-4 py-3 dark:text-gray-200">A+, AB+</td>
                                <td class="px-4 py-3 dark:text-gray-200">A+, A-, O+, O-</td>
                            </tr>
                            <tr class="dark:bg-[#252525]">
                                <td class="px-4 py-3"><span class="font-semibold bg-primary-light/10 text-primary-dark dark:bg-primary-dark/30 dark:text-primary-light px-2 py-1 rounded blood-type-label">A-</span></td>
                                <td class="px-4 py-3 dark:text-gray-200">A+, A-, AB+, AB-</td>
                                <td class="px-4 py-3 dark:text-gray-200">A-, O-</td>
                            </tr>
                            <tr class="dark:bg-[#252525]">
                                <td class="px-4 py-3"><span class="font-semibold bg-primary-light/10 text-primary-dark dark:bg-primary-dark/30 dark:text-primary-light px-2 py-1 rounded blood-type-label">B+</span></td>
                                <td class="px-4 py-3 dark:text-gray-200">B+, AB+</td>
                                <td class="px-4 py-3 dark:text-gray-200">B+, B-, O+, O-</td>
                            </tr>
                            <tr class="dark:bg-[#252525]">
                                <td class="px-4 py-3"><span class="font-semibold bg-primary-light/10 text-primary-dark dark:bg-primary-dark/30 dark:text-primary-light px-2 py-1 rounded blood-type-label">B-</span></td>
                                <td class="px-4 py-3 dark:text-gray-200">B+, B-, AB+, AB-</td>
                                <td class="px-4 py-3 dark:text-gray-200">B-, O-</td>
                            </tr>
                            <tr class="dark:bg-[#252525]">
                                <td class="px-4 py-3"><span class="font-semibold bg-primary-light/10 text-primary-dark dark:bg-primary-dark/30 dark:text-primary-light px-2 py-1 rounded blood-type-label">AB+</span></td>
                                <td class="px-4 py-3 dark:text-gray-200">AB+ only</td>
                                <td class="px-4 py-3 dark:text-gray-200">All blood types</td>
                            </tr>
                            <tr class="dark:bg-[#252525]">
                                <td class="px-4 py-3"><span class="font-semibold bg-primary-light/10 text-primary-dark dark:bg-primary-dark/30 dark:text-primary-light px-2 py-1 rounded blood-type-label">AB-</span></td>
                                <td class="px-4 py-3 dark:text-gray-200">AB+, AB-</td>
                                <td class="px-4 py-3 dark:text-gray-200">AB-, A-, B-, O-</td>
                            </tr>
                            <tr class="dark:bg-[#252525]">
                                <td class="px-4 py-3"><span class="font-semibold bg-primary-light/10 text-primary-dark dark:bg-primary-dark/30 dark:text-primary-light px-2 py-1 rounded blood-type-label">O+</span></td>
                                <td class="px-4 py-3 dark:text-gray-200">O+, A+, B+, AB+</td>
                                <td class="px-4 py-3 dark:text-gray-200">O+, O-</td>
                            </tr>
                            <tr class="dark:bg-[#252525]">
                                <td class="px-4 py-3"><span class="font-semibold bg-primary-light/10 text-primary-dark dark:bg-primary-dark/30 dark:text-primary-light px-2 py-1 rounded blood-type-label">O-</span></td>
                                <td class="px-4 py-3 dark:text-gray-200">All blood types</td>
                                <td class="px-4 py-3 dark:text-gray-200">O- only</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>