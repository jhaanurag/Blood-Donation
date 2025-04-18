<?php
// setup.php - Configuration testing and setup script for Blood Donation System

// Check if the user requested a database import
$importStatus = '';
$importSuccess = false;

if (isset($_POST['import_database'])) {
    require_once './includes/config.php';
    
    // Attempt to connect to MySQL server (without selecting a database)
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
    
    if (!$conn) {
        $importStatus = "Failed to connect to database server: " . mysqli_connect_error();
    } else {
        // Check if database exists, create it if it doesn't
        $dbExists = mysqli_select_db($conn, DB_NAME);
        
        if (!$dbExists) {
            $createDbResult = mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS " . DB_NAME);
            if (!$createDbResult) {
                $importStatus = "Failed to create database: " . mysqli_error($conn);
                mysqli_close($conn);
                exit;
            }
            mysqli_select_db($conn, DB_NAME);
        }
        
        // Read the SQL file
        $sqlFile = file_get_contents('blood_donation.sql');
        
        if ($sqlFile === false) {
            $importStatus = "Could not read blood_donation.sql file";
        } else {
            // Split the SQL file into individual queries
            $sqlQueries = explode(';', $sqlFile);
            
            try {
                // Execute each query
                $success = true;
                mysqli_begin_transaction($conn);
                
                foreach ($sqlQueries as $query) {
                    $query = trim($query);
                    if (!empty($query)) {
                        $result = mysqli_query($conn, $query);
                        if (!$result) {
                            $success = false;
                            $importStatus = "Error importing database: " . mysqli_error($conn);
                            break;
                        }
                    }
                }
                
                if ($success) {
                    mysqli_commit($conn);
                    $importStatus = "Database imported successfully!";
                    $importSuccess = true;
                } else {
                    mysqli_rollback($conn);
                }
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $importStatus = "Exception during import: " . $e->getMessage();
            }
        }
        mysqli_close($conn);
    }
}

// Check PHP version
$requiredPhpVersion = '7.4.0';
$phpVersionCheck = version_compare(PHP_VERSION, $requiredPhpVersion, '>=');

// Check MySQL extension
$mysqlExtensionCheck = extension_loaded('mysqli');

// Check file permissions
$includesPermissionCheck = is_readable('./includes') && is_writable('./includes');
$mailPermissionCheck = is_readable('./mail') && is_writable('./mail');

// Database connection test (using values from includes/config.php)
$dbConnectionCheck = false;
$dbErrorMessage = '';
$dbExistsCheck = false;
$tablesExistCheck = false;

if (file_exists('./includes/config.php')) {
    require_once './includes/config.php';
    
    // Try to connect
    $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS);
    if ($conn) {
        $dbConnectionCheck = true;
        
        // Check if database exists
        $dbExistsCheck = mysqli_select_db($conn, DB_NAME);
        
        // Check if tables exist
        if ($dbExistsCheck) {
            $result = mysqli_query($conn, "SHOW TABLES");
            $tablesExistCheck = mysqli_num_rows($result) > 0;
        }
        
        mysqli_close($conn);
    } else {
        $dbErrorMessage = mysqli_connect_error();
    }
}

// Check for mail configuration
$mailConfigCheck = false;
if (function_exists('mail')) {
    $mailConfigCheck = true;
}

// Check common writeable directories for file uploads (if needed in future)
$uploadsPermissionCheck = is_dir('./assets') && is_writable('./assets');

// Web server information
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
$documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown';
$serverProtocol = $_SERVER['SERVER_PROTOCOL'] ?? 'Unknown';

