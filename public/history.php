<?php
// ── history.php ──────────────────────────────────────────────────────────────
// Admin – Full log of all song requests (light‑mode table)
// -----------------------------------------------------------------------------
// Shows every request ever made, newest first.  Rows that were typed manually
// by the guest (SongId IS NULL) now appear because we switched to a LEFT JOIN
// and COALESCE() the free‑text Artist/Title.
// -----------------------------------------------------------------------------

date_default_timezone_set('Asia/Beirut');

session_start();
require_once "../includes/functions.php";

// ── protect admin ──────────────────────────────────────────────────────────
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit;
}

// ── build & run the query ─────────────────────────────────────────────────
$sql = "
  SELECT
    t.TableName,
    COALESCE(s.Title , r.Title ) AS Title,
    COALESCE(s.Artist, r.Artist) AS Artist,
    r.RequestTime,
    r.Status
  FROM   song_requests AS r
  JOIN   tables        AS t ON t.TableId = r.TableId
  LEFT JOIN songs       AS s ON s.SongId  = r.SongId   -- allow NULL SongId
  ORDER BY r.RequestTime DESC
";
$res = $conn->query($sql);

if ($res === false) {
    die("Database error in history.php:<br>" . htmlspecialchars($conn->error));
}

$rows = [];
while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
}
?>
<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Song History | Karaoke Admin</title>

  <!-- CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link href="../assets/style.css" rel="stylesheet">
  <style>
    /* widen & brighten table rows like queue.php */
    .history-table thead th {
      background:#003366; color:#fff;
    }
    .history-table tbody tr:nth-child(odd) { background:#f8f9fa; }
    .history-table tbody tr:nth-child(even){ background:#e9ecef; }
  </style>
</head>
<body>
  <?php include "navbar-admin.php"; ?>

  <main class="main-container px-3 pb-5">
    <h3 class="mb-4"><i class="fa fa-clock-rotate-left me-2"></i>Full Song History</h3>

    <div class="table-responsive">
      <table id="historyTable" class="table table-bordered table-hover history-table">
        <thead>
          <tr>
            <th>Table</th>
            <th>Song</th>
            <th>Artist</th>
            <th>Requested At</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
          <tr>
            <td><strong><?= htmlspecialchars($r['TableName']) ?></strong></td>
            <td><?= htmlspecialchars($r['Title']) ?: '—' ?></td>
            <td><?= htmlspecialchars($r['Artist']) ?: '—' ?></td>
            <td><?= date('H:i:s', strtotime($r['RequestTime'])) ?></td>
            <td>
              <?php switch($r['Status']):
                case 'Queued': ?>
                  <span class="badge bg-secondary">Queued</span>
                <?php break; case 'Singing': ?>
                  <span class="badge bg-warning text-dark">Now Singing</span>
                <?php break; case 'Done': ?>
                  <span class="badge bg-primary">Done</span>
                <?php break; case 'Skipped': ?>
                  <span class="badge bg-danger">Skipped</span>
              <?php endswitch; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>

  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <script>
    $(function(){
      $('#historyTable').DataTable({
        responsive: true,
        order: [[3,'desc']],
        columnDefs: [ { orderable:false, targets:4 } ],
        dom:
          "<'dt-header'<'dataTables_length'l><'dataTables_filter'f>>" +
          't' +
          "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
      });
    });
  </script>
  <?php include 'footer.php'; ?>
</body>
</html>
