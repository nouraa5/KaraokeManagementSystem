<?php
// ── login.php ────────────────────────────────────────────────────────────────
session_start();
require_once "../includes/functions.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT PasswordHash FROM users WHERE Username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $stmt->bind_result($hash);

    if ($stmt->fetch() && password_verify($pass, $hash)) {
        $_SESSION['is_admin'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login | Karaoke</title>

  <!-- core CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">
  <link
    href="../assets/style.css"
    rel="stylesheet">
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    rel="stylesheet">
</head>
<body>
  <?php include "navbar-admin.php"; ?>

  <main class="main-container">
    <!-- page heading -->
    <h1 class="section-title">
      <i class="fa-solid fa-lock"></i>
      Admin Login
    </h1>

    <!-- login card -->
    <section class="neu-card mb-5 p-4"
             style="max-width:400px; width:90%; margin:auto;">
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="post">
        <div class="mb-3 text-start">
          <label class="form-label">Username</label>
          <input
            type="text"
            name="username"
            class="form-control"
            required
            autofocus>
        </div>
        <div class="mb-4 text-start">
          <label class="form-label">Password</label>
          <input
            type="password"
            name="password"
            class="form-control"
            required>
        </div>
        <button
          type="submit"
          class="btn-neu w-100">
          <i class="fa-solid fa-right-to-bracket"></i>
          Login
        </button>
      </form>
    </section>
  </main>

  <!-- core JS -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
  </script>
</body>
</html>
