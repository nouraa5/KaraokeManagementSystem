<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// -----------------------------------------------------------------------------
// includes/functions.php   – shared helpers + DB connection
// -----------------------------------------------------------------------------

// ── DB connection ─────────────────────────────────────────────────────────────

// -----------------------------------------------------------------------------
// includes/functions.php   – shared helpers + DB connection
// -----------------------------------------------------------------------------

// ── DB connection ─────────────────────────────────────────────────────────────
$host = 'eliebouantoun.com';
$user = 'elieboua_karaoke_user';         // adjust if needed
$pass = 'K@ra@0ke#Us3r$';             // adjust if needed
$db   = 'elieboua_karaoke_event';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->query("SET time_zone = '+03:00'");

// -----------------------------------------------------------------------------
// ▼  Data‑access helpers                                                    ▼
// -----------------------------------------------------------------------------

/**
 * Resolve table meta‑data from a QR string.
 */
function get_table_by_qr(string $qr): ?array
{
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM tables WHERE QRCode = ?");
    $stmt->bind_param("s", $qr);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/** Fetch full song catalogue for auto‑complete / search screens. */
function get_all_songs()
{
    global $conn;
    return $conn->query("SELECT SongId, Title, Artist FROM songs ORDER BY Title");
}

/** Add a brand‑new song into the catalogue (admin only). */
function addSong(string $title, string $artist): bool
{
    global $conn;
    $stmt = $conn->prepare("INSERT INTO songs (Title, Artist) VALUES (?, ?)");
    if (!$stmt) {
        error_log("[SQL] addSong prepare failed: " . $conn->error);
        return false;
    }
    $stmt->bind_param("ss", $title, $artist);
    return $stmt->execute();
}

// -----------------------------------------------------------------------------
// ▼  Admin ‑ queue / now‑singing helpers                                     ▼
// -----------------------------------------------------------------------------

/**
 * Fetch every *Queued* request in the order it was received.
 * Free‑text requests (SongId = NULL) are included thanks to the LEFT JOIN.
 */
function get_queue()
{
    global $conn;
    $sql = "SELECT r.*, t.TableName, t.Region,
                   COALESCE(s.Title , r.Title )  AS Title,
                   COALESCE(s.Artist, r.Artist) AS Artist
              FROM song_requests r
              JOIN tables      t ON t.TableId = r.TableId
         LEFT JOIN songs       s ON s.SongId  = r.SongId
             WHERE r.Status = 'Queued'
          ORDER BY r.RequestTime ASC";
    return $conn->query($sql);
}

/**
 * Currently active song(s) (Status = 'Singing').
 */
function get_now_singing()
{
    global $conn;
    $sql = "SELECT r.*, t.TableName, t.Region,
                   COALESCE(s.Title , r.Title )  AS Title,
                   COALESCE(s.Artist, r.Artist) AS Artist
              FROM song_requests r
              JOIN tables      t ON t.TableId = r.TableId
         LEFT JOIN songs       s ON s.SongId  = r.SongId
             WHERE r.Status = 'Singing'
          ORDER BY r.RequestTime ASC";
    return $conn->query($sql);
}

/**
 * Convenience – return arrays bucketed by status for the queue screen.
 */
function get_all_requests_grouped(): array
{
    global $conn;
    $statuses = ['Queued', 'Singing', 'Done', 'Skipped'];
    $out      = [];

    $sql = "SELECT r.*, t.TableName, t.Region,
                   COALESCE(s.Title , r.Title )  AS Title,
                   COALESCE(s.Artist, r.Artist) AS Artist
              FROM song_requests r
              JOIN tables      t ON t.TableId = r.TableId
         LEFT JOIN songs       s ON s.SongId  = r.SongId
             WHERE r.Status = ?
          ORDER BY r.RequestTime ASC";

    $stmt = $conn->prepare($sql);
    foreach ($statuses as $st) {
        $stmt->bind_param("s", $st);
        $stmt->execute();
        $out[$st] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
    return $out;
}

// -----------------------------------------------------------------------------
// ▼  Per‑table views (guest side)                                           ▼
// -----------------------------------------------------------------------------

function get_table_requests(int $tableId)
{
    global $conn;
    $sql = "SELECT r.*, 
                   COALESCE(s.Title , r.Title )  AS Title,
                   COALESCE(s.Artist, r.Artist) AS Artist
              FROM song_requests r
         LEFT JOIN songs       s ON s.SongId = r.SongId
             WHERE r.TableId = ?
          ORDER BY r.RequestTime DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tableId);
    $stmt->execute();
    return $stmt->get_result();
}

// -----------------------------------------------------------------------------
// The remaining helpers (settings, ETA, auto‑next, CRUD, rate‑limit…) are
// unchanged below.  ▼
// -----------------------------------------------------------------------------

// ── AUTO-NEXT (region-aware) ─────────────────────────────────────────────────
function get_next_request(mysqli $conn): ?array
{
    // 1️⃣ which region sang last?
    $lastRegionRow = $conn->query(
        "SELECT t.Region
           FROM song_requests r
           JOIN `tables` t ON t.TableId = r.TableId
          WHERE r.Status IN ('Singing','Done')
       ORDER BY r.RequestId DESC
          LIMIT 1"
    )->fetch_assoc();
    $lastRegion = $lastRegionRow['Region'] ?? null;

    // 2️⃣ find oldest queued song from a DIFFERENT region
    if ($lastRegion !== null) {
        $stmt = $conn->prepare(
            "SELECT r.* , t.Region
               FROM song_requests r
               JOIN `tables` t ON t.TableId = r.TableId
              WHERE r.Status = 'Queued'
                AND t.Region <> ?
           ORDER BY r.RequestTime ASC
              LIMIT 1"
        );
        if ($stmt) {
            $stmt->bind_param('i', $lastRegion);
            $stmt->execute();
            $res  = $stmt->get_result();
            $next = $res ? $res->fetch_assoc() : null;
            $stmt->close();
            return $next ?: null;
        }
        error_log('SQL error (prepare get_next_request): '.$conn->error);
        return null;
    }

    // 3️⃣ first song of the session – just take the oldest queued one
    $res = $conn->query(
        "SELECT r.* , t.Region
           FROM song_requests r
           JOIN `tables` t ON t.TableId = r.TableId
          WHERE r.Status = 'Queued'
       ORDER BY r.RequestTime ASC
          LIMIT 1"
    );
    return $res ? $res->fetch_assoc() : null;
}

// ── CRUD HELPERS ─────────────────────────────────────────────────────────────
function add_song_request($tableId, $songId) {
    global $conn;
    $stmt = $conn->prepare(
        "INSERT INTO song_requests (TableId, SongId, RequestTime, Status)
         VALUES (?, ?, NOW(), 'Queued')"
    );
    $stmt->bind_param("ii", $tableId, $songId);
    return $stmt->execute();
}

function update_request_status($requestId, $status) {
    global $conn;
    if ($status === 'Undo') $status = 'Queued';
    $stmt = $conn->prepare(
        "UPDATE song_requests SET Status = ? WHERE RequestId = ?"
    );
    $stmt->bind_param("si", $status, $requestId);
    return $stmt->execute();
}

// ── COOLDOWN / RATE-LIMIT HELPERS ────────────────────────────────────────────
function get_last_valid_request_time($tableId) {
    global $conn;
    $stmt = $conn->prepare(
        "SELECT MAX(RequestTime) AS last_request
           FROM song_requests
          WHERE TableId = ?
            AND Status IN ('Queued','Singing','Done')"
    );
    $stmt->bind_param("i", $tableId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row && $row['last_request'] ? strtotime($row['last_request']) : 0;
}

function get_window_start_time($tableId, $cooldown_minutes) {
    global $conn;
    $stmt = $conn->prepare(
        "SELECT MAX(RequestTime) AS win_start
           FROM song_requests
          WHERE TableId = ?
            AND Status IN ('Queued','Singing','Done')
            AND RequestTime <= (NOW() - INTERVAL ? MINUTE)"
    );
    $stmt->bind_param("ii", $tableId, $cooldown_minutes);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row && $row['win_start'] ? $row['win_start'] : '1970-01-01 00:00:00';
}

function get_window_request_count($tableId, $cooldown_minutes) {
    global $conn;
    $win_start = get_window_start_time($tableId, $cooldown_minutes);
    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS cnt
           FROM song_requests
          WHERE TableId = ?
            AND Status IN ('Queued','Singing','Done')
            AND RequestTime >= ?"
    );
    $stmt->bind_param("is", $tableId, $win_start);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ? (int)$row['cnt'] : 0;
}


// ── NEW 30-MIN FIXED COOLDOWN + ETA ──────────────────────────────────────────
/**
 * Returns true if the table may place a new request now.
 *
 * @param int $tableId
 * @param int $minutes  Cool-down length (default 30 minutes)
 * @return bool
 */
function can_table_request(int $tableId, int $minutes = 30): bool
{
    $last = get_last_valid_request_time($tableId);   // helper already exists
    return $last === 0 || (time() - $last) >= ($minutes * 60);
}

/**
 * Very rough estimate of how long the table must wait until its next song plays.
 *
 * Logic:
 *   1  song currently Singing  -> counts as 1 position
 *   # queued songs that were requested **before** this table’s first queued
 *     song (if any)            -> positions ahead
 *   average length per song (4 min) × total positions  =  ETA seconds
 *
 * @param int  $tableId
 * @param int  $avgSongSec  Average song length (default 4 min)
 * @return int Seconds to wait
 */
function estimate_wait_seconds(int $tableId, int $avgSongSec = 240): int
{
    global $conn;

    // How many songs are currently Singing?
    $singing = $conn->query(
        "SELECT COUNT(*) AS c FROM song_requests WHERE Status = 'Singing'"
    )->fetch_assoc()['c'] ?? 0;

    // Earliest queued request that belongs to *this* table
    $stmt = $conn->prepare(
        "SELECT MIN(RequestTime) AS first_req
           FROM song_requests
          WHERE Status = 'Queued' AND TableId = ?"
    );
    $stmt->bind_param("i", $tableId);
    $stmt->execute();
    $first = $stmt->get_result()->fetch_assoc()['first_req'] ?? null;

    if ($first) {
        // Count queued songs that will be played before ours
        $stmt2 = $conn->prepare(
            "SELECT COUNT(*) AS ahead
               FROM song_requests
              WHERE Status = 'Queued' AND RequestTime < ?"
        );
        $stmt2->bind_param("s", $first);
        $stmt2->execute();
        $ahead = $stmt2->get_result()->fetch_assoc()['ahead'] ?? 0;
    } else {
        // If we have no song in queue yet, *all* queued songs are ahead
        $ahead = $conn->query(
            "SELECT COUNT(*) AS c FROM song_requests WHERE Status = 'Queued'"
        )->fetch_assoc()['c'] ?? 0;
    }

    return ($singing + $ahead) * $avgSongSec;
}

?>
