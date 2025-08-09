<?php
    session_start();
    require_once("db.php");
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'];

    if (!isset($_SESSION['user_id'])) {
      header("Location: login.php");
      exit;
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
  <title>Personal Profile</title>
  <link rel="stylesheet" href="styleLogin.css">
  <link rel="stylesheet" href="styleGeneral.css">
</head>
<body>
  <div class="profile-card">
    <h1>🪪 Personal Profile</h1>
    <table>
      <tr>
        <th>ID</th>
        <td><?php echo $user['id']; ?></td>
      </tr>
      <tr>
        <th>Name</th>
        <td><?php echo $user['name']; ?></td>
      </tr>
      <tr>
        <th>Email</th>
        <td><?php echo $user['email']; ?></td>
      </tr>
      <tr>
        <th>Role</th>
        <td><?php echo $user['role']; ?></td>
      </tr>
    </table>
    <button onclick="location.href='editProfile.php'">Update</button><br>
    <a href="<?php echo $home ?>" class="home-link">Home</a>
  </div>
</body>
</html>

