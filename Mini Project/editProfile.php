<?php
  session_start();
  require_once("db.php");
  $user_id = $_SESSION['user_id'];
  $user_role = $_SESSION['user_role'];
  $msg = "";

  if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
  }

  if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newName = trim($_POST['name']);
    $newEmail = trim($_POST['email']);

    if(!empty($newName) && !empty($newEmail)) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $newName, $newEmail, $user_id);

        if($stmt->execute()) {
            $msg = "✅ Profile updated successfully.";
        } else {
            $msg = "❌ Error updating profile: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $msg = "❗ Name and email cannot be empty.";
    }
  }

  $stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $userResult = $stmt->get_result();
  $user = $userResult->fetch_assoc();
  $stmt->close();

  if ($user_role === 'Admin') {
    $home = 'admin.php';
  } elseif ($user_role === 'Manager') {
      $home = 'manager.php';
  } elseif ($user_role === 'User') {
      $home = 'dashboard.php';
  } else {
      $home = 'login.php';
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Personal Profile</title>
  <link rel="stylesheet" href="styleLogin.css">
  <link rel="stylesheet" href="styleGeneral.css">
</head>
<body>
  <div class="profile-card">
    <h1>🪪 Personal Profile</h1>
    <h2>Editing [Name / Email]</h2>
    <?php
        if (!empty($msg)) {
            echo "<p>$msg</p>";
        }
    ?>
    <form action="" method="post">
        <table>
            <tr>
                <th>ID</th>
                <td><?php echo $user['id']; ?></td>
            </tr>
            <tr>
                <th>Name</th>
                <td><input type='text' name='name' value='<?php echo htmlspecialchars($user['name']); ?>' style='width:100%;padding:10px;border-radius:6px;border:1.5px solid #c9d6ff;'></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><input type='text' name='email' value='<?php echo htmlspecialchars($user['email']); ?>' style='width:100%;padding:10px;border-radius:6px;border:1.5px solid #c9d6ff;'></td>
            </tr>
            <tr>
                <th>Role</th>
                <td><?php echo $user['role']; ?></td>
            </tr>
        </table>
        <button type="submit">Submit</button><br>
    </form>
    <a href="<?php echo $home ?>" class="home-link">Home</a>
  </div>
</body>
</html>

