<?php
    session_start();
    require_once("db.php");
    $user_id = $_SESSION['user_id'];
    $msg = "";

    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'User') {
        header("Location: login.php");
        exit;
    }

    $stmt = $conn->prepare("
        SELECT c.id, f.location, f.name AS location_name, f.level, c.issues, c.other, c.status, c.created_at, a.file_path
        FROM complaints c
        JOIN facilities f ON c.facility_id = f.id
        LEFT JOIN attachments a ON c.id = a.complaint_id
        WHERE c.user_id = ?
        ORDER BY c.id ASC
        ");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $complaints = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Complaint History</title>
  <link rel="stylesheet" href="styleRegLog.css">
</head>
<body>
<div class="history-card">
  <h1>📃 Complaint History</h1>
  <h2>Faculty of Computing Facilities Complaint</h2>

  <?php
    if (!empty($msg)) {
        echo "<p>$msg</p>";
    }

    if (count($complaints) > 0) {
        echo "<table>";
        echo "<tr>
                <th>ID</th>
                <th>Location</th>
                <th>Location Name</th>
                <th>Level</th>
                <th>Issues</th>
                <th>Other</th>
                <th>Status</th>
                <th>Date</th>
                <th>Image</th>
              </tr>";

        foreach ($complaints as $comp) {
            echo "<tr>
                    <td>{$comp['id']}</td>
                    <td>{$comp['location']}</td>
                    <td>{$comp['location_name']}</td>
                    <td>{$comp['level']}</td>
                    <td>{$comp['issues']}</td>
                    <td>{$comp['other']}</td>";
            $statusClass = 'status-' . strtolower(str_replace(' ', '', $comp['status']));
            echo "<td class='$statusClass'>{$comp['status']}</td>";
            echo "<td>{$comp['created_at']}</td>";

            if (!empty($comp['file_path']) && file_exists($comp['file_path'])) {
                echo "<td><img src='" . htmlspecialchars($comp['file_path']) . "' class='complaint-img-thumb' data-full='" . htmlspecialchars($comp['file_path']) . "' style='border-radius:8px;box-shadow:0 2px 8px #20408022;cursor:pointer;'></td>";
            } else {
                echo "<td>No image</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No complaints found.</p>";
    }
  ?>

  <br>
  <a href="dashboard.php" class="home-link">Home</a>
</div>
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
