<?php
require_once __DIR__ . '/../database/database.php';

$query = "SELECT SUM(attendanceFee) AS totalRegistration FROM Attendee";
$stmt = $connection->prepare($query);
$stmt->execute();
$totalRegistration = $stmt->fetchColumn();

$query = "SELECT SUM(emailsSent * 100) AS totalSponsorship FROM SponsorCompany";
$stmt = $connection->prepare($query);
$stmt->execute();
$totalSponsorship = $stmt->fetchColumn();

$totalIntake = $totalRegistration + $totalSponsorship;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Financials</title>
    <link rel="stylesheet" href="/cisc332_website/public/styles/style.css">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <p class="sub-title">Welcome to the intake page.</p>
    
    <div class="financials-container">
        <div class="financials-card">
            <dt>Total Registration</dt>
            <dd>$<?php echo number_format($totalRegistration, 2); ?></dd>
        </div>
        <div class="financials-card">
            <dt>Total Sponsorship</dt>
            <dd>$<?php echo number_format($totalSponsorship, 2); ?></dd>
        </div>
        <div class="financials-card">
            <dt>Total Intake</dt>
            <dd>$<?php echo number_format($totalIntake, 2); ?></dd>
        </div>
    </div>
</body>
</html>