<?php



ini_set('memory_limit', '256M');

set_time_limit(300); 


require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/mail/send.php'; 


$short_opts = "a:h"; 
$long_opts = [
    "action:",        
    "id:",            
    "status:",        
    "search:",        
    "blood_group:",   
    "city:",          
    "state:",         
    "title:",         
    "location:",      
    "date:",          
    "description:",   
    "donor_id:",      
    "name:",          
    "email:",         
    "phone:",         
    "age:",           
    "last_donation_date:", 
    "help"            
];

$options = getopt($short_opts, $long_opts);

$action = $options['action'] ?? ($options['a'] ?? null);
$help = isset($options['help']) || isset($options['h']);


function display_help() {
    echo "Blood Donation Admin CLI Tool\n";
    echo "Usage: php admin_cli.php --action=<action_name> [options]\n\n";
    echo "Actions:\n";
    echo "  --action=view_donors        View registered donors.\n";
    echo "                              Options: --search, --blood_group, --city, --state\n";
    echo "  --action=update_donor       Update donor details.\n"; 
    echo "                              Required: --id=<donor_id>\n";
    echo "                              Optional: --name, --phone, --age, --blood_group, --city, --state, --last_donation_date (YYYY-MM-DD or '')\n";
    echo "  --action=delete_donor       Delete a donor.\n"; 
    echo "                              Required: --id=<donor_id>\n";
    echo "  --action=view_camps         View blood camps.\n";
    echo "                              Options: --search, --city, --state\n";
    echo "  --action=add_camp           Add a new blood camp.\n";
    echo "                              Required: --title, --location, --city, --state, --date (YYYY-MM-DD)\n";
    echo "                              Optional: --description\n";
    echo "  --action=update_camp        Update camp details.\n"; 
    echo "                              Required: --id=<camp_id>\n";
    echo "                              Optional: --title, --location, --city, --state, --date, --description\n";
    echo "  --action=delete_camp        Delete a blood camp.\n"; 
    echo "                              Required: --id=<camp_id>\n";
    echo "  --action=view_appointments  View appointments.\n";
    echo "                              Options: --search (donor name), --status, --donor_id, --date (YYYY-MM-DD)\n";
    echo "  --action=update_appt_status Update appointment status.\n";
    echo "                              Required: --id=<appt_id> --status=<new_status>\n";
    echo "                              (status: pending, approved, completed, rejected)\n";
    echo "  --action=view_requests      View blood requests.\n"; 
    echo "                              Options: --search (requester name), --status, --blood_group, --city, --state\n";
    echo "  --action=update_request_status Update blood request status.\n"; 
    echo "                              Required: --id=<request_id> --status=<new_status>\n";
    echo "                              (status: pending, contacted, completed, closed)\n";

    echo "\nOptions:\n";
    echo "  --id=<id>                 Specify record ID.\n";
    echo "  --status=<status>         Specify status.\n";
    echo "  --search=<term>           Search term.\n";
    echo "  --blood_group=<group>     Blood group.\n";
    echo "  --city=<city_name>        City name.\n";
    echo "  --state=<state_name>      State name.\n";
    echo "  --title=<text>            Camp title.\n";
    echo "  --location=<text>         Camp location.\n";
    echo "  --date=<YYYY-MM-DD>       Date.\n";
    echo "  --description=<text>      Camp description.\n";
    echo "  --donor_id=<id>           Donor ID.\n";
    echo "  --name=<name>             Donor name.\n";
    echo "  --phone=<number>          Donor phone.\n";
    echo "  --age=<number>            Donor age.\n";
    echo "  --last_donation_date=<date> Donor last donation date (YYYY-MM-DD or '' to clear).\n";
    echo "  -h, --help                Show this help message.\n\n";
    exit(0);
}


if ($help || !$action) {
    display_help();
}


function print_table(array $headers, array $data) {
    if (empty($data)) {
        echo "No records found.\n";
        return;
    }

    
    $widths = [];
    foreach ($headers as $key => $header) {
        $widths[$key] = strlen($header);
    }
    foreach ($data as $row) {
        foreach ($row as $key => $value) {
            $widths[$key] = max($widths[$key], strlen($value ?? ''));
        }
    }

    
    $header_line = '';
    $separator_line = '';
    foreach ($headers as $key => $header) {
        $header_line .= str_pad($header, $widths[$key]) . ' | ';
        $separator_line .= str_repeat('-', $widths[$key]) . '-+-';
    }
    echo rtrim($header_line, ' | ') . "\n";
    echo rtrim($separator_line, '-+-') . "\n";

    
    foreach ($data as $row) {
        $row_line = '';
        foreach ($headers as $key => $header) { 
             $value = $row[$key] ?? ''; 
             $row_line .= str_pad($value, $widths[$key]) . ' | ';
        }
        echo rtrim($row_line, ' | ') . "\n";
    }
}

