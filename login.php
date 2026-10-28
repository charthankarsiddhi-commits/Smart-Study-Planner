<?php
require_once "config.php";
if (!empty($_SESSION["user_id"])) { header("Location: index.php"); exit; }
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";
    $stmt = $conn->prepare("SELECT id,name,password_hash FROM users WHERE email=?");
    $stmt->bind_param("s", $email); $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if ($user && password_verify($password, $user["password_hash"])) {
        session_regenerate_id(true);
        $_SESSION["user_id"] = (int)$user["id"];
        $_SESSION["user_name"] = $user["name"];
        header("Location: index.php"); exit;
    } else { $error = "Email or password is incorrect."; }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Log in | StudySpace</title><link rel="stylesheet" href="style.css"></head>
<body class="auth-page"><main class="auth-card"><a class="brand" href="login.php">✦ StudySpace</a><h1>Welcome back</h1><p class="muted">Your next small step starts here.</p>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="stack-form"><label>Email address<input type="email" name="email" required value="<?= e($_POST["email"] ?? "") ?>"></label><label>Password<input type="password" name="password" required></label><button class="btn primary full" type="submit">Log in</button></form>
<p class="auth-foot">New to StudySpace? <a href="register.php">Create an account</a></p></main></body></html>
