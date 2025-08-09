<?php
    require_once("db.php");
    $msg = '';
    $email_value = isset($_COOKIE['email']) ? $_COOKIE['email'] : '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name  = trim($_POST['name']);
        $email = trim($_POST['email']);
        $pwd   = trim($_POST['password']);
        $repwd = trim($_POST['repassword']);

        setcookie("email", $email, time() + (86400 * 7), "/");

        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmail->store_result();

        if (empty($name) || empty($email) || empty($pwd) || empty($repwd)) {
            $msg = "❗All fields are required❗";
        } elseif ($pwd !== $repwd) {
            $msg = "❗Passwords do not match❗";
        } elseif ($checkEmail->num_rows >0){
            $msg = "❗Email already registered❗";
        } else {
            $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $role_id = 1; 
            $stmt->bind_param("sssi", $name, $email, $hashedPwd, $role_id);

            if ($stmt->execute()) {
                $msg = "✅ Registered successfully!";
            } else {
                $msg = "❌ Registration failed: " . $stmt->error;
            }
            $stmt->close();
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styleRegLog.css">
</head>
<body>
    <div class="register-card">
        <h1>🧾 Registration</h1>
        <h2>Faculty of Computing Facilities Complaint</h2>
        <form action="" method="post">
           <?php if (!empty($msg)): ?>
                <p class="message animate-pop-in"><?php echo htmlspecialchars($msg); ?></p>
            <?php endif; ?>

            <label for="name">Name</label>
            <input type="text" name="name" id="name">

            <label for="email">Email</label>
            <input type="text" name="email" id="email" value="<?php echo htmlspecialchars($email_value); ?>">

            <label for="password">Password</label>
            <input type="password" name="password" id="password">

            <label for="repassword">Retype Password</label>
            <input type="password" name="repassword" id="repassword">
            
            <button type="submit">Submit</button>

            <p>Already registered? <a href="login.php" class="login-link">Log In</a></p>
        </form>
    </div>
</body>
</html>