function confirm_action(string $prompt = "Are you sure?"): bool {
    while (true) {
        $response = strtolower(readline($prompt . " (yes/no): "));
        if ($response === 'yes') {
            return true;
        } elseif ($response === 'no') {
            return false;
        }
        echo "Please enter 'yes' or 'no'.\n";
    }
}



global $conn; 

switch ($action) {
    case 'view_donors':
        view_donors($options);
        break;
    case 'view_camps':
        view_camps($options);
        break;
    case 'add_camp':
        add_camp($options);
        break;
    case 'update_appt_status':
        update_appointment_status($options);
        break;
    case 'update_donor': 
        update_donor($options);
        break;
    case 'delete_donor': 
        delete_donor($options);
        break;
    case 'update_camp': 
        update_camp($options);
        break;
    case 'delete_camp': 
        delete_camp($options);
        break;
    case 'view_requests': 
        view_requests($options);
        break;
    case 'update_request_status': 
        update_request_status($options);
        break;
    
    default:
        echo "Error: Unknown action '$action'. Use --help for available actions.\n";
        exit(1);
}



function view_donors(array $options) {
    global $conn;
    $query = "SELECT id, name, email, phone, age, blood_group, city, state, last_donation_date FROM users WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($options['search'])) {
        $search = "%" . $options['search'] . "%";
        $query .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR city LIKE ? OR state LIKE ?)";
        array_push($params, $search, $search, $search, $search, $search);
        $types .= "sssss";
    }
    if (!empty($options['blood_group'])) {
        $query .= " AND blood_group = ?";
        $params[] = $options['blood_group'];
        $types .= "s";
    }
     if (!empty($options['city'])) {
        $query .= " AND city LIKE ?";
        $params[] = "%" . $options['city'] . "%";
        $types .= "s";
    }
    if (!empty($options['state'])) {
        $query .= " AND state LIKE ?";
         $params[] = "%" . $options['state'] . "%";
        $types .= "s";
    }
    $query .= " ORDER BY name ASC";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
         die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $donors = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo "--- Donors List ---\n";
    print_table([
        'id' => 'ID',
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'age' => 'Age',
        'blood_group' => 'Blood',
        'city' => 'City',
        'state' => 'State',
        'last_donation_date' => 'Last Donated'
    ], $donors);
}

