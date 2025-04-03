<!-- app/views/schedule.php -->

<?php require_once '../app/views/includes/header.php'; ?>

<h2>Conference Schedule</h2>
<form method="get">
    <label for="day">Select Day:</label>
    <select name="day" id="day">
        <option value="">-- All Days --</option>
        <?php for ($i = 1; $i <= 2; $i++): ?>
            <option value="<?= $i ?>" <?= $selectedDay == $i ? 'selected' : '' ?>>
                Day <?= $i ?>
            </option>
        <?php endfor; ?>
    </select>
    <input type="submit" value="Go">
</form>

<table border="1">
    <tr>
        <th>Session</th>
        <th>Room</th>
        <th>Start Time</th>
        <th>End Time</th>
        <th>Day</th>
    </tr>
    <?php foreach ($sessions as $session): ?>
        <tr>
            <td><?= htmlspecialchars($session['name']) ?></td>
            <td><?= htmlspecialchars($session['room']) ?></td>
            <td><?= htmlspecialchars($session['startTime']) ?></td>
            <td><?= htmlspecialchars($session['endTime']) ?></td>
            <td><?= htmlspecialchars($session['day']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<h3>Edit Session</h3>
<?php require_once '../app/views/schedule/_form.php'; ?>

<?php require_once '../app/views/includes/footer.php'; ?>