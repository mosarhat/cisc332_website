<?php
require_once __DIR__ . '/../database/database.php';

$query = "SELECT DISTINCT companyName FROM JobPost ORDER BY companyName";
$stmt = $connection->prepare($query);
$stmt->execute();
$companies = $stmt->fetchAll(PDO::FETCH_COLUMN);

$selectedCompany = isset($_GET['company']) ? $_GET['company'] : '';

if ($selectedCompany !== '') {
    $query = "SELECT jobTitle, companyName, city, province, payRate 
              FROM JobPost 
              WHERE companyName = ? 
              ORDER BY jobTitle";
    $stmt = $connection->prepare($query);
    $stmt->execute([$selectedCompany]);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $query = "SELECT jobTitle, companyName, city, province, payRate 
              FROM JobPost 
              ORDER BY companyName, jobTitle";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Board</title>
    <link rel="stylesheet" href="/cisc332_website/public/styles/style.css">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <div align="center">
        <form method="get">
            <p><span class="subheading">Select Company</span></p>
            <?php foreach ($companies as $comp): ?>
                <button 
                    type="submit" 
                    name="company" 
                    value="<?= htmlspecialchars($comp) ?>" 
                    class="items <?= $selectedCompany === $comp ? 'toggled' : '' ?>"
                >
                    <?= htmlspecialchars($comp) ?>
                </button>
            <?php endforeach; ?>
            <button 
                type="submit" 
                name="company" 
                value=""
                class="items <?= $selectedCompany === '' ? 'toggled' : '' ?>"
            >
                All Companies
            </button>
        </form>
    </div>

    <?php if ($jobs): ?>
        <table border="2" style="border-collapse: collapse; width: 90%; margin: 20px auto;">
            <thead>
                <tr>
                    <th style="padding: 10px;">Job Title</th>
                    <th style="padding: 10px;">Company</th>
                    <th style="padding: 10px;">City</th>
                    <th style="padding: 10px;">Province</th>
                    <th style="padding: 10px;">Pay Rate (Per Annum)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td style="padding: 10px;"><?= htmlspecialchars($job['jobTitle']) ?></td>
                        <td style="padding: 10px;"><?= htmlspecialchars($job['companyName']) ?></td>
                        <td style="padding: 10px;"><?= htmlspecialchars($job['city']) ?></td>
                        <td style="padding: 10px;"><?= htmlspecialchars($job['province']) ?></td>
                        <!-- pay rate should be * 12 to represent the monthly earnings (per annum) -->
                        <td style="padding: 10px;">$<?= number_format($job['payRate'] * 12, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center;">No job postings available.</p>
    <?php endif; ?>
</body>
</html>