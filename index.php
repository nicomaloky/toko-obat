<!DOCTYPE html>
<html lang="en">
<head>
  <title>El's Pharmacy</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>
<body>
  <?php include 'navbar.php'; ?>

  <header class="bg-light py-5">
    <div class="container text-center">
      <h1>Selamat Datang di El's Pharmacy</h1>
      <p class="lead">Temukan berbagai obat terbaik untuk kebutuhan kesehatan Anda!</p>
      <a href="produk.php" class="btn btn-primary btn-lg mt-3">Lihat Produk</a>
    </div>
  </header>

  <section class="py-5">
    <div class="container">
      <h2 class="text-center mb-4">Layanan Kami</h2>
      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="card h-100 text-center">
            <div class="card-body">
              <i class="bi bi-capsule fs-1 text-primary mb-3"></i>
              <h5 class="card-title">Obat Resep</h5>
              <p class="card-text">Layanan obat resep dokter dengan konsultasi farmasi.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100 text-center">
            <div class="card-body">
              <i class="bi bi-heart-pulse fs-1 text-primary mb-3"></i>
              <h5 class="card-title">Kesehatan Umum</h5>
              <p class="card-text">Berbagai produk untuk menjaga kesehatan harian Anda.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100 text-center">
            <div class="card-body">
              <i class="bi bi-truck fs-1 text-primary mb-3"></i>
              <h5 class="card-title">Pengiriman Cepat</h5>
              <p class="card-text">Antar obat ke rumah Anda dengan cepat dan aman.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php include 'footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>