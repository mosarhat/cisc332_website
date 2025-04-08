<?php
require_once __DIR__ . '/../database/database.php';

$query = "SELECT subCommitteeID, committeeName FROM SubCommittee ORDER BY committeeName";
$stmt = $connection->prepare($query);
$stmt->execute();
$subCommittees = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selectedSubCommittee = isset($_GET['subCommittee']) ? $_GET['subCommittee'] : '';

$committeeMembers = [];
if ($selectedSubCommittee !== '') {
    $query = "
        SELECT cm.fname, cm.lname,
            CASE 
                WHEN sc.chairMemberID = cm.committeeMemberID THEN 'Chair'
                ELSE 'Member'
            END AS role
        FROM CommitteeMember cm
        JOIN committeeMemberOf cmo ON cm.committeeMemberID = cmo.committeeMemberID
        JOIN SubCommittee sc ON cmo.subCommitteeID = sc.subCommitteeID
        WHERE cmo.subCommitteeID = ?
    ";
    $stmt = $connection->prepare($query);
    $stmt->execute([$selectedSubCommittee]);
    $committeeMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Committee Management</title>
    <link rel="stylesheet" href="/cisc332_website/public/styles/style.css">
</head>
<body>

    <?php include '../components/navbar.php'; ?>

    <p class="subheading" style="text-align:center;">Organizing Sub-Committee Members</p>

    <div style="text-align: center;">
        <form method="get">
            <select name="subCommittee" class="gradient-select">
                <option value="">-- Select Sub-Committee --</option>
                <?php foreach ($subCommittees as $subCommittee): ?>
                    <option value="<?= htmlspecialchars($subCommittee['subCommitteeID']) ?>" <?= ($selectedSubCommittee == $subCommittee['subCommitteeID']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($subCommittee['committeeName']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="items">View Members</button>
        </form>
    </div>

    <?php if ($selectedSubCommittee !== ''): ?>
        <h3 class="subheading" style="text-align:center;">
            Members of <?= htmlspecialchars($subCommittees[array_search($selectedSubCommittee, array_column($subCommittees, 'subCommitteeID'))]['committeeName']) ?>
        </h3>

        <?php if (!empty($committeeMembers)): ?>
            <table border="2" style="border-collapse: collapse; width: 90%; margin: 20px auto;">
                <tr>
                    <th style="padding: 10px;">First Name</th>
                    <th style="padding: 10px;">Last Name</th>
                    <th style="padding: 10px;">Role</th>
                </tr>
                <?php foreach ($committeeMembers as $member): ?>
                    <tr>
                        <td style="padding: 10px;"><?= htmlspecialchars($member['fname']) ?></td>
                        <td style="padding: 10px;"><?= htmlspecialchars($member['lname']) ?></td>
                        <td style="padding: 10px;"><?= htmlspecialchars($member['role']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p style="text-align: center;">No members found for this sub-committee.</p>
        <?php endif; ?>
    <?php endif; ?>

</body>
</html>
