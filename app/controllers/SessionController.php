<!-- app/controllers/SessionController.php -->

<!-- app/controllers/SessionController.php -->

<?php
class SessionController {
    private $model;
    
    public function __construct($database) {
        $this->model = new SessionModel($database);
    }

    public function index() {
        $selectedDay = isset($_GET['day']) ? $_GET['day'] : null;
        $sessions = $selectedDay ? 
            $this->model->getSessionsByDay($selectedDay) : 
            $this->model->getAllSessions();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sessionId'])) {
            $this->updateSession();
        }

        require_once '../app/views/schedule/index.php';
    }

    private function updateSession() {
        $result = $this->model->updateSession(
            $_POST['sessionId'],
            $_POST['startTime'],
            $_POST['endTime'],
            $_POST['room']
        );

        if ($result) {
            $redirectDay = isset($_GET['day']) ? '?day=' . urlencode($_GET['day']) : '';
            header("Location: " . $_SERVER['PHP_SELF'] . $redirectDay);
            exit;
        }
    }
}