<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="index.php">El's Pharmacy</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNavbar">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="produk.php">Produk</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
      </ul>
      
      <ul class="navbar-nav">
        <?php if (isset($_SESSION['username'])): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle"></i> <?php echo $_SESSION['username']; ?>
            </a>
            <ul class="dropdown-menu">
              <?php if ($_SESSION['role'] == 'admin'): ?>
                <li><a class="dropdown-item" href="admin_dashboard.php"><i class="bi bi-speedometer2"></i> Admin Dashboard</a></li>
              <?php else: ?>
                <li><a class="dropdown-item" href="user_dashboard.php"><i class="bi bi-person"></i> Dashboard</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
          <li class="nav-item"><a class="nav-link" href="register.php"><i class="bi bi-person-plus"></i> Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>