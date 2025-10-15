<?php
// ── queue.php ────────────────────────────────────────────────────────────────
// Karaoke Admin ‑ Control Queue
// -----------------------------------------------------------------------------
// • Shows the current "Now Singing" song and the waiting queue.
// • One song may be in status Singing at a time (enforced by can_start_new_singing()).
// • "Next" automatically starts the oldest queued song whose REGION differs from
//   the region that just sang.  If no such song exists a flash message is shown.
// • "Force Next" lets the host override this rule and keep the show moving.
// -----------------------------------------------------------------------------

// ── bootstrap ────────────────────────────────────────────────────────────────

date_default_timezone_set('Asia/Beirut');
session_start();
require_once "../includes/functions.php";   // $conn (mysqli) + helpers

// ── guard ────────────────────────────────────────────────────────────────────
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit;
}
if (isset($_GET['action'], $_GET['id'])) {
    $id      = (int) $_GET['id'];
    $action  = $_GET['action'];

    switch ($action) {
        case 'play':   update_request_status($id, 'Singing'); break;
        case 'skip':   update_request_status($id, 'Skipped'); break;
        case 'cancel': update_request_status($id, 'Cancelled'); break;
    }
    header('Location: queue.php');
    exit;
}

$nowSinging = get_now_singing();
$queue      = get_queue();

// ── helper: only one song can be Singing at a time ───────────────────────────
function can_start_new_singing(): bool
{
    global $conn;
    $row = $conn->query("SELECT COUNT(*) AS cnt FROM song_requests WHERE Status='Singing'")
                ->fetch_assoc();
    return $row && (int)$row['cnt'] === 0;
}

// Map RegionId → human label (adjust if you add more regions)
$REGION_LABEL = [
    1 => 'Left',
    2 => 'Right',
    3 => 'Center',
    4 => 'Back'
];

// ── flash helper ─────────────────────────────────────────────────────────────
function set_flash(string $msg): void  { $_SESSION['flash'] = $msg; }
function get_flash(): ?string          { $m = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $m; }

// ── handle POST actions ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action']      ?? '';
    $id  = intval($_POST['request_id'] ?? 0);

    /* 1 ▸ Auto‑next picks a song from a *different* region */
    if ($act === 'NextAuto') {
        if (!can_start_new_singing()) {
            set_flash("Finish or skip the current song first!");
        } else {
            $next = get_next_request($conn);          // helper in functions.php
            if ($next) {
                update_request_status($next['RequestId'], 'Singing');
            } else {
                set_flash("No song from a different region is ready yet.");
            }
        }
        header("Location: queue.php");
        exit;
    }

    /* 2 ▸ Force‑next: always take the oldest queued song (same region allowed) */
    if ($act === 'NextForce') {
        if (!can_start_new_singing()) {
            set_flash("Finish or skip the current song first!");
        } else {
            $row = $conn->query("SELECT * FROM song_requests WHERE Status='Queued' ORDER BY RequestTime ASC LIMIT 1")
                        ->fetch_assoc();
            if ($row) {
                update_request_status($row['RequestId'], 'Singing');
            } else {
                set_flash("Queue is empty – nothing to play.");
            }
        }
        header("Location: queue.php");
        exit;
    }

    /* 3 ▸ Per‑row actions ---------------------------------------------------- */
    if ($id && in_array($act, ['Singing','Done','Skipped','Undo'], true)) {
        if ($act === 'Singing' && !can_start_new_singing()) {
            set_flash("A song is already being sung. Mark it Done or Skipped first!");
        } elseif ($act === 'Undo') {
            update_request_status($id, 'Queued');
        } else {
            update_request_status($id, $act);
        }
        header("Location: queue.php");
        exit;
    }
}

// ── load current data ───────────────────────────────────────────────────────
$group = get_all_requests_grouped();  // returns ['Singing'=>[], 'Queued'=>[]]
$now   = $group['Singing'];
$queue = $group['Queued'];

// ── view ─────────────────────────────────────────────────────────────────────
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Control Queue | Karaoke Admin</title>

  <!-- CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link href="../assets/style.css" rel="stylesheet">

  <style>
    /* Admin table sizing tweaks */
    .main-container{
      max-width: 100%;       /* take the full viewport width */
    }
    .neu-admin-table{
      width: 100% !important; /* stretch */
      font-size: 1.15rem;     /* slightly larger text */
      background-color: #ffffff;   /* light bg */
      color: #212529;              /* bootstrap body color */
    }
    .neu-admin-table thead {
      background-color: #f8f9fa;   /* light grey header */
    }
    .neu-admin-table th,
    .neu-admin-table td{
      padding: 0.75rem 1rem;  /* a bit more breathing room */
    }
    /* striped light rows */
    .neu-admin-table tbody tr:nth-of-type(odd){
      background-color: #fcfcfc;
    }
    .badge-queued{
      background-color:#0d6efd;
    }
  </style>
</head>
<body>
<?php include "navbar-admin.php"; ?>

