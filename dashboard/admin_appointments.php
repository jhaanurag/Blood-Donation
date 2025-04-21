<?php
session_start();
include_once '../includes/db.php';
include_once '../includes/auth.php';

// Only admins should be able to access this page
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: ../login.php");
    exit;
}

include_once '../includes/header.php';
?>

<div class="bg-gray-100 dark:bg-gray-900 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold dark:text-white">Manage Appointments</h1>
                <a href="<?php echo isset($_SESSION['admin_id']) ? 'admin_dashboard.php' : '../index.php'; ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 font-semibold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
            
            <?php 
            // Display success/error messages
            if (isset($_SESSION['success'])) {
                echo '<div class="bg-green-100 border border-green-400 text-green-700 dark:bg-green-900 dark:border-green-700 dark:text-green-200 px-4 py-3 rounded relative mb-6" role="alert">';
                echo '<span class="block sm:inline">' . htmlspecialchars($_SESSION['success']) . '</span>';
                echo '</div>';
                unset($_SESSION['success']);
            }
            
            if (isset($_SESSION['error'])) {
                echo '<div class="bg-red-100 border border-red-400 text-red-700 dark:bg-red-900 dark:border-red-700 dark:text-red-200 px-4 py-3 rounded relative mb-6" role="alert">';
                echo '<span class="block sm:inline">' . htmlspecialchars($_SESSION['error']) . '</span>';
                echo '</div>';
                unset($_SESSION['error']);
            }
            ?>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-2 dark:text-white">Appointment Filters</h2>
                    <div class="flex flex-col md:flex-row gap-4">
                        <button class="filter-btn bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300 px-4 py-2 rounded hover:bg-blue-200 dark:hover:bg-blue-800" data-filter="all">All</button>
                        <button class="filter-btn bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-300 px-4 py-2 rounded hover:bg-yellow-200 dark:hover:bg-yellow-800" data-filter="pending">Pending</button>
                        <button class="filter-btn bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-300 px-4 py-2 rounded hover:bg-indigo-200 dark:hover:bg-indigo-800" data-filter="approved">Approved</button>
                        <button class="filter-btn bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300 px-4 py-2 rounded hover:bg-green-200 dark:hover:bg-green-800" data-filter="completed">Completed</button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-700 text-left">
                                <th class="px-6 py-3 text-gray-600 dark:text-gray-300 font-medium">ID</th>
                                <th class="px-6 py-3 text-gray-600 dark:text-gray-300 font-medium">Donor</th>
                                <th class="px-6 py-3 text-gray-600 dark:text-gray-300 font-medium">Blood Group</th>
                                <th class="px-6 py-3 text-gray-600 dark:text-gray-300 font-medium">Date</th>
                                <th class="px-6 py-3 text-gray-600 dark:text-gray-300 font-medium">Status</th>
                                <th class="px-6 py-3 text-gray-600 dark:text-gray-300 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Get all appointments with user information
                            $query = "SELECT a.id, a.appointment_date, a.status, a.user_id, 
                                      u.name, u.blood_group, u.email, u.phone 
                                      FROM appointments a 
                                      JOIN users u ON a.user_id = u.id 
                                      ORDER BY a.status = 'pending' DESC, a.appointment_date ASC";
                            $result = mysqli_query($conn, $query);
                            
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $status_class = '';
                                    $status_text = '';
                                    
                                    switch ($row['status']) {
                                        case 'pending':
                                            $status_class = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
                                            $status_text = 'Pending';
                                            break;
                                        case 'approved':
                                            $status_class = 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300';
                                            $status_text = 'Approved';
                                            break;
                                        case 'completed':
                                            $status_class = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
                                            $status_text = 'Completed';
                                            break;
                                    }
                                    
                                    echo '<tr class="border-b dark:border-gray-700 appointment-row" data-status="' . $row['status'] . '">';
                                    echo '<td class="px-6 py-4 dark:text-white">' . $row['id'] . '</td>';
                                    echo '<td class="px-6 py-4">';
                                    echo '<div class="dark:text-white font-medium">' . htmlspecialchars($row['name']) . '</div>';
                                    echo '<div class="text-sm text-gray-600 dark:text-gray-400">' . htmlspecialchars($row['email']) . '</div>';
                                    echo '<div class="text-sm text-gray-600 dark:text-gray-400">' . htmlspecialchars($row['phone']) . '</div>';
                                    echo '</td>';
                                    echo '<td class="px-6 py-4 dark:text-white font-medium">' . htmlspecialchars($row['blood_group']) . '</td>';
                                    echo '<td class="px-6 py-4 dark:text-white">' . date("M j, Y", strtotime($row['appointment_date'])) . '</td>';
                                    echo '<td class="px-6 py-4"><span class="px-2 py-1 rounded ' . $status_class . '">' . $status_text . '</span></td>';
                                    echo '<td class="px-6 py-4">';
                                    echo '<div class="flex flex-col sm:flex-row gap-2">';
                                    
                                    // Show update status options based on current status
                                    if ($row['status'] !== 'completed') {
                                        echo '<form method="POST" action="update_appointment.php" class="inline-block">';
                                        echo '<input type="hidden" name="appointment_id" value="' . $row['id'] . '">';
                                        echo '<input type="hidden" name="user_id" value="' . $row['user_id'] . '">';
                                        
                                        // If pending, show approve option
                                        if ($row['status'] == 'pending') {
                                            echo '<button type="submit" name="status" value="approved" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-1 rounded">';
                                            echo 'Approve</button>';
                                        }
                                        
                                        // If pending or approved, show mark as completed option
                                        if ($row['status'] == 'pending' || $row['status'] == 'approved') {
                                            echo '<button type="submit" name="status" value="completed" class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1 rounded ml-1">';
                                            echo 'Mark Completed</button>';
                                        }
                                        
                                        echo '</form>';
                                    }
                                    
                                    echo '</div>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="6" class="px-6 py-4 text-center dark:text-white">No appointments found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Appointment filtering
    const filterButtons = document.querySelectorAll('.filter-btn');
    const appointmentRows = document.querySelectorAll('.appointment-row');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('ring-2', 'ring-offset-2'));
            
            // Add active class to clicked button
            this.classList.add('ring-2', 'ring-offset-2');
            
            const filter = this.getAttribute('data-filter');
            
            // Filter appointments
            appointmentRows.forEach(row => {
                if (filter === 'all' || row.getAttribute('data-status') === filter) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        });
    });
    
    // Set "All" as default active filter
    document.querySelector('[data-filter="all"]').click();
});
</script>

<?php include_once '../includes/footer.php'; ?>