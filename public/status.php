<?php
date_default_timezone_set('Asia/Beirut');
require_once "../includes/functions.php";
$qr = $_GET['table'] ?? '';
$table = get_table_by_qr($qr);
if (!$table) die("Invalid or missing QR code.");
$requests = get_table_requests($table['TableId']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Your Karaoke Requests (<?= htmlspecialchars($table['TableName']) ?>)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include "navbar-user.php"; ?>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="karaoke-card col-12 col-md-10 col-lg-7">
        <div class="karaoke-icon mb-2">
            <i class="fa-solid fa-microphone-lines"></i>
        </div>
        <h2>Your Song Requests: <span style="color:#f67280"><?= htmlspecialchars($table['TableName']) ?></span></h2>
        <table class="table table-bordered table-striped align-middle text-center mt-4" style="background:rgba(255,255,255,0.12); border-radius:0.7rem;">
            <thead class="table-dark">
                <tr>
                    <th>Song</th>
                    <th>Artist</th>
                    <th>Status</th>
                    <th>Requested At</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($r = $requests->fetch_assoc()): ?>
                <?php
                $status = $r['Status'];
                $color = 'secondary';
                if ($status == 'Queued') $color = 'info';
                if ($status == 'Singing') $color = 'warning';
                if ($status == 'Done') $color = 'success';
                if ($status == 'Skipped') $color = 'danger';
                ?>
                <tr>
                    <td><?= htmlspecialchars($r['Title']) ?></td>
                    <td><?= htmlspecialchars($r['Artist']) ?></td>
                    <td>
                        <span class="badge bg-<?= $color ?>"><?= htmlspecialchars($r['Status']) ?></span>
                        <?php if ($status == 'Queued' || $status == 'Singing'): ?>
                            <div class="progress mt-2" style="height:10px;">
                                <div class="progress-bar bg-<?= $color ?>" style="width:70%"></div>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($r['RequestTime']) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <hr class="my-4">
        <a class="btn btn-outline-light btn-sm" href="index.php?table=<?= urlencode($qr) ?>">
            <i class="fa-solid fa-arrow-left"></i> Back to song request
        </a>
        <a class="btn btn-outline-light btn-sm ms-2" href="songs.php?table=<?= urlencode($qr) ?>">
            <i class="fa-solid fa-music"></i> All Songs
        </a>
    </div>
</div>
<!-- Confetti! -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
window.onload = function() {
    confetti({
        particleCount: 120,
        spread: 80,
        origin: { y: 0.7 }
    });
};
</script>
<!-- Bootstrap JS (navbar) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
