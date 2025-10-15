<?php
$tableParam  = isset($_GET['table']) ? 'table=' . urlencode($_GET['table']) : '';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<nav dir="rtl" class="navbar navbar-dark navbar-expand-lg" style="font-size:.95rem;min-height:50px;">
  <div class="container-fluid py-0 px-3">
    <a class="navbar-brand" href="home.php?<?= $tableParam ?>" style="font-size:1.1rem;font-weight:600;">
      <i class="fa-solid fa-microphone-lines"></i> طاولة كاريوكي
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#userNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="userNav">
      <ul class="navbar-nav me-auto gap-lg-2">
        <li class="nav-item">
          <a class="nav-link <?php if ($currentPage=='home') echo 'active'; ?>" href="home.php?<?= $tableParam ?>">
            <i class="fa-solid fa-house"></i> الرئيسية
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php if ($currentPage=='songs') echo 'active'; ?>" href="songs.php?<?= $tableParam ?>">
            <i class="fa-solid fa-music"></i> تصفح الأغاني
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php if ($currentPage=='requests') echo 'active'; ?>" href="requests.php?<?= $tableParam ?>">
            <i class="fa-solid fa-list-music"></i> طلباتي
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>