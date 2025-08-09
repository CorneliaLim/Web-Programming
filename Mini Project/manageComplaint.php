<?php
  session_start();
  require_once("db.php");
  $user_id = $_SESSION['user_id'];
  $msg = '';

  if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Manager') {
    header("Location: login.php");
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complaint_id'], $_POST['status'])) {
    $complaint_id = intval($_POST['complaint_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE complaints SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $complaint_id);


    if ($stmt->execute()) {
      $msg = "✅ Complaint status updated!";
    } else {
      $msg = "❌ Failed to update: " . $stmt->error;
    }
      $stmt->close();
    }

    $stmt = $conn->prepare("
                            SELECT c.*, f.location, f.name AS location_name, a.file_path
                            FROM complaints c
                            JOIN facilities f ON c.facility_id = f.id
                            LEFT JOIN attachments a ON c.id = a.complaint_id
                            ORDER BY c.id ASC
                          ");

    $stmt->execute();

    $result = $stmt->get_result();
    $complaints = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Complaints</title>
  <link rel="stylesheet" href="styleRegLog.css">
</head>
<body>
  <div class="container">
    <h1>📋 Complaints</h1>
    <?php
      if (!empty($msg)) {
        echo "<p>$msg</p>";
      }

      if (count($complaints) > 0) {
          echo '<table class="tablestyle">';
          echo '<tr>
                <th>ID</th>
                <th>Location</th>
                <th>Location Name</th>
                <th>Issues</th>
                <th>Other</th>
                <th>Status</th>
                <th>Update</th>
                <th>Image</th>
                </tr>';

          foreach ($complaints as $comp) {
              echo '<tr>';
              echo '<td>' . $comp['id'] . '</td>';
              echo '<td>' . $comp['location'] . '</td>';
              echo '<td>' . $comp['location_name'] . '</td>';
              echo '<td>' . $comp['issues'] . '</td>';
              echo '<td>' . $comp['other'] . '</td>';
              echo '<td>' . $comp['status'] . '</td>';
              echo '<td>
                      <form method="post" style="display: inline;">
                          <input type="hidden" name="complaint_id" value="' . $comp['id'] . '">
                          <select name="status">
                              <option value="Pending"' . ($comp['status'] == 'Pending' ? ' selected' : '') . '>Pending</option>
                              <option value="In Progress"' . ($comp['status'] == 'In Progress' ? ' selected' : '') . '>In Progress</option>
                              <option value="Resolved"' . ($comp['status'] == 'Resolved' ? ' selected' : '') . '>Resolved</option>
                          </select>
                          <button type="submit">Update</button>
                      </form>
                    </td>';

          if (!empty($comp['file_path']) && file_exists($comp['file_path'])) {
                echo "<td><img src='" . htmlspecialchars($comp['file_path']) . "' class='complaint-img-thumb' data-full='" . htmlspecialchars($comp['file_path']) . "' style='border-radius:8px;box-shadow:0 2px 8px #20408022;cursor:pointer;'></td>";
            } else {
                echo "<td>No image</td>";
            }
              echo '</tr>';
          }
          echo '</table>';
      } else {
          echo '<p>No complaints found.</p>';
      }
    ?>
<button onclick="location.href='manager.php'"><b>Home</b></button>
<div id="imageModal" class="modal" style="display:none;">
  <span class="modal-close" id="modalClose">&times;</span>
  <img class="modal-content" id="modalImg">
</div>
<script>
document.querySelectorAll('.complaint-img-thumb').forEach(img => {
  img.onclick = function() {
    document.getElementById('imageModal').style.display = 'flex';
    document.getElementById('modalImg').src = this.getAttribute('data-full');
  }
});
document.getElementById('modalClose').onclick = function() {
  document.getElementById('imageModal').style.display = 'none';
}
document.getElementById('imageModal').onclick = function(e) {
  if (e.target === this) this.style.display = 'none';
}
</script>
</body>
</html>

