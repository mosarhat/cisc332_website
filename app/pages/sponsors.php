<?php
require_once __DIR__ . '/../database/database.php';

function getMaxEmailsByTier($tier) {
    return match($tier) {
        'Platinum' => 20,
        'Gold' => 15,
        'Silver' => 10,
        'Bronze' => 5,
        default => 0
    };
}

$editingCompany = null;
if (isset($_GET['edit'])) {
    $editingCompanyName = $_GET['edit'];
    $stmt = $connection->prepare("SELECT * FROM SponsorCompany WHERE companyName = ?");
    $stmt->execute([$editingCompanyName]);
    $editingCompany = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteCompany'])) {
    $companyName = $_POST['companyName'];
    $stmt = $connection->prepare("DELETE FROM SponsorCompany WHERE companyName = ?");
    $stmt->execute([$companyName]);
    header("Location: sponsors.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateCompany'])) {
    $companyName = $_POST['companyName'];
    $tier = $_POST['tier'];
    $stmt = $connection->prepare("UPDATE SponsorCompany SET tier = ? WHERE companyName = ?");
    $stmt->execute([$tier, $companyName]);
    header("Location: sponsors.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addCompany'])) {
    $companyName = $_POST['companyName'];
    $tier = $_POST['tier'];
    $stmt = $connection->prepare("INSERT INTO SponsorCompany (companyName, tier) VALUES (?, ?)");
    $stmt->execute([$companyName, $tier]);
    header("Location: sponsors.php");
    exit;
}

$stmt = $connection->prepare("SELECT * FROM SponsorCompany ORDER BY tier DESC, companyName");
$stmt->execute();
$sponsorCompanies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sponsor Management</title>
    <link rel="stylesheet" href="/cisc332_website/public/styles/style.css">
</head>
<body>
    <?php include '../components/navbar.php'; ?>
    <p><span class="subheading">View Sponsors</span></p>

    <table border="2" style="border-collapse: collapse; width: 90%; margin: 20px auto;">
        <tr>
            <th style="padding: 10px;">Company Name</th>
            <th style="padding: 10px;">Sponsorship Tier</th>
            <th style="padding: 10px;">Emails Sent</th>
            <th style="padding: 10px;">Emails Left</th>
            <th style="padding: 10px;">Actions</th>
        </tr>
        <?php foreach ($sponsorCompanies as $company): ?>
            <tr class="<?= ($editingCompany && $editingCompany['companyName'] === $company['companyName']) ? 'highlighted-row' : '' ?>">
            <td style="padding: 10px;"><?= htmlspecialchars($company['companyName']) ?></td>
            <td style="padding: 10px;"><?= htmlspecialchars($company['tier']) ?></td>
            <td style="padding: 10px;"><?= htmlspecialchars($company['emailsSent']) ?></td>
            <td style="padding: 10px;"><?= getMaxEmailsByTier($company['tier']) - $company['emailsSent'] ?></td>
            <td style="padding: 10px;">
                <a href="sponsors.php?edit=<?= urlencode($company['companyName']) ?>" class="edit-link <?= ($editingCompany && $editingCompany['companyName'] === $company['companyName']) ? 'edit-active' : '' ?>">Edit</a>
            </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php if ($editingCompany): ?>
        <div class="edit-session-container">
            <div align="center">
                <p class="modal-title">Edit Sponsor Company</p>
            </div>
            <form method="post" class="edit-session-form">
                <input type="hidden" name="updateCompany" value="1">
                <input type="hidden" name="companyName" value="<?= htmlspecialchars($editingCompany['companyName']) ?>">

                <div class="form-group">
                    <label for="tier">Sponsorship Tier:</label>
                    <select name="tier" class="gradient-select" required>
                        <option value="Platinum" <?= $editingCompany['tier'] === 'Platinum' ? 'selected' : '' ?>>Platinum</option>
                        <option value="Gold" <?= $editingCompany['tier'] === 'Gold' ? 'selected' : '' ?>>Gold</option>
                        <option value="Silver" <?= $editingCompany['tier'] === 'Silver' ? 'selected' : '' ?>>Silver</option>
                        <option value="Bronze" <?= $editingCompany['tier'] === 'Bronze' ? 'selected' : '' ?>>Bronze</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit">Update Company</button>
                    <button type="button" onclick="window.location.href='sponsors.php'">Cancel</button>
                    <button type="button" onclick="confirmDelete('<?= htmlspecialchars($editingCompany['companyName']) ?>')">Delete Sponsor</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="edit-session-container">
            <div align="center">
                <p class="modal-title">Add Sponsor Company</p>
            </div>
            <form method="post" class="edit-session-form">
                <input type="hidden" name="addCompany" value="1">

                <div class="form-group">
                    <label for="companyName">Company Name:</label>
                    <input type="text" name="companyName" required>
                </div>

                <div class="form-group">
                    <label for="tier">Sponsorship Tier:</label>
                    <select name="tier" class="gradient-select" required>
                        <option value="">-- Select Tier --</option>
                        <option value="Platinum">Platinum</option>
                        <option value="Gold">Gold</option>
                        <option value="Silver">Silver</option>
                        <option value="Bronze">Bronze</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit">Add Sponsor</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <form id="deleteForm" method="post" style="display:none;">
        <input type="hidden" name="deleteCompany" value="1">
        <input type="hidden" id="deleteCompanyName" name="companyName">
    </form>

    <script>
        function confirmDelete(companyName) {
            if (confirm('Are you sure you want to delete this sponsor and their attendees? This action cannot be undone.')) {
                document.getElementById('deleteCompanyName').value = companyName;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>
