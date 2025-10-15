<?php
// dashboard.php
session_start();
require_once "../includes/functions.php";
if (!($_SESSION['is_admin'] ?? false)) {
    header("Location: login.php");
    exit;
}
// You'll need a helper that returns each table plus its status counts.
// Here’s an example SQL embedded; you can move it into functions.php:
$sql = "
  SELECT t.TableId, t.TableName,
    SUM(CASE WHEN r.Status='Queued' THEN 1 ELSE 0 END)   AS Queued,
    SUM(CASE WHEN r.Status='Singing' THEN 1 ELSE 0 END)  AS Singing,
    SUM(CASE WHEN r.Status='Done' THEN 1 ELSE 0 END)     AS Done,
    SUM(CASE WHEN r.Status='Skipped' THEN 1 ELSE 0 END)  AS Skipped
  FROM tables t
  LEFT JOIN song_requests r
    ON r.TableId = t.TableId
  GROUP BY t.TableId, t.TableName
  ORDER BY t.TableName;
";
$tables = $conn->query($sql);
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard | Karaoke Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/style.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
</head>
<body>
  <?php include "navbar-admin.php"; ?>

  <main class="main-container px-3 py-4">
    <h1 class="section-title">
      <i class="fa-solid fa-table-columns"></i>
      Dashboard
    </h1>

    <section class="neu-card wide-card p-4">
      <h2 class="table-title mb-3">
        <i class="fa-solid fa-users-gear"></i>
        Tables Status
      </h2>

      <div class="table-responsive" dir="ltr">
        <table class="table neu-admin-table text-center w-100">
          <thead>
            <tr>
              <th>Table</th>
              <th>Queued</th>
              <th>Singing</th>
              <th>Done</th>
              <th>Skipped</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $tables->fetch_assoc()): ?>
              <tr>
                <td><b><?= htmlspecialchars($row['TableName']) ?></b></td>
                <td><?= $row['Queued']   ?></td>
                <td><?= $row['Singing']  ?></td>
                <td><?= $row['Done']     ?></td>
                <td><?= $row['Skipped']  ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <?php include 'footer.php'; ?>

</body>
</html>
