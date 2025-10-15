<?php
/*******************************
 *  requests.php – قائمة طلباتي (RTL Arabic)
 *******************************/
date_default_timezone_set('Asia/Beirut');
require_once "../includes/functions.php";
session_start();

/* ── 1. Resolve table from QR ───────────────────────── */
$qr    = $_GET['table'] ?? '';
$table = get_table_by_qr($qr);
if (!$table) die("رمز QR غير صالح.");

/* ── 2. Fetch this table’s requests ─────────────────── */
$stmt = $conn->prepare(
    "SELECT RequestId, Artist, Title, SingerName, Status, RequestTime
       FROM song_requests
      WHERE TableId = ?
      ORDER BY RequestTime DESC"
);
$stmt->bind_param("i", $table['TableId']);
$stmt->execute();
$requests = $stmt->get_result();
?>
<!doctype html>
<html lang="ar" dir="rtl" data-bs-theme="dark">
<head>
<meta charset="utf-8">
<title>طلباتي | الطاولة <?= htmlspecialchars($table['TableName']) ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500&display=swap" rel="stylesheet">

<style>
 body{font-family:'Tajawal',sans-serif;font-size:.95rem;}
 .neu-card{border-radius:1.4rem;background:#0d1628;padding:1.8rem 1.4rem;
           box-shadow:0 8px 28px rgba(0,0,0,.45);}
 .status-badge{font-size:.8rem;font-weight:600;padding:.35em .8em;border-radius:1.2rem;}
 .queued {background:#ffc107;color:#212529;}
 .singing{background:#198754;}
 .done   {background:#adb5bd;}
 .skipped{background:#dc3545;}
 /* DataTables */
 #reqTable thead th{border-bottom:1px solid #263247;}
</style>
</head>

<body>
<?php $currentPage='requests'; include 'navbar-user.php'; ?>

<main class="main-container px-2">
  <h1 class="section-title text-center my-4 fw-bold" style="font-size:2.1rem">
      طلبات&nbsp;الطاولة
  </h1>

  <section class="neu-card">
    <div class="table-responsive">
      <table id="reqTable" class="table table-dark table-striped align-middle mb-0 w-100">
        <thead>
          <tr>
            <th style="width:55px">#</th>
            <th>الفنان</th>
            <th>عنوان الأغنية</th>
            <th>اسم المغني</th>
            <th style="width:120px">الحالة</th>
          </tr>
        </thead>
        <tbody>
        <?php $i=1; while($row=$requests->fetch_assoc()): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= $row['Artist']      ? htmlspecialchars($row['Artist'])      : '—' ?></td>
            <td><?= $row['Title']       ? htmlspecialchars($row['Title'])       : '—' ?></td>
            <td><?= $row['SingerName']  ? htmlspecialchars($row['SingerName'])  : '—' ?></td>
            <td>
              <?php
                switch($row['Status']){
                  case 'Singing':  $cls='singing'; $txt='يُغنى الآن';     break;
                  case 'Done':     $cls='done';    $txt='تم';            break;
                  case 'Skipped':  $cls='skipped'; $txt='تخطّى';         break;
                  default:         $cls='queued';  $txt='في الانتظار';   break;
                }
              ?>
              <span class="status-badge <?= $cls ?>"><?= $txt ?></span>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </section>

  <div class="d-flex justify-content-center gap-2 my-4">
      <a href="songs.php?table=<?=urlencode($qr)?>" class="btn btn-outline-light btn-sm">
          <i class="fa-solid fa-microphone"></i> طلب أغنية
      </a>
      <a href="home.php?table=<?=urlencode($qr)?>" class="btn btn-outline-light btn-sm">
          <i class="fa-solid fa-house"></i> الرئيسية
      </a>
  </div>
</main>

<!--──────── JS ─────────-->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(function(){
  $('#reqTable').DataTable({
     language:{url:"https://cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json",direction:"rtl"},
     paging:false, info:false, lengthChange:false, ordering:false
  });

  /* toast helper (if you add delete later) */
  window.showToast=(msg,d=2500)=>{
     const t=$('#toast');t.text(msg).css({display:'block',opacity:1});
     setTimeout(()=>{t.css('opacity',0);setTimeout(()=>t.hide(),300)},d);
  };
});
</script>

<!-- Toast -->
<div id="toast" role="alert" style="display:none; position:fixed; bottom:2.5em; right:50%; transform:translateX(50%);
 z-index:9999; background:#222e4c; color:#ffd363; padding:1em 2em; border-radius:1.2em; font-size:1rem;
 box-shadow:0 4px 20px rgba(0,0,0,.35); transition:opacity .3s;"></div>

<?php include 'footer.php'; ?>
</body>
</html>
