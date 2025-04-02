<!-- app/core/connectdb.php -->
<?php
try {
    // we create a connection variable that stores a new PDO object
    $connection = new PDO('mysql:host=localhost;dbname=conference', "root", "");
} catch (PDOException $e) {
    echo 'Error!: ' . $e->getMessage() . '<br/>';
	// Prints a message and terminates the script. 
    die();
}
?>