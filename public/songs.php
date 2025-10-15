<?php
/*******************************
 *  songs.php  – Arabic RTL + red cool-down (UI tweaks)
 *******************************/
date_default_timezone_set('Asia/Beirut');
require_once "../includes/functions.php";
session_start();

/* ── 1. INPUTS & GUARDS ─────────────────────────────── */
$qr    = $_GET['table'] ?? '';
$table = get_table_by_qr($qr);
if (!$table) die("رمز QR غير صالح.");

/* ── 2. Cool-down helpers ───────────────────────────── */
$COOLDOWN_MIN = 30;
$canRequest   = can_table_request($table['TableId'], $COOLDOWN_MIN);
$lastReq      = get_last_valid_request_time($table['TableId']);
$remainSec    = ($canRequest || !$lastReq) ? 0 :
                max(0, $COOLDOWN_MIN*60 - (time() - $lastReq));
$etaSec       = estimate_wait_seconds($table['TableId']);

/* ── 3. Handle POST ─────────────────────────────────── */
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $artist = trim($_POST['artist'] ?? '');
    $title  = trim($_POST['title']  ?? '');
    $singer = trim($_POST['singer'] ?? '');

    if (!$canRequest)        $errors[] = "يرجى الانتظار حتى انتهاء فترة التجميد.";
    if (mb_strlen($artist) < 2) $errors[] = "اسم الفنان قصير جداً.";
    if (mb_strlen($title)  < 2) $errors[] = "عنوان الأغنية قصير جداً.";
    if (mb_strlen($singer) < 2) $errors[] = "اسمك قصير جداً.";

    if (!$errors) {
        $stmt = $conn->prepare(
          "INSERT INTO song_requests
                 (TableId, SongId, Artist, Title, SingerName, RequestTime, Status)
           VALUES (?, NULL, ?, ?, ?, NOW(), 'Queued')"
        );
        $stmt->bind_param("isss", $table['TableId'], $artist, $title, $singer);
        if ($stmt->execute()) {
            header("Location: songs.php?table=".urlencode($qr)."&success=1");
            exit;
        }
        $errors[] = "خطأ في قاعدة البيانات، حاول مرة أخرى.";
    }
}
?>
<!doctype html>
<html lang="ar" dir="rtl" data-bs-theme="dark">
<head>
<meta charset="utf-8">
<title>طلب أغنية | الطاولة <?= htmlspecialchars($table['TableName']) ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500&display=swap" rel="stylesheet">

