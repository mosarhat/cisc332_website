<?php
require_once __DIR__ . '/../database/database.php';

$query = "SELECT DISTINCT sessionDate FROM Session ORDER BY sessionDate";
$stmt = $connection->prepare($query);
$stmt->execute();
$days = $stmt->fetchAll(PDO::FETCH_COLUMN);

$selectedDay = isset($_GET['day']) ? $_GET['day'] : null;

if ($selectedDay) {
    $query = "SELECT * FROM Session WHERE sessionDate = ? ORDER BY startTime";
    $stmt = $connection->prepare($query);
    $stmt->execute([$selectedDay]);
} else {
    $query = "SELECT * FROM Session ORDER BY sessionDate, startTime";
    $stmt = $connection->prepare($query);
    $stmt->execute();
}
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$editingSession = null;
if (isset($_GET['edit'])) {
    $sessionName = $_GET['sessionName'];
    $roomLocation = $_GET['roomLocation'];
    $startTime = $_GET['startTime'];
    $endTime = $_GET['endTime'];
    $sessionDate = $_GET['sessionDate'];

    $query = "SELECT * FROM Session WHERE sessionName = ? AND roomLocation = ? AND startTime = ? AND endTime = ? AND sessionDate = ?";
    $stmt = $connection->prepare($query);
    $stmt->execute([$sessionName, $roomLocation, $startTime, $endTime, $sessionDate]);
    $editingSession = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $newSessionName = $_POST['newSessionName'];
    $newRoomLocation = $_POST['newRoomLocation'];
    $newStartTime = $_POST['newStartTime'];
    $newEndTime = $_POST['newEndTime'];
    $newSessionDate = $_POST['newSessionDate'];
    $originalSessionName = $_POST['originalSessionName'];
    $originalRoomLocation = $_POST['originalRoomLocation'];
    $originalStartTime = $_POST['originalStartTime'];
    $originalEndTime = $_POST['originalEndTime'];
    $originalSessionDate = $_POST['originalSessionDate'];

    try {
        $connection->beginTransaction();

        $stmt = $connection->prepare("
            UPDATE speaksat 
            SET roomLocation = ?, startTime = ?, endTime = ?, sessionDate = ?
            WHERE roomLocation = ? AND startTime = ? AND endTime = ? AND sessionDate = ?
        ");
        $stmt->execute([
            $newRoomLocation, $newStartTime, $newEndTime, $newSessionDate,
            $originalRoomLocation, $originalStartTime, $originalEndTime, $originalSessionDate
        ]);

        $stmt = $connection->prepare("
            UPDATE Session 
            SET sessionName = ?, roomLocation = ?, startTime = ?, endTime = ?, sessionDate = ?
            WHERE sessionName = ? AND roomLocation = ? AND startTime = ? AND endTime = ? AND sessionDate = ?
        ");
        $stmt->execute([
            $newSessionName, $newRoomLocation, $newStartTime, $newEndTime, $newSessionDate,
            $originalSessionName, $originalRoomLocation, $originalStartTime, $originalEndTime, $originalSessionDate
        ]);

        $connection->commit();

        header("Location: schedule.php" . ($selectedDay ? "?day=" . urlencode($selectedDay) : ""));
        exit;
    } catch (PDOException $e) {
        $connection->rollBack();
        echo "Update failed: " . $e->getMessage();
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conference Schedule</title>
    <link rel="stylesheet" href="/cisc332_website/public/styles/style.css">
</head>

<body>
    <?php include '../components/navbar.php'; ?>

    <div align="center">
    <form method="get">
        <p><span class="subheading">Select a Day</span></p>
        <?php foreach ($days as $day): ?>
            <button 
                type="submit" 
                name="day" 
                value="<?= $day ?>" 
                class="items <?= $selectedDay == $day ? 'toggled' : '' ?>"
            >
                <?= date('F j, Y', strtotime($day)) ?>
            </button>
        <?php endforeach; ?>
        <button 
            type="submit" 
            name="day" 
            value="" 
            class="items <?= $selectedDay === null || $selectedDay === '' ? 'toggled' : '' ?>"
        >
            All Days
        </button>
        </form>
        <br/>
    </div>

    <table border="2" style="border-collapse: collapse; width: 90%; margin: 20px auto;">
        <tr>
            <th style="padding: 10px;">Session</th>
            <th style="padding: 10px;">Room</th>
            <th style="padding: 10px;">Start Time</th>
            <th style="padding: 10px;">End Time</th>
            <th style="padding: 10px;">Date</th>
            <th style="padding: 10px;">Actions</th>
        </tr>
        <?php foreach ($sessions as $session): ?>
            <?php 
                $isEditing = $editingSession && 
                            $editingSession['sessionName'] === $session['sessionName'] && 
                            $editingSession['roomLocation'] === $session['roomLocation'] && 
                            $editingSession['startTime'] === $session['startTime'] && 
                            $editingSession['endTime'] === $session['endTime'] && 
                            $editingSession['sessionDate'] === $session['sessionDate'];
            ?>
            <tr class="<?= $isEditing ? 'highlighted-row' : '' ?>">
                <td style="padding: 10px;"><?= htmlspecialchars($session['sessionName']) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($session['roomLocation']) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($session['startTime']) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($session['endTime']) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($session['sessionDate']) ?></td>
                <td style="padding: 10px;">
                    <a href="schedule.php?<?= $selectedDay ? 'day=' . urlencode($selectedDay) . '&' : '' ?>edit=1&sessionName=<?= urlencode($session['sessionName']) ?>&roomLocation=<?= urlencode($session['roomLocation']) ?>&startTime=<?= urlencode($session['startTime']) ?>&endTime=<?= urlencode($session['endTime']) ?>&sessionDate=<?= urlencode($session['sessionDate']) ?>" class="edit-link <?= $isEditing ? 'edit-active' : '' ?>">Edit</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php if ($editingSession): ?>
        <p class="subheading">Edit Session</p>
        <div class="edit-session-container">
            <form method="post" class="edit-session-form">
                <input type="hidden" name="originalSessionName" value="<?= htmlspecialchars($editingSession['sessionName']) ?>">
                <input type="hidden" name="originalRoomLocation" value="<?= htmlspecialchars($editingSession['roomLocation']) ?>">
                <input type="hidden" name="originalStartTime" value="<?= htmlspecialchars($editingSession['startTime']) ?>">
                <input type="hidden" name="originalEndTime" value="<?= htmlspecialchars($editingSession['endTime']) ?>">
                <input type="hidden" name="originalSessionDate" value="<?= htmlspecialchars($editingSession['sessionDate']) ?>">

                <div class="form-group">
                    <label for="newSessionName">Session Name:</label>
                    <input type="text" name="newSessionName" value="<?= htmlspecialchars($editingSession['sessionName']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="newRoomLocation">Room Location:</label>
                    <input type="text" name="newRoomLocation" value="<?= htmlspecialchars($editingSession['roomLocation']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="newStartTime">Start Time:</label>
                    <input type="time" name="newStartTime" value="<?= htmlspecialchars($editingSession['startTime']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="newEndTime">End Time:</label>
                    <input type="time" name="newEndTime" value="<?= htmlspecialchars($editingSession['endTime']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="newSessionDate">Date:</label>
                    <input type="date" name="newSessionDate" value="<?= htmlspecialchars($editingSession['sessionDate']) ?>" required>
                </div>

                <div class="form-actions">
                    <button type="submit" name="update">Update Session</button>
                    <button type="button" onclick="window.location.href='schedule.php<?= $selectedDay ? '?day=' . urlencode($selectedDay) : '' ?>'">Cancel Edit</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
    
</body>
</html>