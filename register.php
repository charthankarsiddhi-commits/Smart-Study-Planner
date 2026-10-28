<?php
require_once "config.php";
if (!empty($_SESSION["user_id"])) { header("Location: index.php"); exit; }
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";
    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = "Enter your name, a valid email, and a password with at least 8 characters.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name,email,password_hash) VALUES (?,?,?)");
        $stmt->bind_param("sss", $name, $email, $hash);
        if ($stmt->execute()) {
            session_regenerate_id(true);
            $_SESSION["user_id"] = $stmt->insert_id;
            $_SESSION["user_name"] = $name;
            header("Location: index.php"); exit;
        } else {
            $error = $stmt->errno === 1062 ? "That email is already registered. Please log in." : "Could not create account. Please try again.";
        }
        $stmt->close();
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Create account | StudySpace</title><link rel="stylesheet" href="style.css"></head>
<body class="auth-page"><main class="auth-card"><a class="brand" href="login.php">✦ StudySpace</a><h1>Create your account</h1><p class="muted">Make a little progress every day.</p>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="stack-form"><label>Full name<input name="name" required maxlength="100" value="<?= e($_POST["name"] ?? "") ?>"></label><label>Email address<input type="email" name="email" required value="<?= e($_POST["email"] ?? "") ?>"></label><label>Password <span class="hint">(at least 8 characters)</span><input type="password" name="password" minlength="8" required></label><button class="btn primary full" type="submit">Create account</button></form>
<p class="auth-foot">Already have an account? <a href="login.php">Log in</a></p></main></body></html>
