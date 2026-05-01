<?php
// CEIT-SC Office Duty Tracker - Create Tables Script (Updated)
require_once 'config.php';

// Read and execute the SQL schema
$sql_file = 'database_schema.sql';
$sql_content = file_get_contents($sql_file);

// Split the SQL content into individual statements
$statements = explode(';', $sql_content);

echo "<h2>Creating Database Tables...</h2>";

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (!empty($statement)) {
        if ($conn->query($statement) === TRUE) {
            echo "<p style='color: green;'>✓ Executed: " . substr($statement, 0, 50) . "...</p>";
        } else {
            echo "<p style='color: red;'>✗ Error: " . $conn->error . "</p>";
            echo "<p>Statement: " . $statement . "</p>";
        }
    }
}

echo "<h3>Database setup completed!</h3>";
echo "<p><a href='index.php'>Go to Main Application</a></p>";

$conn->close();
?>

