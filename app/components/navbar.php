<nav class="navbar">
    <div class="title" onclick="window.location.href='/cisc332_website/public/'">Conference Management System</div>
    <div class="button-container">
        <div class="button <?= strpos($_SERVER['REQUEST_URI'], '/committees') !== false ? 'toggled' : '' ?>" onclick="window.location.href='/cisc332_website/public/pages/committees'">Committees</div>
        <div class="button <?= strpos($_SERVER['REQUEST_URI'], '/schedule') !== false ? 'toggled' : '' ?>" onclick="window.location.href='/cisc332_website/app/pages/schedule.php'">Schedule</div>
        <div class="button <?= strpos($_SERVER['REQUEST_URI'], '/rooms') !== false ? 'toggled' : '' ?>" onclick="window.location.href='/cisc332_website/app/pages/rooms.php'">Rooms</div>
        <div class="button <?= strpos($_SERVER['REQUEST_URI'], '/jobs') !== false ? 'toggled' : '' ?>" onclick="window.location.href='/cisc332_website/app/pages/jobs.php'">Jobs</div>
        <div class="button <?= strpos($_SERVER['REQUEST_URI'], '/sponsors') !== false ? 'toggled' : '' ?>" onclick="window.location.href='/cisc332_website/public/pages/sponsors'">Sponsors</div>
        <div class="button <?= strpos($_SERVER['REQUEST_URI'], '/sponsors') !== false ? 'toggled' : '' ?>" onclick="window.location.href='/cisc332_website/app/pages/intake.php'">Intake</div>
    </div>
</nav>