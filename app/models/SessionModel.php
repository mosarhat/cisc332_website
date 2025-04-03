<!-- app/models/SessionModel.php -->

<!-- 
Just a little fun discovery, PDO::FETCH_ASSOC returns an array that is indexed by column name.
-->

<?php 

class SessionModel {
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    public function getAllSessions() {
        $query = "SELECT * FROM Session ORDER BY day, startTime";
        $stmt = $this->database->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSessionsByDay($day) {
        $query = "SELECT * FROM Session WHERE day = ? ORDER BY startTime";
        $stmt = $this->database->prepare($query);
        $stmt->execute([$day]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateSession($sessionId, $startTime, $endTime, $room) {
        $query = "UPDATE Session SET startTime = ?, endTime = ?, room = ? WHERE id = ?";
        $stmt = $this->database->prepare($query);
        return $stmt->execute([$startTime, $endTime, $room, $sessionId]);
    }

?>