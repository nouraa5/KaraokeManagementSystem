<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$tableParam   = isset($_GET['table']) ? 'table=' . urlencode($_GET['table']) : '';
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm" style="font-size:1rem; min-height:56px;">
  <div class="container-fluid py-0">
    <a class="navbar-brand fw-semibold d-flex align-items-center gap-2" href="queue.php?<?= $tableParam ?>">
      <i class="fa-solid fa-microphone-lines"></i>
      Karaoke Admin
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="adminNav">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 gap-lg-2">
        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'queue' ? 'active fw-semibold' : '' ?>" href="queue.php?<?= $tableParam ?>">
            <i class="fa-solid fa-list-check me-1"></i>Queue
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'history' ? 'active fw-semibold' : '' ?>" href="history.php?<?= $tableParam ?>">
            <i class="fa-solid fa-clock-rotate-left me-1"></i>History
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php"><i class="fa-solid fa-right-from-bracket me-1"></i>Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