// Start the HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Donation System - Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #d9534f;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        h2 {
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-top: 25px;
        }
        .check-item {
            margin: 10px 0;
            padding: 10px;
            border-radius: 3px;
        }
        .pass {
            background-color: #dff0d8;
            border-left: 4px solid #5cb85c;
        }
        .fail {
            background-color: #f2dede;
            border-left: 4px solid #d9534f;
        }
        .warning {
            background-color: #fcf8e3;
            border-left: 4px solid #f0ad4e;
        }
        .info {
            background-color: #d9edf7; 
            border-left: 4px solid #5bc0de;
        }
        .actions {
            margin-top: 25px;
            padding: 15px;
            background: #eee;
            border-radius: 3px;
        }
        .btn {
            display: inline-block;
            padding: 8px 15px;
            background: #d9534f;
            color: #fff;
            border: none;
            border-radius: 3px;
            text-decoration: none;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn:hover {
            background: #c9302c;
        }
        code {
            background: #f0f0f0;
            padding: 2px 5px;
            border-radius: 3px;
            font-family: monospace;
        }
        .success-message {
            padding: 10px;
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            color: #3c763d;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .error-message {
            padding: 10px;
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            color: #a94442;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Blood Donation System - Setup</h1>
        
        <?php if (!empty($importStatus)): ?>
            <div class="<?php echo $importSuccess ? 'success-message' : 'error-message'; ?>">
                <?php echo $importStatus; ?>
            </div>
        <?php endif; ?>
        
        <div class="check-item info">
            This script checks your system configuration to ensure the Blood Donation System will run correctly.
        </div>
        
        <h2>System Requirements</h2>
        
        <div class="check-item <?php echo $phpVersionCheck ? 'pass' : 'fail'; ?>">
            <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?>
            <?php if (!$phpVersionCheck): ?>
                <p>PHP version <?php echo $requiredPhpVersion; ?> or higher is required.</p>
            <?php endif; ?>
        </div>
        
        <div class="check-item <?php echo $mysqlExtensionCheck ? 'pass' : 'fail'; ?>">
            <strong>MySQL Extension:</strong> <?php echo $mysqlExtensionCheck ? 'Installed' : 'Not Installed'; ?>
            <?php if (!$mysqlExtensionCheck): ?>
                <p>The MySQLi extension is required. Please enable it in your PHP configuration.</p>
            <?php endif; ?>
        </div>
        
        <div class="check-item <?php echo $mailConfigCheck ? 'pass' : 'warning'; ?>">
            <strong>Mail Function:</strong> <?php echo $mailConfigCheck ? 'Available' : 'Not Available'; ?>
            <?php if (!$mailConfigCheck): ?>
                <p>The mail() function is not available. Email notifications will not work.</p>
            <?php endif; ?>
        </div>
        
        <h2>Permissions</h2>
        
        <div class="check-item <?php echo $includesPermissionCheck ? 'pass' : 'warning'; ?>">
            <strong>Includes Directory:</strong> <?php echo $includesPermissionCheck ? 'Readable/Writable' : 'Permission Issue'; ?>
            <?php if (!$includesPermissionCheck): ?>
                <p>The includes directory should be readable and writable.</p>
            <?php endif; ?>
        </div>
        
        <div class="check-item <?php echo $mailPermissionCheck ? 'pass' : 'warning'; ?>">
            <strong>Mail Directory:</strong> <?php echo $mailPermissionCheck ? 'Readable/Writable' : 'Permission Issue'; ?>
            <?php if (!$mailPermissionCheck): ?>
                <p>The mail directory should be readable and writable.</p>
            <?php endif; ?>
        </div>
        
        <div class="check-item <?php echo $uploadsPermissionCheck ? 'pass' : 'warning'; ?>">
            <strong>Assets Directory:</strong> <?php echo $uploadsPermissionCheck ? 'Readable/Writable' : 'Permission Issue'; ?>
            <?php if (!$uploadsPermissionCheck): ?>
                <p>The assets directory should be writable for file uploads.</p>
            <?php endif; ?>
        </div>
        
        <h2>URL Path Configuration</h2>
        
        <div class="check-item info">
            <strong>URL Paths:</strong> Some links in the application may use absolute paths (starting with "/").
            <p>If you're experiencing "Not Found" errors when clicking links, run the link fixer script:</p>
            <ol>
                <li>Open a command prompt/terminal</li>
                <li>Navigate to your project directory: <code>cd C:\xampp\htdocs\Blood-Donation</code></li>
                <li>Run the fixer script: <code>php fix_links.php</code></li>
            </ol>
            <p>Alternatively, access the application using: <strong>http://localhost/Blood-Donation/</strong></p>
        </div>
        
        <h2>Database Configuration</h2>
        
        <div class="check-item <?php echo $dbConnectionCheck ? 'pass' : 'fail'; ?>">
            <strong>Database Connection:</strong> <?php echo $dbConnectionCheck ? 'Connected Successfully' : 'Connection Failed'; ?>
            <?php if (!$dbConnectionCheck): ?>
                <p>Could not connect to the database server. Error: <?php echo $dbErrorMessage; ?></p>
                <p>Please check your database settings in <code>includes/config.php</code>.</p>
            <?php endif; ?>
        </div>
        
        <?php if ($dbConnectionCheck): ?>
        <div class="check-item <?php echo $dbExistsCheck ? 'pass' : 'fail'; ?>">
            <strong>Database '<?php echo DB_NAME; ?>':</strong> <?php echo $dbExistsCheck ? 'Exists' : 'Does Not Exist'; ?>
            <?php if (!$dbExistsCheck): ?>
                <p>The database '<?php echo DB_NAME; ?>' does not exist.</p>
            <?php endif; ?>
        </div>
        
        <div class="check-item <?php echo $tablesExistCheck ? 'pass' : 'warning'; ?>">
            <strong>Database Tables:</strong> <?php echo $tablesExistCheck ? 'Exist' : 'Not Found'; ?>
            <?php if (!$tablesExistCheck): ?>
                <p>No tables found in the database. You need to import the database schema.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <h2>Server Information</h2>
        
        <div class="check-item info">
            <strong>Server Software:</strong> <?php echo htmlspecialchars($serverSoftware); ?><br>
            <strong>Document Root:</strong> <?php echo htmlspecialchars($documentRoot); ?><br>
            <strong>Server Protocol:</strong> <?php echo htmlspecialchars($serverProtocol); ?><br>
            <strong>PHP SAPI:</strong> <?php echo php_sapi_name(); ?>
        </div>
        
        <div class="actions">
            <h3>Next Steps</h3>
            
            <?php if ($dbConnectionCheck && $dbExistsCheck && $tablesExistCheck && $phpVersionCheck && $mysqlExtensionCheck): ?>
                <p class="pass">Your system meets all requirements to run the Blood Donation System!</p>
                <p><a href="index.php" class="btn">Go to Homepage</a></p>
            <?php else: ?>
                <p>Please fix the issues highlighted above before using the system.</p>
                
                <?php if ($dbConnectionCheck && (!$dbExistsCheck || !$tablesExistCheck)): ?>
                <form method="post" action="setup.php">
                    <button type="submit" name="import_database" class="btn">Create/Import Database</button>
                </form>
                <p><small>This will create the database if needed and import all required tables from blood_donation.sql</small></p>
                <?php endif; ?>
                
                <p><a href="setup.php" class="btn">Refresh Check</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>