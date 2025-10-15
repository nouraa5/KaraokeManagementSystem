<?php
// ── tables.php ───────────────────────────────────────────────────────────────
date_default_timezone_set('Asia/Beirut');
session_start();
require_once "../includes/functions.php";

// protect
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit;
}

$qr = $_GET['table'] ?? '';
$tableParam = $qr ? 'table=' . urlencode($qr) : '';

// fetch summary with separate counts
$sql = "
  SELECT
    t.TableId,
    t.TableName,
    SUM(CASE WHEN r.Status = 'Singing' THEN 1 ELSE 0 END)   AS Singing,
    SUM(CASE WHEN r.Status = 'Queued'  THEN 1 ELSE 0 END)   AS Queued,
    SUM(CASE WHEN r.Status = 'Done'    THEN 1 ELSE 0 END)   AS Completed,
    SUM(CASE WHEN r.Status = 'Skipped' THEN 1 ELSE 0 END)   AS Skipped
  FROM `tables` AS t
  LEFT JOIN `song_requests` AS r
    ON r.TableId = t.TableId
  GROUP BY t.TableId, t.TableName
  ORDER BY t.TableName ASC
";

$result = $conn->query($sql);
if (!$result) {
    die("DB error: " . htmlspecialchars($conn->error));
}

$rows = [];
while ($r = $result->fetch_assoc()) {
    $rows[] = $r;
}
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Table Summary | Karaoke Admin</title>

  <!-- CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
  <?php include "navbar-admin.php"; ?>

  <main class="main-container px-3">
    <h1 class="section-title">
      <i class="fa-solid fa-table"></i>
      Table Summary
    </h1>

    <section class="neu-card wide-card p-4">
      <div class="table-responsive" dir="ltr">
        <table id="summaryTable"
               class="table neu-admin-table w-100 text-center align-middle">
          <thead>
            <tr>
              <th>Table</th>
              <th>Singing</th>
              <th>Queued</th>
              <th>Completed</th>
              <th>Skipped</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
            <tr>
              <td><b><?= htmlspecialchars($r['TableName']) ?></b></td>
              <td><?= intval($r['Singing']) ?></td>
              <td><?= intval($r['Queued']) ?></td>
              <td><?= intval($r['Completed']) ?></td>
              <td><?= intval($r['Skipped']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <script>
    $(function(){
      $('#summaryTable').DataTable({
        responsive: true,
        order: [[0,'asc']],    // sort by Table name
        columnDefs: [
          { className: 'text-center', targets: [1,2,3,4] }
        ],
        dom:
          "<'dt-header'<'dataTables_length'l><'dataTables_filter'f>>" +
          "t" +
          "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
      });
    });
  </script>
    <!-- … at the end of your page, before </body> … -->
  <footer class="site-footer py-3">
    <div class="container text-center">
      <img
        src="../assets/footer.png"
        alt="Footer Logo"
        class="footer-logo"
      >
    </div>
  </footer>
    <?php include 'footer.php'; ?>

</body>
</html>
