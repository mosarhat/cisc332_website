<?php
require_once '../app/core/Router.php';
require_once '../app/core/connectdb.php';
require_once '../app/controllers/SessionController.php';
require_once '../app/models/SessionModel.php';

// Create router with database connection
$router = new Router($connection);

// Define routes with correct path
$router->add('/cisc332_website/schedule', 'SessionController', 'index');

// Get the current URL path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conference Home</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <?php 
    include 'components/navbar.php';
    $router->dispatch($path);
    ?>
</body>
</html>