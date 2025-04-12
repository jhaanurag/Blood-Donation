<?php
include_once 'includes/header.php';
include_once 'includes/db.php';

$blood_group = $_GET['blood_group'] ?? '';
$city = trim($_GET['city'] ?? '');
$state = trim($_GET['state'] ?? '');
$age_min_param = $_GET['age_min'] ?? '';
$age_max_param = $_GET['age_max'] ?? '';


$filter_active = isset($_GET['filter']) || !empty($blood_group) || !empty($city) || !empty($state) || $age_min_param !== '' || $age_max_param !== '';


$age_min = ($age_min_param !== '') ? intval($age_min_param) : 18;
$age_max = ($age_max_param !== '') ? intval($age_max_param) : 65;


if ($age_min < 18) $age_min = 18;
if ($age_max > 65 || $age_max < $age_min) $age_max = 65;

$donors = [];
$search_performed = true; 


$query = "SELECT id, name, blood_group, city, state, last_donation_date FROM users 
          WHERE (last_donation_date IS NULL OR last_donation_date <= DATE_SUB(CURDATE(), INTERVAL 3 MONTH))";
$params = [];
$types = "";


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


if ($age_min_param !== '' || $age_max_param !== '') {
    $query .= " AND age BETWEEN ? AND ?";
    $params[] = $age_min;
    $params[] = $age_max;
    $types .= "ii";
} elseif ($filter_active) {
    
    $query .= " AND age BETWEEN ? AND ?";
    $params[] = 18; 
    $params[] = 65; 
    $types .= "ii";
}



$query .= " ORDER BY last_donation_date ASC, name ASC";


$stmt = $conn->prepare($query);

if ($stmt === false) {
    
    error_log("Failed to prepare statement: " . $conn->error);
    $donors = []; 
} else {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        
        error_log("Failed to execute statement: " . $stmt->error);
        $donors = [];
    } else {
        $result = $stmt->get_result();
        
        $donors = [];
        while ($row = $result->fetch_assoc()) {
            $donors[] = $row; 
        }
    }
    $stmt->close();
}


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

<div class="bg-gray-100 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold text-center mb-8">Find Blood Donors</h1>
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form method="GET" action="" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="blood_group">Blood Group</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                                name="blood_group" id="blood_group">
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
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="city">City</label>
                        <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                               type="text" name="city" id="city" value="<?php echo htmlspecialchars($city); ?>" placeholder="Any City" list="cities">
                        <datalist id="cities">
                            <?php foreach ($cities as $city_option): ?>
                                <option value="<?php echo htmlspecialchars($city_option); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="state">State</label>
                        <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                               type="text" name="state" id="state" value="<?php echo htmlspecialchars($state); ?>" placeholder="Any State" list="states">
                        <datalist id="states">
                            <?php foreach ($states as $state_option): ?>
                                <option value="<?php echo htmlspecialchars($state_option); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Age Range</label>
                        <div class="flex items-center space-x-2">
                            <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                                   type="number" name="age_min" min="18" max="65" value="<?php echo $age_min; ?>" placeholder="Min (18)">
                            <span>to</span>
                            <input class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600" 
                                   type="number" name="age_max" min="18" max="65" value="<?php echo $age_max; ?>" placeholder="Max (65)">
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-center">
                    <button type="submit" name="filter" value="1" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-opacity-50">
                        <i class="fas fa-search mr-2"></i> Filter Donors
                    </button>
                    <a href="/search.php" class="ml-4 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Reset Filters</a>
                </div>
            </form>
        </div>
        
        <?php if ($search_performed): ?>
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-4 border-b bg-gray-50">
                    <h2 class="text-xl font-bold">Eligible Donors (<?php echo count($donors); ?> found)</h2>
                </div>
                
                <?php if (!empty($donors)): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Blood Group</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Donation</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($donors as $donor): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php echo htmlspecialchars($donor['name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
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
                                                echo '<span class="text-gray-500">Never donated</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php 
                                            <a href="/login.php" class="text-blue-600 hover:underline">Login to Contact</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-6 text-center">
                        <p class="text-gray-500">No eligible donors found matching your criteria.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Blood Compatibility Information -->
        <div class="mt-8">
            <h2 class="text-xl font-bold mb-4">Blood Type Compatibility Guide</h2>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">Blood Type</th>
                                <th class="px-4 py-2 text-left">Can Donate To</th>
                                <th class="px-4 py-2 text-left">Can Receive From</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <td class="px-4 py-3"><span class="font-semibold bg-red-100 text-red-800 px-2 py-1 rounded">A+</span></td>
                                <td class="px-4 py-3">A+, AB+</td>
                                <td class="px-4 py-3">A+, A-, O+, O-</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3"><span class="font-semibold bg-red-100 text-red-800 px-2 py-1 rounded">A-</span></td>
                                <td class="px-4 py-3">A+, A-, AB+, AB-</td>
                                <td class="px-4 py-3">A-, O-</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3"><span class="font-semibold bg-red-100 text-red-800 px-2 py-1 rounded">B+</span></td>
                                <td class="px-4 py-3">B+, AB+</td>
                                <td class="px-4 py-3">B+, B-, O+, O-</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3"><span class="font-semibold bg-red-100 text-red-800 px-2 py-1 rounded">B-</span></td>
                                <td class="px-4 py-3">B+, B-, AB+, AB-</td>
                                <td class="px-4 py-3">B-, O-</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3"><span class="font-semibold bg-red-100 text-red-800 px-2 py-1 rounded">AB+</span></td>
                                <td class="px-4 py-3">AB+ only</td>
                                <td class="px-4 py-3">All blood types</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3"><span class="font-semibold bg-red-100 text-red-800 px-2 py-1 rounded">AB-</span></td>
                                <td class="px-4 py-3">AB+, AB-</td>
                                <td class="px-4 py-3">AB-, A-, B-, O-</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3"><span class="font-semibold bg-red-100 text-red-800 px-2 py-1 rounded">O+</span></td>
                                <td class="px-4 py-3">O+, A+, B+, AB+</td>
                                <td class="px-4 py-3">O+, O-</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3"><span class="font-semibold bg-red-100 text-red-800 px-2 py-1 rounded">O-</span></td>
                                <td class="px-4 py-3">All blood types</td>
                                <td class="px-4 py-3">O- only</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>