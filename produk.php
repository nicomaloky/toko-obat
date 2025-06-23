<?php 
include 'koneksi.php'; 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Produk - El's Pharmacy</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>
<body>
  <?php include 'navbar.php'; ?>

  <div class="container mt-5">
    <h2 class="mb-4 text-center">Daftar Produk Obat</h2>
    
    <!-- Filter dan Pencarian Section -->
    <div class="row mb-4 justify-content-center">
        <div class="col-md-8">
            <form method="GET" action="produk.php" class="d-flex flex-column flex-md-row gap-2">
                <select name="kategori" class="form-select">
                    <option value="">Semua Kategori</option>
                    <?php
                    $query_kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
                    while($kategori = mysqli_fetch_assoc($query_kategori)) {
                        $selected = (isset($_GET['kategori']) && $_GET['kategori'] == $kategori['id_kategori']) ? 'selected' : '';
                        echo "<option value='{$kategori['id_kategori']}' {$selected}>" . htmlspecialchars($kategori['nama_kategori']) . "</option>";
                    }
                    ?>
                </select>
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Cari produk..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="row" id="productContainer">
      <?php
      // Membangun query dengan filter kategori dan pencarian
      $sql = "SELECT p.*, k.nama_kategori 
              FROM produk p 
              LEFT JOIN kategori k ON p.id_kategori = k.id_kategori";
      
      $conditions = [];
      $params = [];
      $types = '';

      if (isset($_GET['kategori']) && !empty($_GET['kategori'])) {
          $conditions[] = "p.id_kategori = ?";
          $params[] = (int)$_GET['kategori'];
          $types .= 'i';
      }
      if (isset($_GET['search']) && !empty($_GET['search'])) {
          $conditions[] = "p.nama_produk LIKE ?";
          $params[] = "%" . $_GET['search'] . "%";
          $types .= 's';
      }

      if (!empty($conditions)) {
          $sql .= " WHERE " . implode(' AND ', $conditions);
      }
      
      $stmt = $conn->prepare($sql);
      if (!empty($params)) {
          $stmt->bind_param($types, ...$params);
      }
      $stmt->execute();
      $query = $stmt->get_result();

      if ($query->num_rows > 0):
          while ($row = $query->fetch_assoc()):
      ?>
      <div class="col-lg-4 col-md-6 mb-4 product-card">
        <div class="card h-100 shadow-sm">
          <img src="img/<?php echo htmlspecialchars($row['gambar']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['nama_produk']); ?>" 
               onerror="this.src='https://placehold.co/300x200/EFEFEF/AAAAAA?text=<?php echo urlencode($row['nama_produk']); ?>'">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title"><?php echo htmlspecialchars($row['nama_produk']); ?></h5>
            <p class="text-muted mb-2"><span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['nama_kategori'] ?? 'Tanpa Kategori'); ?></span></p>
            <p class="card-text text-muted flex-grow-1"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
            
            <!-- Tampilkan Status Stok -->
            <div class="mb-2">
                <?php 
                    $stok = $row['stok'];
                    if ($stok <= 0) {
                        echo 'Stok: <span class="badge bg-danger">Habis</span>';
                    } elseif ($stok <= 10) {
                        echo 'Stok: <span class="badge bg-warning text-dark">Terbatas (' . $stok . ')</span>';
                    } else {
                        echo 'Stok: <span class="badge bg-success">Tersedia</span>';
                    }
                ?>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-auto">
              <p class="card-text fw-bold mb-0 text-primary fs-5">Rp <?php echo number_format($row['harga']); ?></p>
              <?php if (isset($_SESSION['username'])): ?>
                <!-- Tombol Beli dengan Pengecekan Stok -->
                <form action="checkout.php" method="GET" class="d-inline">
                  <input type="hidden" name="produk_id" value="<?php echo $row['id']; ?>">
                  <button type="submit" class="btn btn-sm btn-primary <?php if ($row['stok'] <= 0) echo 'disabled'; ?>">
                    <i class="bi bi-cart-plus"></i> <?php if ($row['stok'] > 0) echo 'Beli'; else echo 'Habis'; ?>
                  </button>
                </form>
              <?php else: ?>
                <a href="login.php" class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-box-arrow-in-right"></i> Login untuk Beli
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php 
          endwhile;
      else:
          echo '<div class="col-12 text-center py-5"><p class="text-muted fs-5">Produk tidak ditemukan.</p></div>';
      endif; 
      ?>
    </div>
  </div>

  <?php include 'footer.php'; ?>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
