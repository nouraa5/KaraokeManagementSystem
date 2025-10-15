<?php
/*******************************
 *  home.php – الصفحة الرئيسية للضيف
 *******************************/
date_default_timezone_set('Asia/Beirut');
require_once "../includes/functions.php";
session_start();

/* ── 1. Resolve table from QR ───────────────────────── */
$qr    = $_GET['table'] ?? '';
$table = get_table_by_qr($qr);
if (!$table) die("رمز QR غير صالح.");

/* ── 2. Cool-down helpers (reuse same logic) ─────────── */
$COOLDOWN_MIN = 30;
$canRequest   = can_table_request($table['TableId'], $COOLDOWN_MIN);
$lastReq      = get_last_valid_request_time($table['TableId']);
$remainSec    = ($canRequest || !$lastReq) ? 0 :
                max(0, $COOLDOWN_MIN*60 - (time() - $lastReq));
?>
<!doctype html>
<html lang="ar" dir="rtl" data-bs-theme="dark">
<head>
<meta charset="utf-8">
<title>قائمة الطاولة | الطاولة <?= htmlspecialchars($table['TableName']) ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500&display=swap" rel="stylesheet">

<style>
 body{font-family:'Tajawal',sans-serif;font-size:.95rem;}
 .neu-card{border-radius:1.4rem;background:#0d1628;padding:2rem 1.6rem;
           box-shadow:0 8px 28px rgba(0,0,0,.45);}
 .cooldown-active{border:2px solid #dc3545;box-shadow:0 0 15px rgba(220,53,69,.35);}
 .cooldown-active #cdTxt{color:#dc3545;font-weight:700;}
 .main-btn{padding:1.15rem 2.8rem;font-size:1.1rem;font-weight:600;
           border-radius:3rem;}
</style>
</head>

<body>
<?php $currentPage='home'; include 'navbar-user.php'; ?>

<main class="main-container px-2">
  <h1 class="section-title text-center my-4 fw-bold" style="font-size:2.2rem">
      أهلاً&nbsp;وسهلاً
  </h1>

  <section class="neu-card text-center mb-5 <?= $canRequest?'':'cooldown-active' ?>">
    <div class="icon mb-3" style="font-size:2.7rem;color:var(--c-accent);">
        <i class="fa-solid fa-microphone-lines"></i>
    </div>

    <span class="badge bg-secondary mb-4" style="font-size:1rem;">
        <i class="fa-solid fa-user-group"></i>
        <?= htmlspecialchars($table['TableName']) ?>
    </span>

    <?php if($canRequest): ?>
      <p class="fs-5 fw-semibold text-success m-0">
         يمكنك طلب أغنية الآن <i class="fa-solid fa-circle-check"></i>
      </p>
    <?php else: ?>
      <p class="fs-5 fw-semibold text-danger-emphasis m-0">يرجى الانتــــظار</p>
      <small><i class="fa-solid fa-hourglass-half"></i>
        <span id="cdTxt"><?= gmdate("i&nbsp;د&nbsp; s&nbsp;ث", $remainSec) ?></span>
      </small>
    <?php endif; ?>
  </section>

  <!--──────── أزرار التنقل ─────────-->
  <div class="d-flex flex-column flex-md-row justify-content-center gap-3">
      <a href="songs.php?table=<?=urlencode($qr)?>" class="btn btn-neu main-btn">
          <i class="fa-solid fa-microphone"></i> طلب أغنية
      </a>
      <a href="requests.php?table=<?=urlencode($qr)?>" class="btn btn-neu main-btn">
          <i class="fa-solid fa-list-ul"></i> طلبات الطاولة
      </a>
  </div>
</main>

<!--──────── JS (count-down only) ─────────-->
<script>
let remain = <?= $canRequest?0:$remainSec ?>;
(function cd(){
  if(!remain) return;
  const el=document.getElementById('cdTxt');
  if(!el) return;
  if(remain<=0){el.textContent='0';return;}
  const m=Math.floor(remain/60),s=remain%60;
  el.innerHTML=`${m}&nbsp;د&nbsp; ${s.toString().padStart(2,'0')}&nbsp;ث`;
  remain--; setTimeout(cd,1000);
})();
</script>

<?php include 'footer.php'; ?>
</body>
</html>
