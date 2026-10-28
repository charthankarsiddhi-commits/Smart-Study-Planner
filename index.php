<?php
require_once "config.php";
require_login();
$user_id = (int)$_SESSION["user_id"];
$error = ""; $notice = "";

// Handle task and timetable forms using POST/redirect/get.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    if ($action === "add_task") {
        $title = trim($_POST["title"] ?? "");
        $subject = trim($_POST["subject"] ?? "");
        $due = $_POST["due_date"] ?? "";
        $priority = $_POST["priority"] ?? "Medium";
        $notes = trim($_POST["notes"] ?? "");
        $allowed = ["Low","Medium","High"];
        if ($title === "" || $subject === "" || $due === "" || !in_array($priority, $allowed, true)) {
            $error = "Please complete the task title, subject, due date, and priority.";
        } else {
            $stmt = $conn->prepare("INSERT INTO tasks (user_id,title,subject,due_date,priority,notes) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("isssss", $user_id, $title, $subject, $due, $priority, $notes);
            $stmt->execute(); $stmt->close();
            header("Location: index.php?msg=task_added#tasks"); exit;
        }
    } elseif ($action === "toggle_task") {
        $task_id = (int)($_POST["task_id"] ?? 0);
        $stmt = $conn->prepare("UPDATE tasks SET is_done=1-is_done WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $task_id, $user_id); $stmt->execute(); $stmt->close();
        header("Location: index.php#tasks"); exit;
    } elseif ($action === "delete_task") {
        $task_id = (int)($_POST["task_id"] ?? 0);
        $stmt = $conn->prepare("DELETE FROM tasks WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $task_id, $user_id); $stmt->execute(); $stmt->close();
        header("Location: index.php?msg=task_deleted#tasks"); exit;
    } elseif ($action === "add_slot") {
        $subject = trim($_POST["slot_subject"] ?? "");
        $day = $_POST["day_of_week"] ?? "";
        $start = $_POST["start_time"] ?? "";
        $end = $_POST["end_time"] ?? "";
        $room = trim($_POST["room"] ?? "");
        $days = ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"];
        if ($subject === "" || !in_array($day, $days, true) || $start === "" || $end === "" || $end <= $start) {
            $error = "Enter a subject, valid day, and an end time later than the start time.";
        } else {
            $stmt = $conn->prepare("INSERT INTO timetable (user_id,subject,day_of_week,start_time,end_time,room) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("isssss", $user_id, $subject, $day, $start, $end, $room);
            $stmt->execute(); $stmt->close();
            header("Location: index.php?msg=slot_added#timetable"); exit;
        }
    } elseif ($action === "delete_slot") {
        $slot_id = (int)($_POST["slot_id"] ?? 0);
        $stmt = $conn->prepare("DELETE FROM timetable WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $slot_id, $user_id); $stmt->execute(); $stmt->close();
        header("Location: index.php?msg=slot_deleted#timetable"); exit;
    }
}
$msgs = ["task_added"=>"Task added successfully.","task_deleted"=>"Task deleted.","slot_added"=>"Timetable slot added.","slot_deleted"=>"Timetable slot deleted."];
$msg_key = $_GET["msg"] ?? ""; if (isset($msgs[$msg_key])) $notice = $msgs[$msg_key];

$stmt = $conn->prepare("SELECT COUNT(*) total, SUM(is_done=1) completed, SUM(is_done=0 AND due_date<CURDATE()) overdue, SUM(is_done=0 AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)) upcoming FROM tasks WHERE user_id=?");
$stmt->bind_param("i", $user_id); $stmt->execute(); $stats = $stmt->get_result()->fetch_assoc(); $stmt->close();
$total = (int)($stats["total"] ?? 0); $completed = (int)($stats["completed"] ?? 0);
$percent = $total ? (int)round($completed * 100 / $total) : 0;
$stmt = $conn->prepare("SELECT id,title,subject,due_date,priority,notes,is_done FROM tasks WHERE user_id=? ORDER BY is_done ASC, due_date ASC, FIELD(priority,'High','Medium','Low')");
$stmt->bind_param("i", $user_id); $stmt->execute(); $tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
$stmt = $conn->prepare("SELECT id,subject,day_of_week,start_time,end_time,room FROM timetable WHERE user_id=? ORDER BY FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), start_time");
$stmt->bind_param("i", $user_id); $stmt->execute(); $slots = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
$today = date("l");
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>StudySpace Dashboard</title><link rel="stylesheet" href="style.css"><script defer src="script.js"></script></head>
<body class="app-page">
<header class="topbar"><a class="brand" href="index.php">✦ StudySpace</a><nav><a href="#tasks">My tasks</a><a href="#timetable">Timetable</a></nav><div class="user-menu"><span>Hi, <?= e($_SESSION["user_name"] ?? "Student") ?></span><a class="btn outline small" href="logout.php">Log out</a></div></header>
<main class="container">
<section class="welcome"><div><p class="eyebrow"><?= e(date("l, d F Y")) ?></p><h1>Make today count.</h1><p class="muted">A clear plan makes studying feel manageable. Focus on one task at a time.</p></div><div class="welcome-mark">✦</div></section>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><?php if ($notice): ?><div class="alert success"><?= e($notice) ?></div><?php endif; ?>
<section class="stats-grid">
<div class="stat-card"><span class="stat-icon lavender">▤</span><p>Total tasks</p><strong><?= $total ?></strong><small>All your study tasks</small></div>
<div class="stat-card"><span class="stat-icon mint">✓</span><p>Completed</p><strong><?= $completed ?></strong><small>One step at a time</small></div>
<div class="stat-card"><span class="stat-icon peach">◷</span><p>Due soon</p><strong><?= (int)($stats["upcoming"] ?? 0) ?></strong><small>Due within 3 days</small></div>
<div class="stat-card"><span class="stat-icon rose">!</span><p>Overdue</p><strong><?= (int)($stats["overdue"] ?? 0) ?></strong><small>Tasks needing attention</small></div>
</section>
<div class="main-grid">
<section class="panel" id="tasks"><div class="section-heading"><div><p class="eyebrow">STAY ON TRACK</p><h2>Study tasks</h2></div><button class="btn primary" type="button" data-scroll="#add-task">+ Add task</button></div>
<div class="progress-area"><div class="progress-label"><span>Overall progress</span><strong><?= $percent ?>%</strong></div><div class="progress-track"><div class="progress-fill" style="width:<?= $percent ?>%"></div></div></div>
<div class="filter-row"><input id="taskSearch" type="search" placeholder="Search tasks or subjects..." aria-label="Search tasks"><select id="statusFilter" aria-label="Filter tasks"><option value="all">All tasks</option><option value="pending">Pending</option><option value="done">Completed</option></select></div>
<?php if (!$tasks): ?><div class="empty-state"><div>✎</div><h3>Your plan starts here</h3><p>Add your first study task below.</p></div><?php else: ?>
<div class="task-list" id="taskList">
<?php foreach ($tasks as $task): $is_done = (int)$task["is_done"] === 1; $is_overdue = !$is_done && $task["due_date"] < date("Y-m-d"); ?>
<article class="task-item <?= $is_done ? "is-done" : "" ?>" data-status="<?= $is_done ? "done" : "pending" ?>" data-search="<?= e(strtolower($task["title"]." ".$task["subject"])) ?>">
<form method="post" class="task-check-form"><input type="hidden" name="action" value="toggle_task"><input type="hidden" name="task_id" value="<?= (int)$task["id"] ?>"><button class="check-button" type="submit" aria-label="<?= $is_done ? "Mark pending" : "Mark completed" ?>"><?= $is_done ? "✓" : "" ?></button></form>
<div class="task-content"><h3><?= e($task["title"]) ?></h3><p><?= e($task["subject"]) ?> · Due <?= e(date("d M Y", strtotime($task["due_date"]))) ?></p><?php if ($task["notes"]): ?><p class="task-notes"><?= e($task["notes"]) ?></p><?php endif; ?></div>
<div class="task-actions"><span class="priority <?= strtolower(e($task["priority"])) ?>"><?= e($task["priority"]) ?></span><?php if ($is_overdue): ?><span class="due-label overdue-label">Overdue</span><?php endif; ?><form method="post" onsubmit="return confirm('Delete this task?')"><input type="hidden" name="action" value="delete_task"><input type="hidden" name="task_id" value="<?= (int)$task["id"] ?>"><button class="icon-button" type="submit" aria-label="Delete task" title="Delete task">×</button></form></div>
</article>
<?php endforeach; ?></div><p id="noMatches" class="muted hidden">No tasks match your search.</p><?php endif; ?>
<div class="subpanel" id="add-task"><h3>Add a study task</h3><form method="post" class="form-grid"><input type="hidden" name="action" value="add_task"><label class="span-2">Task title<input name="title" maxlength="180" placeholder="e.g. Revise normalization" required></label><label>Subject<input name="subject" maxlength="100" placeholder="e.g. DBMS" required></label><label>Due date<input type="date" name="due_date" min="<?= e(date("Y-m-d")) ?>" required></label><label>Priority<select name="priority"><option>Low</option><option selected>Medium</option><option>High</option></select></label><label class="span-2">Notes (optional)<textarea name="notes" rows="2" placeholder="What should you cover?"></textarea></label><div class="span-2"><button class="btn primary" type="submit">Save task</button></div></form></div>
</section>
<aside class="side-column"><section class="panel" id="timetable"><div class="section-heading"><div><p class="eyebrow">YOUR WEEK</p><h2>Timetable</h2></div><span class="today-chip"><?= e($today) ?></span></div>
<?php if (!$slots): ?><div class="empty-small"><p>No classes scheduled yet.</p><span>Add your weekly study slots below.</span></div><?php else: ?><div class="schedule-list"><?php foreach ($slots as $slot): ?><div class="schedule-item"><div class="schedule-time"><?= e(date("g:i A",strtotime($slot["start_time"]))) ?><small><?= e(date("g:i A",strtotime($slot["end_time"]))) ?></small></div><div class="schedule-details"><strong><?= e($slot["subject"]) ?></strong><span><?= e($slot["day_of_week"]) ?><?= $slot["room"] ? " · ".e($slot["room"]) : "" ?></span></div><form method="post" onsubmit="return confirm('Delete this timetable slot?')"><input type="hidden" name="action" value="delete_slot"><input type="hidden" name="slot_id" value="<?= (int)$slot["id"] ?>"><button class="icon-button" type="submit" aria-label="Delete timetable slot">×</button></form></div><?php endforeach; ?></div><?php endif; ?>
<div class="subpanel"><h3>Add timetable slot</h3><form method="post" class="stack-form compact"><input type="hidden" name="action" value="add_slot"><label>Subject<input name="slot_subject" maxlength="100" placeholder="e.g. Data Structures" required></label><label>Day<select name="day_of_week" required><?php foreach (["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"] as $day): ?><option <?= $day === $today ? "selected" : "" ?>><?= e($day) ?></option><?php endforeach; ?></select></label><div class="two-fields"><label>Start<input type="time" name="start_time" required></label><label>End<input type="time" name="end_time" required></label></div><label>Room / location (optional)<input name="room" maxlength="100" placeholder="e.g. Lab 2"></label><button class="btn secondary full" type="submit">Add to timetable</button></form></div></section>
<section class="tip-card"><div class="tip-spark">✦</div><p class="eyebrow">A LITTLE REMINDER</p><h3>Progress over perfection.</h3><p>Try a 25-minute focus session, then take a short break. Small, consistent efforts add up.</p><button class="btn outline full" id="focusButton" type="button">Start 25-minute focus</button><p id="focusStatus" class="focus-status" aria-live="polite"></p></section></aside>
</div><footer class="footer">StudySpace · Your personal study companion</footer>
</main></body></html>
