<?php
require_once __DIR__ . '/../database/database.php';

$query = "SELECT roomNumber FROM Room ORDER BY roomNumber";
$stmt = $connection->prepare($query);
$stmt->execute();
$rooms = $stmt->fetchAll(PDO::FETCH_COLUMN);

$selectedRoom = isset($_GET['room']) ? $_GET['room'] : '';

$students = [];
$numberOfBeds = null;
if ($selectedRoom !== '') {

    $query = "SELECT numberOfBeds FROM Room WHERE roomNumber = ?";
    $stmt = $connection->prepare($query);
    $stmt->execute([$selectedRoom]);
    $numberOfBeds = $stmt->fetchColumn();

    $query = "SELECT a.firstName, a.lastName 
              FROM Student s
              JOIN Attendee a ON s.attendeeID = a.attendeeID
              WHERE s.roomID = ?";
    $stmt = $connection->prepare($query);
    $stmt->execute([$selectedRoom]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms & Students</title>
    <link rel="stylesheet" href="/cisc332_website/public/styles/style.css">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <p class="subheading" style="text-align:center;">List Students in a Hotel Room</p>
    <div style="text-align: center;">
        <form method="get">
            <select name="room" class="gradient-select">
                <option value="">-- Select Room --</option>
                <?php foreach ($rooms as $room): ?>
                    <option value="<?= htmlspecialchars($room) ?>" <?= ($selectedRoom == $room) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($room) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="items" id="viewBtn">View Students</button>
        </form>
    </div>

    <?php if ($selectedRoom !== ''): ?>
        <p style="text-align: center;">
            Students in Room <?= htmlspecialchars($selectedRoom) ?> : <?= count($students) ?>
            <?php if ($numberOfBeds > 1): ?>
                <br>Number of Beds: <?= htmlspecialchars($numberOfBeds) ?> <br/> Number of Available Beds: <?= htmlspecialchars($numberOfBeds - count($students)) ?>
            <?php else: ?>
                <br>There is 1 bed.
            <?php endif; ?>
        </p>
        <?php if (!empty($students)): ?>
            <table border="2" style="border-collapse: collapse; width: 90%; margin: 20px auto;">
            <tr>
                <th style="padding: 10px;">First Name</th>
                <th style="padding: 10px;">Last Name</th>
            </tr>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td style="padding: 10px;"><?= htmlspecialchars($student['firstName']) ?></td>
                    <td style="padding: 10px;"><?= htmlspecialchars($student['lastName']) ?></td>
                </tr>
            <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p style="text-align: center;">No students assigned to this room.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>