function view_camps(array $options) {
    global $conn;
    $query = "SELECT id, title, location, city, state, date, description FROM blood_camps WHERE 1=1";
     $params = [];
    $types = "";

    if (!empty($options['search'])) {
        $search = "%" . $options['search'] . "%";
        $query .= " AND (title LIKE ? OR location LIKE ? OR description LIKE ?)";
        array_push($params, $search, $search, $search);
        $types .= "sss";
    }
     if (!empty($options['city'])) {
        $query .= " AND city LIKE ?";
        $params[] = "%" . $options['city'] . "%";
        $types .= "s";
    }
    if (!empty($options['state'])) {
        $query .= " AND state LIKE ?";
         $params[] = "%" . $options['state'] . "%";
        $types .= "s";
    }
    $query .= " ORDER BY date DESC";

    $stmt = $conn->prepare($query);
     if (!$stmt) {
         die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $camps = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo "--- Blood Camps List ---\n";
    print_table([
        'id' => 'ID',
        'title' => 'Title',
        'date' => 'Date',
        'location' => 'Location',
        'city' => 'City',
        'state' => 'State',
        'description' => 'Description'
    ], $camps);
}

function add_camp(array $options) {
    global $conn;
    
    $required = ['title', 'location', 'city', 'state', 'date'];
    foreach ($required as $field) {
        if (empty($options[$field])) {
            echo "Error: Missing required option --$field for add_camp action.\n";
            display_help();
            exit(1);
        }
    }

    
    if (DateTime::createFromFormat('Y-m-d', $options['date']) === false) {
         echo "Error: Invalid date format for --date. Use YYYY-MM-DD.\n";
         exit(1);
    }
     if (new DateTime($options['date']) < new DateTime()) {
         echo "Warning: Camp date is in the past.\n";
     }


    $title = $options['title'];
    $location = $options['location'];
    $city = $options['city'];
    $state = $options['state'];
    $date = $options['date'];
    $description = $options['description'] ?? '';

    $stmt = $conn->prepare("INSERT INTO blood_camps (title, location, city, state, date, description) VALUES (?, ?, ?, ?, ?, ?)");
     if (!$stmt) {
         die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    $stmt->bind_param("ssssss", $title, $location, $city, $state, $date, $description);

    if ($stmt->execute()) {
        echo "Success: Blood camp '$title' added successfully with ID: " . $stmt->insert_id . "\n";
    } else {
        echo "Error: Failed to add blood camp. " . $stmt->error . "\n";
    }
    $stmt->close();
}

function update_appointment_status(array $options) {
    global $conn;
    $appointment_id = $options['id'] ?? null;
    $new_status = $options['status'] ?? null;

    if (!$appointment_id || !$new_status) {
        echo "Error: Missing required options --id and --status for update_appt_status action.\n";
        display_help();
        exit(1);
    }

    $allowed_statuses = ['pending', 'approved', 'completed', 'rejected'];
    if (!in_array($new_status, $allowed_statuses)) {
        echo "Error: Invalid status '$new_status'. Allowed statuses: " . implode(', ', $allowed_statuses) . "\n";
        exit(1);
    }

    
    $fetch_stmt = $conn->prepare("SELECT a.user_id, a.appointment_date, u.email, u.name FROM appointments a JOIN users u ON a.user_id = u.id WHERE a.id = ?");
     if (!$fetch_stmt) die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    $fetch_stmt->bind_param("i", $appointment_id);
    $fetch_stmt->execute();
    $result = $fetch_stmt->get_result();
    $details = $result->fetch_assoc();
    $fetch_stmt->close();

    if (!$details) {
        echo "Error: Appointment with ID $appointment_id not found.\n";
        exit(1);
    }

    $donor_id = $details['user_id'];
    $appointment_date = $details['appointment_date'];
    $donor_email = $details['email'];
    $donor_name = $details['name'];


    $update_stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
     if (!$update_stmt) die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    $update_stmt->bind_param("si", $new_status, $appointment_id);

    if ($update_stmt->execute()) {
        echo "Success: Appointment ID $appointment_id status updated to '$new_status'.\n";

        
        if ($new_status === 'completed') {
            $update_donor_stmt = $conn->prepare("UPDATE users SET last_donation_date = ? WHERE id = ?");
             if (!$update_donor_stmt) die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            $update_donor_stmt->bind_param("si", $appointment_date, $donor_id);
            if ($update_donor_stmt->execute()) {
                 echo "Updated last donation date for donor ID $donor_id.\n";
            } else {
                 echo "Warning: Failed to update last donation date for donor ID $donor_id.\n";
            }
            $update_donor_stmt->close();
        }

        
        $subject = "Appointment Status Update - Blood Donation System";
        $formatted_date = date("F j, Y", strtotime($appointment_date));
        $message = "Dear " . htmlspecialchars($donor_name) . ",<br><br>The status of your blood donation appointment scheduled for <strong>" . $formatted_date . "</strong> has been updated to: <strong>" . ucfirst($new_status) . "</strong>.<br><br>";
        
        $message .= "Best regards,<br>The Blood Donation Team";

        if (send_email($donor_email, $subject, $message)) {
            echo "Notification email sent to $donor_email.\n";
        } else {
            echo "Warning: Failed to send notification email to $donor_email.\n";
        }

    } else {
        echo "Error: Failed to update appointment status. " . $update_stmt->error . "\n";
    }
    $update_stmt->close();
}

function update_donor(array $options) { 
    global $conn;
    $donor_id = $options['id'] ?? null;

    if (!$donor_id) {
        echo "Error: Missing required option --id for update_donor action.\n";
        display_help();
        exit(1);
    }

    
    $fetch_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    if (!$fetch_stmt) die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    $fetch_stmt->bind_param("i", $donor_id);
    $fetch_stmt->execute();
    $result = $fetch_stmt->get_result();
    $current_donor = $result->fetch_assoc();
    $fetch_stmt->close();

    if (!$current_donor) {
        echo "Error: Donor with ID $donor_id not found.\n";
        exit(1);
    }

    
    $update_fields = [];
    $update_params = [];
    $update_types = "";

    $allowed_fields = ['name', 'phone', 'age', 'blood_group', 'city', 'state', 'last_donation_date'];
    $field_types = ['name'=>'s', 'phone'=>'s', 'age'=>'i', 'blood_group'=>'s', 'city'=>'s', 'state'=>'s', 'last_donation_date'=>'s'];

    foreach ($allowed_fields as $field) {
        if (isset($options[$field])) {
            $value = $options[$field];
            
            if ($field === 'age') {
                $value = intval($value);
                if ($value < 18) {
                     echo "Error: Age must be 18 or above.\n"; exit(1);
                }
            } elseif ($field === 'last_donation_date') {
                if ($value === '') {
                    $value = null; 
                } elseif (DateTime::createFromFormat('Y-m-d', $value) === false) {
                    echo "Error: Invalid date format for --last_donation_date. Use YYYY-MM-DD or ''.\n"; exit(1);
                }
            }

            if ($value !== $current_donor[$field]) { 
                 $update_fields[] = "$field = ?";
                 $update_params[] = $value;
                 $update_types .= $field_types[$field];
            }
        }
    }

    if (empty($update_fields)) {
        echo "No changes detected. Nothing to update.\n";
        exit(0);
    }

    
    $update_params[] = $donor_id;
    $update_types .= "i";

    $query = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
    $stmt = $conn->prepare($query);
     if (!$stmt) die("Prepare failed: (" . $conn->errno . ") " . $conn->error);

    $stmt->bind_param($update_types, ...$update_params);

    if ($stmt->execute()) {
        echo "Success: Donor ID $donor_id updated successfully.\n";
    } else {
        echo "Error: Failed to update donor ID $donor_id. " . $stmt->error . "\n";
    }
    $stmt->close();
}

function delete_donor(array $options) { 
    global $conn;
    $donor_id = $options['id'] ?? null;

    if (!$donor_id) {
        echo "Error: Missing required option --id for delete_donor action.\n";
        display_help();
        exit(1);
    }

    
    $name = "ID $donor_id";
    $fetch_stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    if ($fetch_stmt) {
        $fetch_stmt->bind_param("i", $donor_id);
        $fetch_stmt->execute();
        $result = $fetch_stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $name = $row['name'] . " (ID: $donor_id)";
        }
        $fetch_stmt->close();
    }


    if (confirm_action("Are you sure you want to delete donor '$name'? This is irreversible!")) {
        
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
         if (!$stmt) die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        $stmt->bind_param("i", $donor_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo "Success: Donor '$name' deleted successfully.\n";
            } else {
                echo "Warning: Donor with ID $donor_id not found or already deleted.\n";
            }
        } else {
            echo "Error: Failed to delete donor '$name'. " . $stmt->error . "\n";
        }
        $stmt->close();
    } else {
        echo "Action cancelled.\n";
    }
}