<main class="main-container px-3">
  <h1 class="section-title"><i class="fa-solid fa-list-music"></i> Control Queue</h1>

  <?php if ($msg = get_flash()): ?>
    <div class="alert alert-info mb-3"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <!-- Now Singing block -->
  <div class="admin-table-title info mb-3"><i class="fa-solid fa-play"></i> Now Singing</div>

  <?php if ($now): $s = $now[0]; ?>
    <div class="now-singing-block mb-4 text-center">
      <div class="icon"><i class="fa-solid fa-music"></i></div>
      <span class="badge mb-2"><i class="fa-solid fa-user-group"></i> Table <b><?= htmlspecialchars($s['TableName']) ?></b></span>
      <div class="song-title"><?= htmlspecialchars($s['Title']) ?></div>
      <div class="artist"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($s['Artist']) ?></div>
      <div class="meta-row">Requested At: <b><?= date('H:i:s', strtotime($s['RequestTime'])) ?></b></div>
      <div class="meta-row">Region: <b><?= $REGION_LABEL[$s['Region']] ?? $s['Region'] ?></b></div>
      <div class="admin-btns mt-2">
        <form method="post" class="me-2 d-inline">
          <input type="hidden" name="request_id" value="<?= $s['RequestId'] ?>">
          <button name="action" value="Done" class="btn btn-success btn-sm"><i class="fa-solid fa-circle-check"></i> Done</button>
        </form>
        <form method="post" class="me-2 d-inline">
          <input type="hidden" name="request_id" value="<?= $s['RequestId'] ?>">
          <button name="action" value="Undo" class="btn btn-secondary btn-sm"><i class="fa-solid fa-rotate-left"></i> Undo</button>
        </form>
        <form method="post" class="d-inline" onsubmit="return confirm('Skip this song?');">
          <input type="hidden" name="request_id" value="<?= $s['RequestId'] ?>">
          <button name="action" value="Skipped" class="btn btn-danger btn-sm"><i class="fa-solid fa-xmark"></i> Skip</button>
        </form>
      </div>
    </div>
  <?php else: ?>
    <div class="now-singing-block mb-4 text-center">
      <div class="icon"><i class="fa-solid fa-microphone-slash"></i></div>
      <div>No song is currently being sung.</div>
      <?php if (count($queue) > 0): ?>
        <form method="post" class="mt-3 d-inline-block me-2">
          <input type="hidden" name="action" value="NextAuto">
          <button class="btn btn-primary btn-sm"><i class="fa-solid fa-forward-step"></i> Next</button>
        </form>
        <form method="post" class="mt-3 d-inline-block">
          <input type="hidden" name="action" value="NextForce">
          <button class="btn btn-outline-warning btn-sm"><i class="fa-solid fa-forward"></i> Force Next</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Waiting Queue table -->
  <!-- Queue table -->
  <h2 class="admin-table-title waiting mb-2"><i class="fa-solid fa-hourglass-half"></i> Waiting Queue</h2>
  <div class="table-responsive">
    <table id="queueTable" class="table neu-admin-table table-hover w-100 text-center align-middle">
      <thead>
        <tr>
          <th>Table</th>
          <th>Region</th>
          <th>Song</th>
          <th>Artist</th>
          <th>Requested At</th>
          <th>Status</th>
          <th class="text-nowrap">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($queue): ?>
        <?php foreach ($queue as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['TableName']) ?></td>
            <td><span class="badge bg-secondary"><?= $REGION_LABEL[$r['Region']] ?? $r['Region'] ?></span></td>
            <td><?= htmlspecialchars($r['Title']) ?: '—' ?></td>
            <td><?= htmlspecialchars($r['Artist']) ?: '—' ?></td>
            <td><?= date('H:i:s', strtotime($r['RequestTime'])) ?></td>
            <td><span class="badge badge-queued">Queued</span></td>
            <td>
              <!-- PLAY -->
              <?php if (can_start_new_singing()): ?>
                <form method="post" class="d-inline me-1">
                  <input type="hidden" name="request_id" value="<?= $r['RequestId'] ?>">
                  <button name="action" value="Singing" class="btn btn-success btn-sm" title="Play (start singing)"><i class="fa-solid fa-play"></i> <span class="d-none d-md-inline"></span></button>
                </form>
              <?php endif; ?>
              <!-- SKIP -->
              <form method="post" class="d-inline me-1" onsubmit="return confirm('Skip this song?');">
                <input type="hidden" name="request_id" value="<?= $r['RequestId'] ?>">
                <button name="action" value="Skipped" class="btn btn-warning btn-sm" title="Skip"><i class="fa-solid fa-forward"></i> <span class="d-none d-md-inline"></span></button>
              </form>
              <!-- CANCEL -->
              <form method="post" class="d-inline" onsubmit="return confirm('Cancel / remove this request?');">
                <input type="hidden" name="request_id" value="<?= $r['RequestId'] ?>">
                <button name="action" value="Cancelled" class="btn btn-danger btn-sm" title="Cancel"><i class="fa-solid fa-xmark"></i> <span class="d-none d-md-inline"></span></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
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
$('#queueTable').DataTable({
  responsive: true,
  scrollX: true,           // allow horizontal scroll / full width
  pageLength: 25,          // show more rows per page
  autoWidth: false,
  order: [[4, 'asc']],     // Requested At column
  columnDefs: [ { orderable: false, targets: 6 } ], // Actions column
  language: { emptyTable: "No songs in queue." },
  dom: "<'dt-header'<'dataTables_length'l><'dataTables_filter'f>>" +
       "t" +
       "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
});
</script>
<?php include 'footer.php'; ?>
</body>
</html>
