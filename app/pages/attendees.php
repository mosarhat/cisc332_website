<?php
require_once __DIR__ . '/../database/database.php';

$roomsStmt = $connection->prepare("SELECT roomNumber, numberOfBeds FROM Room ORDER BY roomNumber");
$roomsStmt->execute();
$hotelRooms = $roomsStmt->fetchAll(PDO::FETCH_ASSOC);

$sponsorsStmt = $connection->prepare("SELECT companyName FROM SponsorCompany ORDER BY companyName");
$sponsorsStmt->execute();
$sponsorCompanies = $sponsorsStmt->fetchAll(PDO::FETCH_ASSOC);

$editingAttendee = null;
$viewType = $_GET['view'] ?? '';

function getAttendeesByType($type, $connection) {
    if ($type === 'student') {
        $query = "SELECT a.attendeeID, a.firstName AS fname, a.lastName AS lname, a.attendanceFee, r.roomNumber 
                  FROM Attendee a 
                  JOIN Student s ON a.attendeeID = s.attendeeID 
                  JOIN Room r ON s.roomID = r.roomNumber 
                  ORDER BY a.lastName";
    } elseif ($type === 'professional') {
        $query = "SELECT a.attendeeID, a.firstName AS fname, a.lastName AS lname, a.attendanceFee 
                  FROM Attendee a 
                  JOIN Professional p ON a.attendeeID = p.attendeeID 
                  ORDER BY a.lastName";
    } elseif ($type === 'sponsor') {
        $query = "SELECT a.attendeeID, a.firstName AS fname, a.lastName AS lname, a.attendanceFee, sp.companyName 
                  FROM Attendee a 
                  JOIN Sponsor sp ON a.attendeeID = sp.attendeeID 
                  ORDER BY a.lastName";
    } else {
        $query = "SELECT attendeeID, firstName AS fname, lastName AS lname, attendanceFee 
                  FROM Attendee 
                  ORDER BY lastName";
    }
    $stmt = $connection->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$students = getAttendeesByType('student', $connection);
$professionals = getAttendeesByType('professional', $connection);
$sponsors = getAttendeesByType('sponsor', $connection);

if (isset($_GET['edit'])) {
    $attendeeID = $_GET['edit'];
    $query = "SELECT * FROM Attendee WHERE attendeeID = ?";
    $stmt = $connection->prepare($query);
    $stmt->execute([$attendeeID]);
    $editingAttendee = $stmt->fetch(PDO::FETCH_ASSOC);

    $type = null;
    $stmt = $connection->prepare("SELECT * FROM Student WHERE attendeeID = ?");
    $stmt->execute([$attendeeID]);
    if ($stmt->fetch()) $type = 'student';

    $stmt = $connection->prepare("SELECT * FROM Professional WHERE attendeeID = ?");
    $stmt->execute([$attendeeID]);
    if ($stmt->fetch()) $type = 'professional';

    $stmt = $connection->prepare("SELECT * FROM Sponsor WHERE attendeeID = ?");
    $sponsorData = $stmt->execute([$attendeeID]);
    if ($sponsorData = $stmt->fetch()) {
        $type = 'sponsor';
        $editingAttendee['companyName'] = $sponsorData['companyName'];
    }

    $editingAttendee['type'] = $type;
    if ($type === 'student') {
        $stmt = $connection->prepare("SELECT roomID FROM Student WHERE attendeeID = ?");
        $stmt->execute([$attendeeID]);
        $editingAttendee['roomID'] = $stmt->fetchColumn();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateAttendee'])) {
    $attendeeID = $_POST['attendeeID'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $fee = $_POST['fee'];
    $type = $_POST['type'];
    $roomID = $_POST['hotelRoomID'] ?? null;
    $companyName = $_POST['sponsorCompany'] ?? null;

    $connection->prepare("DELETE FROM Student WHERE attendeeID = ?")->execute([$attendeeID]);
    $connection->prepare("DELETE FROM Professional WHERE attendeeID = ?")->execute([$attendeeID]);
    $connection->prepare("DELETE FROM Sponsor WHERE attendeeID = ?")->execute([$attendeeID]);

    $stmt = $connection->prepare("UPDATE Attendee SET firstName = ?, lastName = ?, attendanceFee = ? WHERE attendeeID = ?");
    $stmt->execute([$fname, $lname, $fee, $attendeeID]);

    if ($type === 'student' && $roomID) {
        $stmt = $connection->prepare("INSERT INTO Student (attendeeID, roomID) VALUES (?, ?)");
        $stmt->execute([$attendeeID, $roomID]);
    } elseif ($type === 'professional') {
        $stmt = $connection->prepare("INSERT INTO Professional (attendeeID) VALUES (?)");
        $stmt->execute([$attendeeID]);
    } elseif ($type === 'sponsor' && $companyName) {
        $stmt = $connection->prepare("INSERT INTO Sponsor (attendeeID, companyName) VALUES (?, ?)");
        $stmt->execute([$attendeeID, $companyName]);
    }

    header("Location: attendees.php?view=" . urlencode($viewType));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addAttendee'])) {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $type = $_POST['type'];
    $fee = $_POST['fee'];
    $roomID = $_POST['hotelRoomID'] ?? null;
    $companyName = $_POST['sponsorCompany'] ?? null;

    $stmt = $connection->prepare("INSERT INTO Attendee (firstName, lastName, attendanceFee) VALUES (?, ?, ?)");
    $stmt->execute([$fname, $lname, $fee]);
    $attendeeID = $connection->lastInsertId();

    if ($type === 'student' && $roomID) {
        $stmt = $connection->prepare("INSERT INTO Student (attendeeID, roomID) VALUES (?, ?)");
        $stmt->execute([$attendeeID, $roomID]);
    } elseif ($type === 'professional') {
        $stmt = $connection->prepare("INSERT INTO Professional (attendeeID) VALUES (?)");
        $stmt->execute([$attendeeID]);
    } elseif ($type === 'sponsor' && $companyName) {
        $stmt = $connection->prepare("INSERT INTO Sponsor (attendeeID, companyName) VALUES (?, ?)");
        $stmt->execute([$attendeeID, $companyName]);
    }

    header("Location: attendees.php?view=" . urlencode($viewType));
    exit;
}

$roomsStmt = $connection->prepare("
    SELECT r.roomNumber, r.numberOfBeds, 
           (r.numberOfBeds - COUNT(s.attendeeID)) AS availableBeds
    FROM Room r
    LEFT JOIN Student s ON r.roomNumber = s.roomID
    GROUP BY r.roomNumber, r.numberOfBeds
    ORDER BY r.roomNumber
");
$roomsStmt->execute();
$hotelRooms = $roomsStmt->fetchAll(PDO::FETCH_ASSOC);
$hotelRooms = array_filter($hotelRooms, function ($room) {
    return $room['availableBeds'] > 0;
});

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteAttendee'])) {
    $attendeeID = $_POST['attendeeID'];
    
    try {
        $stmt = $connection->prepare("DELETE FROM Attendee WHERE attendeeID = ?");
        $stmt->execute([$attendeeID]);
        
        header("Location: attendees.php?view=" . urlencode($viewType));
        exit;
    } catch (PDOException $e) {
        error_log("Delete failed: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendee Management</title>
    <link rel="stylesheet" href="/cisc332_website/public/styles/style.css">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <div align="center">
        <form method="get">
            <p><span class="subheading">View Attendees By Type</span></p>
            <button type="submit" name="view" value="student" class="items <?= ($_GET['view'] ?? '') === 'student' ? 'toggled' : '' ?>">Students</button>
            <button type="submit" name="view" value="professional" class="items <?= ($_GET['view'] ?? '') === 'professional' ? 'toggled' : '' ?>">Professionals</button>
            <button type="submit" name="view" value="sponsor" class="items <?= ($_GET['view'] ?? '') === 'sponsor' ? 'toggled' : '' ?>">Sponsors</button>
            <button type="submit" name="view" value="" class="items <?= empty($_GET['view']) ? 'toggled' : '' ?>">All Attendees</button>
        </form>
        <br/>
    </div>

    <div style="width: 90%; margin: 0 auto;">
        <?php 
        $viewType = $_GET['view'] ?? '';
        if ($viewType === 'student' || $viewType === ''): ?>
            <?php if($viewType === ''): ?>
                <p class="subheading" align="center">Students</p>
            <?php endif; ?>
            <table border="2" style="border-collapse: collapse; width: 90%; margin: 20px auto;">
                <tr>
                    <th style="padding: 10px;">Name</th>
                    <th style="padding: 10px;">Room</th>
                    <th style="padding: 10px;">Fee</th>
                    <th style="padding: 10px;">Type</th>
                    <th style="padding: 10px;">Actions</th>
                </tr>
                <?php foreach ($students as $attendee): ?>
                    <?php 
                        $isEditing = $editingAttendee && $editingAttendee['attendeeID'] == $attendee['attendeeID'];
                    ?>
                    <tr class="<?= $isEditing ? 'highlighted-row' : '' ?>">
                        <td style="padding: 10px;"><?= htmlspecialchars($attendee['fname']) ?> <?= htmlspecialchars($attendee['lname']) ?></td>
                        <td style="padding: 10px;"><?= isset($attendee['roomNumber']) ? htmlspecialchars($attendee['roomNumber']) : '' ?></td>
                        <td style="padding: 10px;">$<?= number_format($attendee['attendanceFee'], 2) ?></td>
                        <td style="padding: 10px;">Student</td>
                        <td style="padding: 10px;">
                            <a href="attendees.php?view=<?= urlencode($viewType) ?>&edit=<?= $attendee['attendeeID'] ?>" class="edit-link <?= $isEditing ? 'edit-active' : '' ?>">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <?php if ($viewType === 'professional' || $viewType === ''): ?>
            <?php if($viewType === ''): ?>
                <p class="subheading" align="center">Professionals</p>
            <?php endif; ?>
            <table border="2" style="border-collapse: collapse; width: 90%; margin: 20px auto;">
                <tr>
                    <th style="padding: 10px;">Name</th>
                    <th style="padding: 10px;">Fee</th>
                    <th style="padding: 10px;">Type</th>
                    <th style="padding: 10px;">Actions</th>
                </tr>
                <?php foreach ($professionals as $attendee): ?>
                    <?php 
                        $isEditing = $editingAttendee && $editingAttendee['attendeeID'] == $attendee['attendeeID'];
                    ?>
                    <tr class="<?= $isEditing ? 'highlighted-row' : '' ?>">
                        <td style="padding: 10px;"><?= htmlspecialchars($attendee['fname']) ?> <?= htmlspecialchars($attendee['lname']) ?></td>
                        <td style="padding: 10px;">$<?= number_format($attendee['attendanceFee'], 2) ?></td>
                        <td style="padding: 10px;">Professional</td>
                        <td style="padding: 10px;">
                            <a href="attendees.php?view=<?= urlencode($viewType) ?>&edit=<?= $attendee['attendeeID'] ?>" class="edit-link <?= $isEditing ? 'edit-active' : '' ?>">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <?php if ($viewType === 'sponsor' || $viewType === ''): ?>
            <?php if($viewType === ''): ?>
                <p class="subheading" align="center">Sponsors</p>
            <?php endif; ?>
            <table border="2" style="border-collapse: collapse; width: 90%; margin: 20px auto;">
                <tr>
                    <th style="padding: 10px;">Name</th>
                    <th style="padding: 10px;">Sponsor Company</th>
                    <th style="padding: 10px;">Fee</th>
                    <th style="padding: 10px;">Type</th>
                    <th style="padding: 10px;">Actions</th>
                </tr>
                <?php foreach ($sponsors as $attendee): ?>
                    <?php 
                        $isEditing = $editingAttendee && $editingAttendee['attendeeID'] == $attendee['attendeeID'];
                    ?>
                    <tr class="<?= $isEditing ? 'highlighted-row' : '' ?>">
                        <td style="padding: 10px;"><?= htmlspecialchars($attendee['fname']) ?> <?= htmlspecialchars($attendee['lname']) ?></td>
                        <td style="padding: 10px;"><?= isset($attendee['companyName']) ? htmlspecialchars($attendee['companyName']) : '' ?></td>
                        <td style="padding: 10px;">$<?= number_format($attendee['attendanceFee'], 2) ?></td>
                        <td style="padding: 10px;">Sponsor</td>
                        <td style="padding: 10px;">
                            <a href="attendees.php?view=<?= urlencode($viewType) ?>&edit=<?= $attendee['attendeeID'] ?>" class="edit-link <?= $isEditing ? 'edit-active' : '' ?>">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
    
    
    <?php if ($editingAttendee): ?>
        <div class="edit-session-container">
            <div align="center">
                <p class="modal-title">Edit Attendee</p>
            </div>
            <form method="post" class="edit-session-form">
                <input type="hidden" name="updateAttendee" value="1">
                <input type="hidden" name="attendeeID" value="<?= htmlspecialchars($editingAttendee['attendeeID']) ?>">

                <div class="form-group">
                    <label for="fname">First Name:</label>
                    <input type="text" name="fname" value="<?= htmlspecialchars($editingAttendee['firstName']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="lname">Last Name:</label>
                    <input type="text" name="lname" value="<?= htmlspecialchars($editingAttendee['lastName']) ?>" required>
                </div>

                <div class="form-group" id="feeGroup" style="display: <?= $editingAttendee['type'] !== 'sponsor' ? 'block' : 'none' ?>;">
                    <label for="fee">Attendance Fee:</label>
                    <input type="number" name="fee" step="0.01" min="0" value="<?= htmlspecialchars($editingAttendee['attendanceFee']) ?>" <?= $editingAttendee['type'] !== 'sponsor' ? 'required' : '' ?>>
                </div>

                <div class="form-group">
                    <label for="type">Attendee Type:</label>
                    <select name="type" id="attendeeType" onchange="toggleHotelField(); toggleSponsorField();" class="gradient-select" required>
                        <option value="student" <?= $editingAttendee['type'] === 'student' ? 'selected' : '' ?>>Student</option>
                        <option value="professional" <?= $editingAttendee['type'] === 'professional' ? 'selected' : '' ?>>Professional</option>
                        <option value="sponsor" <?= $editingAttendee['type'] === 'sponsor' ? 'selected' : '' ?>>Sponsor</option>
                    </select>
                </div>

                <div class="form-group" id="hotelRoomGroup" style="display: <?= $editingAttendee['type'] === 'student' ? 'block' : 'none' ?>;">
                    <label for="hotelRoomID">Hotel Room (Students only):</label>
                    <select name="hotelRoomID" class="gradient-select">
                        <option value="">Select Hotel Room</option>
                        <?php foreach ($hotelRooms as $room): ?>
                            <option value="<?= htmlspecialchars($room['roomNumber']) ?>" <?= isset($editingAttendee['roomID']) && $editingAttendee['roomID'] == $room['roomNumber'] ? 'selected' : '' ?>>
                                Room <?= htmlspecialchars($room['roomNumber']) ?> (<?= htmlspecialchars($room['availableBeds']) ?> Available Beds)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" id="sponsorCompanyGroup" style="display: <?= $editingAttendee['type'] === 'sponsor' ? 'block' : 'none' ?>;">
                    <label for="sponsorCompany">Sponsor Company:</label>
                    <select name="sponsorCompany" class="gradient-select">
                        <option value="">Select Sponsor Company</option>
                        <?php foreach ($sponsorCompanies as $company): ?>
                            <option value="<?= htmlspecialchars($company['companyName']) ?>" <?= isset($editingAttendee['companyName']) && $editingAttendee['companyName'] === $company['companyName'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($company['companyName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit">Update Attendee</button>
                    <button type="button" onclick="window.location.href='attendees.php?view=<?= urlencode($viewType) ?>'">Cancel</button>
                    <button type="button" onclick="confirmDelete(<?= htmlspecialchars($editingAttendee['attendeeID']) ?>)">Delete Attendee</button>
                </div>
            </form>
        </div>
        
        <form id="deleteForm" method="post" style="display:none;">
            <input type="hidden" name="deleteAttendee" value="1">
            <input type="hidden" id="deleteAttendeeId" name="attendeeID">
        </form>

    <?php else: ?>
        <div class="edit-session-container">
            <div align="center">
                <p class="modal-title">Add Attendee</p>
            </div>
            <form method="post" class="edit-session-form">
                <input type="hidden" name="addAttendee" value="1">

                <div class="form-group">
                    <label for="fname">First Name:</label>
                    <input type="text" name="fname" required>
                </div>

                <div class="form-group">
                    <label for="lname">Last Name:</label>
                    <input type="text" name="lname" required>
                </div>

                <div class="form-group" id="feeGroup">
                    <label for="fee">Attendance Fee:</label>
                    <input type="number" name="fee" step="0.01" min="0" required>
                </div>

                <div class="form-group">
                    <label for="type">Attendee Type:</label>
                    <select name="type" id="attendeeType" onchange="toggleHotelField(); toggleSponsorField();" class="gradient-select" required>
                        <option value="">--Select Type--</option>
                        <option value="student">Student</option>
                        <option value="professional">Professional</option>
                        <option value="sponsor">Sponsor</option>
                    </select>
                </div>

                <div class="form-group" id="hotelRoomGroup" style="display:none;">
                    <label for="hotelRoomID">Hotel Room (Students only):</label>
                    <select name="hotelRoomID" class="gradient-select">
                        <option value="">Select Hotel Room</option>
                        <?php foreach ($hotelRooms as $room): ?>
                            <option value="<?= htmlspecialchars($room['roomNumber']) ?>">
                                Room <?= htmlspecialchars($room['roomNumber']) ?> (<?= htmlspecialchars($room['availableBeds']) ?> Available Beds)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" id="sponsorCompanyGroup" style="display:none;">
                    <label for="sponsorCompany">Sponsor Company:</label>
                    <select name="sponsorCompany" class="gradient-select">
                        <option value="">Select Sponsor Company</option>
                        <?php foreach ($sponsorCompanies as $company): ?>
                            <option value="<?= htmlspecialchars($company['companyName']) ?>">
                                <?= htmlspecialchars($company['companyName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit">Add Attendee</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <script>
        function toggleHotelField() {
            const type = document.getElementById('attendeeType').value;
            document.getElementById('hotelRoomGroup').style.display = (type === 'student') ? 'block' : 'none';
        }

        function toggleSponsorField() {
            const type = document.getElementById('attendeeType').value;
            document.getElementById('sponsorCompanyGroup').style.display = (type === 'sponsor') ? 'block' : 'none';
            const feeGroup = document.getElementById('feeGroup');
            if (feeGroup) {
                feeGroup.style.display = (type === 'sponsor') ? 'none' : 'block';
                const feeInput = feeGroup.querySelector('input');
                if (feeInput) {
                    feeInput.required = (type !== 'sponsor');
                    if (type === 'sponsor') {
                        feeInput.value = '0';
                    }
                }
            }
        }

        function confirmDelete(attendeeId) {
            if (confirm('Are you sure you want to delete this attendee? This action cannot be undone.')) {
                document.getElementById('deleteAttendeeId').value = attendeeId;
                document.getElementById('deleteForm').submit();
            }
        }

    </script>

</body>
</html>