<?php
date_default_timezone_set('Asia/Beirut');
require_once "../includes/functions.php";
$qr = $_GET['table'] ?? '';
$table = get_table_by_qr($qr);
if (!$table) die("Invalid or missing QR code.");

$max = get_setting('MaxSongsPerTable');
$cooldown = get_setting('CooldownMinutes');
$request_count = get_window_request_count($table['TableId'], $cooldown);
$songs = get_active_songs();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Karaoke at <?= htmlspecialchars($table['TableName']) ?>!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include "navbar-user.php"; ?>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="karaoke-card col-12 col-md-8 col-lg-5 text-center">
        <div class="karaoke-icon mb-2">
            <i class="fa-solid fa-microphone-lines"></i>
        </div>
        <h1 class="mb-1">Karaoke Night</h1>
        <h3 class="mb-3">Table <span style="color:#f67280"><?= htmlspecialchars($table['TableName']) ?></span></h3>
        <p class="lead mb-2">You can request <b><?= $max ?></b> songs per round!</p>
        <p>You have requested <b><?= $request_count ?></b> of <b><?= $max ?></b> songs.</p>
        <?php
        if ($request_count >= $max) {
            if (!$canRequest) {
                $last = get_last_valid_request_time($table['TableId']);
                $now = time();
                $remaining = 0;
                if ($last && ($now - $last < ($cooldown * 60))) {
                    $remaining = ($cooldown * 60) - ($now - $last);
                    echo '<h4>You have reached your song limit.</h4>
                    <span id="cd" class="cooldown-badge">
                    <i class="fa-solid fa-hourglass-half"></i>
                    <span id="cooldown-txt">'. gmdate("i \m\i\n u \s\e\c", $remaining) .'</span> before you can request another song.
                    </span>';
                    echo "<script>
                        let remain = $remaining;
                        function updateCooldown() {
                            if(remain <= 0) { document.getElementById('cooldown-txt').innerText = 'now'; return; }
                            let m = Math.floor(remain / 60);
                            let s = remain % 60;
                            document.getElementById('cooldown-txt').innerText = m + ' min ' + (s<10?'0':'') + s + ' sec';
                            remain--;
                            setTimeout(updateCooldown, 1000);
                        }
                        updateCooldown();
                    </script>";
                }
            }
            } else {
            ?>
            <form class="mt-4" method="post" action="request.php?table=<?= urlencode($qr) ?>">
                <input type="hidden" name="table_id" value="<?= $table['TableId'] ?>">
                <div class="mb-3 text-start">
                    <label for="song_id" class="form-label">Select your song:</label>
                    <select name="song_id" id="song_id" class="form-select" required>
                        <option value="">-- Choose a song --</option>
                        <?php while ($song = $songs->fetch_assoc()): ?>
                            <option value="<?= $song['SongId'] ?>">
                                <?= htmlspecialchars($song['Title']) ?> — <?= htmlspecialchars($song['Artist']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-warning mb-2 ms-1" onclick="pickRandomSong()">
                        <i class="fa-solid fa-shuffle"></i> Surprise Me!
                    </button>
                </div>
                <button type="submit" class="btn btn-karaoke">
                    <i class="fa-solid fa-microphone"></i> Request Song
                </button>
            </form>
            <script>
            function pickRandomSong() {
                let select = document.getElementById('song_id');
                let options = select.querySelectorAll('option');
                let valid = [];
                options.forEach((opt,i) => { if(i>0) valid.push(opt); });
                let rand = Math.floor(Math.random() * valid.length);
                select.value = valid[rand].value;
                select.classList.add('glow');
                setTimeout(()=>select.classList.remove('glow'), 1200);
            }
            </script>
            <?php
        }
        ?>
        <hr class="my-4">
        <a class="btn btn-outline-light btn-sm" href="status.php?table=<?= urlencode($qr) ?>">
            <i class="fa-solid fa-list-music"></i> Check your song requests & status
        </a>
        <a class="btn btn-outline-light btn-sm ms-2" href="songs.php?table=<?= urlencode($qr) ?>">
            <i class="fa-solid fa-music"></i> All Songs
        </a>
    </div>
</div>
<!-- Bootstrap JS (navbar) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