<style>
 body{font-family:'Tajawal',sans-serif;font-size:0.95rem;}
 /* inputs */
 .karaoke-input{background:#1d2942;border:0;border-radius:.7rem;padding:.75rem 1.1rem;
                font-size:1.05rem;color:var(--c-text);}
 .karaoke-input:focus{outline:0;box-shadow:0 0 0 .25rem rgba(102,163,255,.35);}
 /* bigger labels */
 label.form-label{color:var(--c-text);font-weight:700;font-size:1.125rem;}
 /* big rounded button */
 .karaoke-btn{padding:1.05rem 2.6rem;font-size:1.1rem;font-weight:600;border-radius:2.8rem;}
 .karaoke-btn:disabled{opacity:.55;cursor:not-allowed;}
 /* cool-down highlight */
 .cooldown-active{border:2px solid #dc3545;box-shadow:0 0 15px rgba(220,53,69,.35);}
 .cooldown-active #cooldown-txt{color:#dc3545;font-weight:700;}
 #songsTable{display:none;} /* dummy table */
</style>
</head>

<body>
<?php $currentPage='songs'; include 'navbar-user.php'; ?>

<main class="main-container px-2">
  <h1 class="section-title text-center my-4 fw-bold" style="font-size:2.2rem">
      اختر&nbsp;أغنيتك
  </h1>

  <!--──────── حالة التجميد ─────────-->
  <section class="neu-card text-center mb-4 p-4 <?= $canRequest?'':'cooldown-active' ?>">
    <div class="icon mb-3" style="font-size:2.4rem;color:var(--c-accent);">
        <i class="fa-solid fa-music"></i>
    </div>
    <span class="badge bg-secondary mb-3" style="font-size:1rem;">
        <i class="fa-solid fa-user-group"></i> <?= htmlspecialchars($table['TableName']) ?>
    </span>

    <?php if($canRequest): ?>
      <p class="fs-5 fw-semibold text-success m-0">يمكنك الآن إرسال طلب أغنية!</p>
    <?php else: ?>
      <p class="fs-5 fw-semibold text-danger-emphasis m-0">يرجى الانتــــظار</p>
      <small><i class="fa-solid fa-hourglass-half"></i>
          <span id="cooldown-txt"><?= gmdate("i&nbsp;د&nbsp; s&nbsp;ث", $remainSec) ?></span>
      </small>
    <?php endif; ?>

  </section>

  <!--──────── نموذج الطلب ─────────-->
  <section class="neu-card p-4">
    <h2 class="h6 mb-3"><i class="fa-solid fa-pencil"></i> أدخل التفاصيل</h2>

    <?php if($errors): ?>
      <div class="alert alert-danger py-2"><?= implode('<br>', array_map('htmlspecialchars',$errors)) ?></div>
    <?php endif; ?>

    <form method="post" class="needs-validation" novalidate>
      <div class="mb-3">
        <label class="form-label">الفنان</label>
        <input type="text" name="artist" class="form-control karaoke-input" minlength="2"
               value="<?= htmlspecialchars($_POST['artist']??'') ?>" <?= $canRequest?'required':'disabled' ?>>
        <div class="invalid-feedback">أدخل اسم الفنان.</div>
      </div>

      <div class="mb-3">
        <label class="form-label">عنوان الأغنية</label>
        <input type="text" name="title"  class="form-control karaoke-input" minlength="2"
               value="<?= htmlspecialchars($_POST['title']??'') ?>" <?= $canRequest?'required':'disabled' ?>>
        <div class="invalid-feedback">أدخل عنوان الأغنية.</div>
      </div>

      <div class="mb-4">
        <label class="form-label">اسمك</label>
        <input type="text" name="singer" class="form-control karaoke-input" minlength="2"
               value="<?= htmlspecialchars($_POST['singer']??'') ?>" <?= $canRequest?'required':'disabled' ?>>
        <div class="invalid-feedback">أدخل اسمك.</div>
      </div>

      <div class="d-flex justify-content-center">
        <button class="btn btn-neu karaoke-btn <?= $canRequest?'':'disabled' ?>"
                <?= $canRequest?'':'disabled' ?>>
            <i class="fa-solid <?= $canRequest?'fa-paper-plane':'fa-ban' ?>"></i>
            <?= $canRequest?'أرسل الطلب':'انتظر' ?>
        </button>
      </div>
    </form>
  </section>

  <div class="d-flex justify-content-center gap-2 my-4">
      <a href="requests.php?table=<?=urlencode($qr)?>" class="btn btn-outline-light btn-sm">
          <i class="fa-solid fa-list-ul"></i> طلباتي
      </a>
      <a href="home.php?table=<?=urlencode($qr)?>" class="btn btn-outline-light btn-sm">
          <i class="fa-solid fa-house"></i> الرئيسية
      </a>
  </div>

  <!-- طاولة وهمية لـ DataTables -->
  <table id="songsTable"><thead><tr><th>#</th><th>Title</th><th>Artist</th><th>Act</th></tr></thead>
        <tbody><tr><td></td><td></td><td></td><td></td></tr></tbody></table>
</main>

<!--──────── JS ─────────-->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(function(){
  /* DataTables dummy: no pagination/info */
  $('#songsTable').DataTable({
      language:{url:"https://cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json",direction:"rtl"},
      paging:false, info:false, lengthChange:false, ordering:false, searching:false,
      dom:'t'
  });

  /* Toast */
  window.showToast=(msg,d=2500)=>{
      const t=$('#toast');t.text(msg).css({display:'block',opacity:1});
      setTimeout(()=>{t.css('opacity',0);setTimeout(()=>t.hide(),300)},d);
  };
  <?php if(isset($_GET['success'])):?> showToast("🎉 تم إضافة الطلب إلى القائمة!"); <?php endif;?>

  /* live cool-down */
  let remain=<?= $canRequest?0:$remainSec ?>;
  (function cd(){
      if(!remain) return;
      const el=document.getElementById('cooldown-txt');
      if(!el) return;
      if(remain<=0){el.textContent='0';return;}
      const m=Math.floor(remain/60),s=remain%60;
      el.innerHTML=`${m}&nbsp;دقيقة&nbsp; ${s.toString().padStart(2,'0')}&nbsp;ثانية`;
      remain--; setTimeout(cd,1000);
  })();

  /* Bootstrap live validation (adds green ✓ immediately) */
  $('input.karaoke-input').on('input', function(){
      if(this.value.length>=2){
          this.classList.remove('is-invalid');
          this.classList.add('is-valid');
      }else{
          this.classList.remove('is-valid');
      }
  });

  /* submit validation */
  (()=>{ 'use strict';
      const forms=document.querySelectorAll('.needs-validation');
      Array.from(forms).forEach(f=>{
          f.addEventListener('submit',e=>{
              if(!f.checkValidity()){e.preventDefault();e.stopPropagation();}
              f.classList.add('was-validated');
          },false);
      });
  })();
});
</script>
<!-- <script>
document.querySelector('form').addEventListener('submit', () => {
    document.querySelectorAll('.karaoke-input, .karaoke-btn')
            .forEach(el => el.setAttribute('disabled', 'disabled'));
});
</script> -->
<!-- Toast -->
<div id="toast" role="alert" style="display:none; position:fixed; bottom:2.5em; right:50%; transform:translateX(50%);
 z-index:9999; background:#222e4c; color:#ffd363; padding:1em 2em; border-radius:1.2em; font-size:1rem;
 box-shadow:0 4px 20px rgba(0,0,0,.35); transition:opacity .3s;"></div>

<?php include 'footer.php'; ?>
</body>
</html>
