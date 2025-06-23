<?php
session_start();
include 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data pesanan user dengan JOIN ke tabel produk
$query_pesanan = mysqli_query($conn, "
    SELECT pesanan.*, produk.nama_produk, produk.harga 
    FROM pesanan 
    JOIN produk ON pesanan.produk_id = produk.id 
    WHERE pesanan.user_id = $user_id 
    ORDER BY pesanan.tanggal_pesan DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Dashboard - El's Pharmacy</title>
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
        <div class="row">
            <div class="col-12">
                <h2><i class="bi bi-person-circle"></i> Dashboard User</h2>
                <p class="text-muted">Selamat datang, <strong><?php echo $_SESSION['username']; ?></strong>!</p>
            </div>
        </div>

        <!-- Statistik -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5>Total Pesanan</h5>
                                <h3><?php echo mysqli_num_rows($query_pesanan); ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-cart3 fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5>Pesanan Selesai</h5>
                                <h3>
                                    <?php 
                                    $completed = mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE user_id = $user_id AND status = 'completed'");
                                    echo mysqli_fetch_assoc($completed)['total'];
                                    ?>
                                </h3>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-check-circle fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5>Pesanan Pending</h5>
                                <h3>
                                    <?php 
                                    $pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE user_id = $user_id AND status = 'pending'");
                                    echo mysqli_fetch_assoc($pending)['total'];
                                    ?>
                                </h3>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-clock fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Pesanan -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-list-ul"></i> Riwayat Pesanan</h5>
                        <a href="produk.php" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle"></i> Belanja Lagi
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($query_pesanan) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Produk</th>
                                            <th>Harga</th>
                                            <th>Jumlah</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = 1;
                                        mysqli_data_seek($query_pesanan, 0);
                                        while ($pesanan = mysqli_fetch_assoc($query_pesanan)): 
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $pesanan['nama_produk']; ?></td>
                                            <td>Rp <?php echo number_format($pesanan['harga']); ?></td>
                                            <td><?php echo $pesanan['jumlah']; ?></td>
                                            <td><strong>Rp <?php echo number_format($pesanan['total_harga']); ?></strong></td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                $status_text = '';
                                                switch($pesanan['status']) {
                                                    case 'pending':
                                                        $status_class = 'badge bg-warning';
                                                        $status_text = 'Pending';
                                                        break;
                                                    case 'completed':
                                                        $status_class = 'badge bg-success';
                                                        $status_text = 'Selesai';
                                                        break;
                                                    case 'cancelled':
                                                        $status_class = 'badge bg-danger';
                                                        $status_text = 'Dibatalkan';
                                                        break;
                                                }
                                                ?>
                                                <span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($pesanan['tanggal_pesan'])); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-cart-x fs-1 text-muted"></i>
                                <h5 class="mt-3 text-muted">Belum ada pesanan</h5>
                                <p class="text-muted">Anda belum melakukan pembelian apapun</p>
                                <a href="produk.php" class="btn btn-primary">
                                    <i class="bi bi-shop"></i> Mulai Belanja
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
