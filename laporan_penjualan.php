<?php
session_start();
include 'koneksi.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Tentukan periode (bulan dan tahun)
$current_year = date('Y');
$current_month = date('m');

// Ambil bulan dari filter, jika tidak ada, gunakan bulan saat ini
$selected_month = isset($_GET['bulan']) ? $_GET['bulan'] : $current_month;
$selected_year = isset($_GET['tahun']) ? $_GET['tahun'] : $current_year;

// Persiapkan array untuk menampung data
$product_labels = [];
$product_quantities = [];
$limit = 10; // Batasi untuk 10 produk terlaris

// Query untuk mengambil produk terlaris berdasarkan jumlah
$query = "SELECT pr.nama_produk, SUM(p.jumlah) as total_terjual
          FROM pesanan p
          JOIN produk pr ON p.produk_id = pr.id
          WHERE p.status = 'completed' AND YEAR(p.tanggal_pesan) = ? AND MONTH(p.tanggal_pesan) = ?
          GROUP BY p.produk_id, pr.nama_produk
          ORDER BY total_terjual DESC
          LIMIT ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $selected_year, $selected_month, $limit);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $product_labels[] = $row['nama_produk'];
    $product_quantities[] = (int)$row['total_terjual'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Laporan Produk Terlaris - Admin Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .card { border-radius: 0.75rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5 mb-5">
        <div class="d-flex align-items-center mb-4">
            <a href="admin_dashboard.php" class="btn btn-outline-primary me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h2 class="mb-0"><i class="bi bi-trophy-fill"></i> Laporan Produk Terlaris</h2>
        </div>

        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Grafik 10 Produk Terlaris</h5>
                <form method="GET" class="d-flex gap-2">
                    <select name="bulan" class="form-select form-select-sm" style="width: 150px;">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php if ($m == $selected_month) echo 'selected'; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $m, 10)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <select name="tahun" class="form-select form-select-sm" style="width: 100px;">
                        <?php for ($y = $current_year; $y >= $current_year - 5; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php if ($y == $selected_year) echo 'selected'; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                </form>
            </div>
            <div class="card-body">
                <?php if (!empty($product_labels)): ?>
                    <canvas id="bestSellingChart"></canvas>
                <?php else: ?>
                    <div class="text-center p-5">
                        <i class="bi bi-bar-chart fs-1 text-muted"></i>
                        <h5 class="mt-3">Tidak ada data penjualan</h5>
                        <p class="text-muted">Belum ada transaksi yang selesai pada periode yang dipilih.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Jalankan script hanya jika ada data
            <?php if (!empty($product_labels)): ?>
                const productLabels = <?php echo json_encode($product_labels); ?>;
                const productQuantities = <?php echo json_encode($product_quantities); ?>;

                const ctx = document.getElementById('bestSellingChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar', // Mengubah menjadi 'bar' untuk orientasi vertikal atau horizontal
                    data: {
                        labels: productLabels,
                        datasets: [{
                            label: 'Jumlah Terjual',
                            data: productQuantities,
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.6)',
                                'rgba(54, 162, 235, 0.6)',
                                'rgba(255, 206, 86, 0.6)',
                                'rgba(75, 192, 192, 0.6)',
                                'rgba(153, 102, 255, 0.6)',
                                'rgba(255, 159, 64, 0.6)',
                                'rgba(255, 99, 132, 0.6)',
                                'rgba(54, 162, 235, 0.6)',
                                'rgba(255, 206, 86, 0.6)',
                                'rgba(75, 192, 192, 0.6)'
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)',
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        indexAxis: 'y', // Membuat grafik menjadi horizontal, lebih baik untuk nama produk
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false // Sembunyikan legenda karena sudah jelas dari judul
                            },
                            title: {
                                display: true,
                                text: 'Top 10 Produk Terlaris (Berdasarkan Jumlah Terjual)'
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1 // Pastikan skala sumbu x adalah bilangan bulat
                                }
                            }
                        }
                    }
                });
            <?php endif; ?>
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