function update_camp(array $options) { 
    global $conn;
    $camp_id = $options['id'] ?? null;

    if (!$camp_id) {
        echo "Error: Missing required option --id for update_camp action.\n";
        display_help();
        exit(1);
    }

     
    $fetch_stmt = $conn->prepare("SELECT * FROM blood_camps WHERE id = ?");
    if (!$fetch_stmt) die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    $fetch_stmt->bind_param("i", $camp_id);
    $fetch_stmt->execute();
    $result = $fetch_stmt->get_result();
    $current_camp = $result->fetch_assoc();
    $fetch_stmt->close();

    if (!$current_camp) {
        echo "Error: Camp with ID $camp_id not found.\n";
        exit(1);
    }

    
    $update_fields = [];
    $update_params = [];
    $update_types = "";

    $allowed_fields = ['title', 'location', 'city', 'state', 'date', 'description'];

    foreach ($allowed_fields as $field) {
        if (isset($options[$field])) {
             $value = $options[$field];
             
             if ($field === 'date' && DateTime::createFromFormat('Y-m-d', $value) === false) {
                 echo "Error: Invalid date format for --date. Use YYYY-MM-DD.\n"; exit(1);
             }

             if ($value !== $current_camp[$field]) { 
                 $update_fields[] = "$field = ?";
                 $update_params[] = $value;
                 $update_types .= "s"; 
             }
        }
    }

     if (empty($update_fields)) {
        echo "No changes detected. Nothing to update.\n";
        exit(0);
    }

    
    $update_params[] = $camp_id;
    $update_types .= "i";

    $query = "UPDATE blood_camps SET " . implode(', ', $update_fields) . " WHERE id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) die("Prepare failed: (" . $conn->errno . ") " . $conn->error);

    $stmt->bind_param($update_types, ...$update_params);

    if ($stmt->execute()) {
        echo "Success: Camp ID $camp_id updated successfully.\n";
    } else {
        echo "Error: Failed to update camp ID $camp_id. " . $stmt->error . "\n";
    }
    $stmt->close();
}

