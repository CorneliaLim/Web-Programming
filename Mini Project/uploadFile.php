<?php
    session_start();
    require_once("db.php");
    $msg = '';

    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
        header("Location: login.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['user_file'])) {
        //Temporary path for file uploaded
        $file = $_FILES['user_file']['tmp_name'];
        $selectedRole = $_POST['role'];


        //Check file properly uploaded
        if (is_uploaded_file($file)) {
            // open file read line-by-line 'r' means read
            $handler = fopen($file, 'r');

            $insertedCount = 0;
            $failCount = 0;
            //read one line
            while (($line = fgets($handler)) !== false){
                $line = trim($line);
                //if line empty move to next line
                if (empty($line)) continue;

                //Split attribute using ',' and trim extra whitespace
                [$name, $email, $password] = array_map('trim', explode(',', $line));

                //Check for duplicate email
                $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $checkEmail->bind_param("s", $email);
                $checkEmail->execute();
                $checkEmail->store_result();

                if ($checkEmail->num_rows === 0){
                    $hashedPwd = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $name, $email, $hashedPwd, $selectedRole);
                    $stmt->execute();
                    $stmt->close();
                    $insertedCount++;
                } else {
                    $failCount++;
                }
                $checkEmail->close();
            }

            fclose($handler);
            if ($insertedCount > 0){
                $msg = "✅ $insertedCount user(s) imported successfully. ";
                if ($failCount > 0) {
                    $msg .= "$failCount duplicate(s) skipped.";
                }
            } else {
                $msg = "⚠️ No new users were imported. All records might be duplicates.";
            }
    }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload users file</title>
    <link rel="stylesheet" href="styleForm.css">
    <script>
        function Delete() {
            return confirm("Are you sure you want to remove this user?");
        }
    </script>
</head>
<body>
    <div class="container">
        <h1><b>📄 Upload File</b></h1>
        <h2>Faculty of Computing Facilities Complaint System Database</h2>

        <div class="reg">
        <?php
            if (!empty($msg)) {
                $color = str_starts_with($msg,"✅") ? 'green' : 'red';
                echo "<p style='color: $color;'>$msg</p>";
            }
        ?>
            <form action="" method="post" enctype="multipart/form-data">
                <label>Select file (file format: .txt only):</label>
                <input type="file" name="user_file" accept=".txt" required><br><br>

                <label>Select Role for Users:</label>
                <select name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                    <option value="user">User</option>
                </select><br><br>

                <button type="submit">Upload</button>

                <p><a href="admin.php">[ Home ]</a></p>
            </form>
        </div>
    </div>
</body>
</html>