function delete_camp(array $options) { 
    global $conn;
    $camp_id = $options['id'] ?? null;

    if (!$camp_id) {
        echo "Error: Missing required option --id for delete_camp action.\n";
        display_help();
        exit(1);
    }

     
    $title = "ID $camp_id";
    $fetch_stmt = $conn->prepare("SELECT title FROM blood_camps WHERE id = ?");
    if ($fetch_stmt) {
        $fetch_stmt->bind_param("i", $camp_id);
        $fetch_stmt->execute();
        $result = $fetch_stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $title = $row['title'] . " (ID: $camp_id)";
        }
        $fetch_stmt->close();
    }

    if (confirm_action("Are you sure you want to delete camp '$title'?")) {
        $stmt = $conn->prepare("DELETE FROM blood_camps WHERE id = ?");
         if (!$stmt) die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        $stmt->bind_param("i", $camp_id);

        if ($stmt->execute()) {
             if ($stmt->affected_rows > 0) {
                echo "Success: Camp '$title' deleted successfully.\n";
            } else {
                echo "Warning: Camp with ID $camp_id not found or already deleted.\n";
            }
        } else {
            echo "Error: Failed to delete camp '$title'. " . $stmt->error . "\n";
        }
        $stmt->close();
    } else {
        echo "Action cancelled.\n";
    }
}

function view_requests(array $options) { 
    global $conn;
    $query = "SELECT r.id, r.requester_name, r.blood_group, r.city, r.state, r.status, r.created_at, u.name as matched_donor
              FROM requests r
              LEFT JOIN users u ON r.matched_donor_id = u.id
              WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($options['search'])) {
        $search = "%" . $options['search'] . "%";
        $query .= " AND r.requester_name LIKE ?";
        $params[] = $search;
        $types .= "s";
    }
     if (!empty($options['status'])) {
        $query .= " AND r.status = ?";
        $params[] = $options['status'];
        $types .= "s";
    }
    if (!empty($options['blood_group'])) {
        $query .= " AND r.blood_group = ?";
        $params[] = $options['blood_group'];
        $types .= "s";
    }
     if (!empty($options['city'])) {
        $query .= " AND r.city LIKE ?";
        $params[] = "%" . $options['city'] . "%";
        $types .= "s";
    }
    if (!empty($options['state'])) {
        $query .= " AND r.state LIKE ?";
         $params[] = "%" . $options['state'] . "%";
        $types .= "s";
    }

    $query .= " ORDER BY r.created_at DESC";

    $stmt = $conn->prepare($query);
    if (!$stmt) die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $requests = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo "--- Blood Requests List ---\n";
    print_table([
        'id' => 'Req ID',
        'requester_name' => 'Requester',
        'blood_group' => 'Blood',
        'city' => 'City',
        'state' => 'State',
        'status' => 'Status',
        'created_at' => 'Requested On',
        'matched_donor' => 'Matched Donor'
    ], $requests);
}

function update_request_status(array $options) { 
    global $conn;
    $request_id = $options['id'] ?? null;
    $new_status = $options['status'] ?? null;

    if (!$request_id || !$new_status) {
        echo "Error: Missing required options --id and --status for update_request_status action.\n";
        display_help();
        exit(1);
    }

    $allowed_statuses = ['pending', 'contacted', 'completed', 'closed'];
    if (!in_array($new_status, $allowed_statuses)) {
        echo "Error: Invalid status '$new_status'. Allowed statuses: " . implode(', ', $allowed_statuses) . "\n";
        exit(1);
    }

    

    $stmt = $conn->prepare("UPDATE requests SET status = ? WHERE id = ?");
    if (!$stmt) die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    $stmt->bind_param("si", $new_status, $request_id);

    if ($stmt->execute()) {
         if ($stmt->affected_rows > 0) {
             echo "Success: Request ID $request_id status updated to '$new_status'.\n";
             
         } else {
             echo "Warning: Request ID $request_id not found or status already set to '$new_status'.\n";
         }
    } else {
        echo "Error: Failed to update request status. " . $stmt->error . "\n";
    }
    $stmt->close();
}



$conn->close();
?>