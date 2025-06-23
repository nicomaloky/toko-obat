<?php
session_start();
include 'koneksi.php';

// Cek jika user belum login, arahkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$produk = null;

// 1. Ambil ID Produk dari request (GET atau POST)
$produk_id = 0;
if (isset($_REQUEST['produk_id'])) {
    $produk_id = intval($_REQUEST['produk_id']);
}

// 2. Jika ID produk ada, ambil data produk dari database
if ($produk_id > 0) {
    $stmt_get = $conn->prepare("SELECT * FROM produk WHERE id = ?");
    $stmt_get->bind_param("i", $produk_id);
    $stmt_get->execute();
    $produk = $stmt_get->get_result()->fetch_assoc();
}

// 3. Jika produk valid, lanjutkan ke pemrosesan form atau tampilkan halaman
if ($produk) {
    // Cek apakah form disubmit untuk membuat pesanan
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order_whatsapp'])) {
        // Ambil data dari form yang disubmit
        $jumlah = intval($_POST['jumlah']);
        $nama_pemesan = trim($_POST['nama_pemesan']);
        $alamat_pemesan = trim($_POST['alamat_pemesan']);
        $telepon_pemesan = trim($_POST['telepon_pemesan']);
        $catatan = trim($_POST['catatan']);

        // Validasi input
        if (empty($nama_pemesan) || empty($alamat_pemesan) || empty($telepon_pemesan)) {
            $error = "Mohon lengkapi semua data pengiriman (Nama, Alamat, dan No. Telepon).";
        } elseif ($jumlah <= 0) {
            $error = "Jumlah pembelian harus minimal 1.";
        } elseif ($produk['stok'] < $jumlah) {
            // Validasi stok
            $error = "Maaf, stok produk tidak mencukupi. Sisa stok saat ini: " . $produk['stok'] . ".";
        } else {
            // Jika semua validasi lolos, proses pesanan
            $total_harga = $produk['harga'] * $jumlah;
            
            $stmt_insert = $conn->prepare("INSERT INTO pesanan (user_id, produk_id, jumlah, total_harga, status, nama_penerima, alamat_pengiriman, telepon_penerima, catatan) VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, ?)");
            $stmt_insert->bind_param("iiidssss", $user_id, $produk_id, $jumlah, $total_harga, $nama_pemesan, $alamat_pemesan, $telepon_pemesan, $catatan);

            if ($stmt_insert->execute()) {
                // Jika berhasil disimpan, arahkan ke WhatsApp
                $adminNumber = "6285798409804"; // GANTI DENGAN NOMOR WA ADMIN

                // --- BAGIAN YANG DIUBAH ---
                // Menyusun pesan dengan format baru yang lebih detail
                $message = "Halo, saya ingin memesan produk berikut:\n\n";
                $message .= "--- DETAIL PESANAN ---\n";
                $message .= "Produk: " . htmlspecialchars($produk['nama_produk']) . "\n";
                $message .= "Jumlah: " . $jumlah . " pcs\n";
                $message .= "Harga Satuan: Rp " . number_format($produk['harga'], 0, ',', '.') . "\n";
                $message .= "Total Harga: Rp " . number_format($total_harga, 0, ',', '.') . "\n\n";
                $message .= "--- DATA PEMESAN ---\n";
                $message .= "Nama: " . htmlspecialchars($nama_pemesan) . "\n";
                $message .= "Alamat Pengiriman: " . htmlspecialchars($alamat_pemesan) . "\n";
                $message .= "No. WhatsApp: " . htmlspecialchars($telepon_pemesan) . "\n";
                if (!empty($catatan)) {
                    $message .= "Catatan: " . htmlspecialchars($catatan) . "\n";
                }
                $message .= "\nMohon segera diproses ya. Terima kasih!";
                // --- AKHIR BAGIAN YANG DIUBAH ---

                $encodedMessage = urlencode($message);
                $whatsappURL = "https://wa.me/{$adminNumber}?text={$encodedMessage}";
                header("Location: " . $whatsappURL);
                exit();
            } else {
                $error = "Terjadi kesalahan pada database. Gagal menyimpan pesanan Anda.";
            }
        }
    }
} else {
    // Jika produk tidak ditemukan dari awal
    $error = "Produk tidak ditemukan atau belum dipilih. Silakan kembali ke halaman produk.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Checkout Pesanan - El's Pharmacy</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .card { border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .btn-whatsapp { background-color: #25D366; color: white; font-weight: 600; }
        .btn-whatsapp:hover { background-color: #1EBE57; color: white; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5 mb-5"><div class="row justify-content-center"><div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white"><h4 class="mb-0"><i class="bi bi-cart-check"></i> Konfirmasi Pesanan</h4></div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                        <br><a href="produk.php" class="alert-link">Kembali ke halaman produk</a>.
                    </div>
                <?php endif; ?>

                <?php if ($produk): ?>
                    <form method="POST" action="checkout.php">
                        <input type="hidden" name="produk_id" value="<?php echo $produk['id']; ?>">
                        
                        <h5><i class="bi bi-box-seam"></i> Produk yang Dipesan</h5>
                        <div class="row mb-4 align-items-center">
                            <div class="col-md-4">
                                <img src="img/<?php echo htmlspecialchars($produk['gambar']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>">
                            </div>
                            <div class="col-md-8">
                                <h6><?php echo htmlspecialchars($produk['nama_produk']); ?></h6>
                                <p class="text-muted mb-1">Stok tersedia: <strong><?php echo $produk['stok']; ?></strong></p>
                                <p>Harga Satuan: Rp <span id="hargaSatuanText"><?php echo number_format($produk['harga'], 0, ',', '.'); ?></span></p>
                                
                                <div class="mb-2">
                                    <label for="jumlah" class="form-label">Jumlah:</label>
                                    <input type="number" class="form-control" id="jumlah" name="jumlah" value="1" min="1" max="<?php echo $produk['stok']; ?>" required onchange="updateTotal()" <?php if($produk['stok'] <= 0) echo 'disabled'; ?>>
                                </div>
                                <h5>Total Harga: Rp <span id="totalHargaText"><?php echo number_format($produk['harga'], 0, ',', '.'); ?></span></h5>
                            </div>
                        </div>
                        <hr>
                        
                        <h5><i class="bi bi-person-fill"></i> Data Pengiriman</h5>
                        <div class="mb-3">
                            <label for="nama_pemesan" class="form-label">Nama Penerima</label>
                            <input type="text" class="form-control" id="nama_pemesan" name="nama_pemesan" value="<?php echo isset($_POST['nama_pemesan']) ? htmlspecialchars($_POST['nama_pemesan']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="alamat_pemesan" class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control" id="alamat_pemesan" name="alamat_pemesan" rows="3" required><?php echo isset($_POST['alamat_pemesan']) ? htmlspecialchars($_POST['alamat_pemesan']) : ''; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="telepon_pemesan" class="form-label">No. Telepon/WA</label>
                            <input type="tel" class="form-control" id="telepon_pemesan" name="telepon_pemesan" value="<?php echo isset($_POST['telepon_pemesan']) ? htmlspecialchars($_POST['telepon_pemesan']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="catatan" class="form-label">Catatan (Opsional)</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="2"><?php echo isset($_POST['catatan']) ? htmlspecialchars($_POST['catatan']) : ''; ?></textarea>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" name="submit_order_whatsapp" class="btn btn-whatsapp btn-lg" <?php if($produk['stok'] <= 0) echo 'disabled'; ?>>
                                <i class="bi bi-whatsapp"></i> 
                                <?php echo ($produk['stok'] > 0) ? 'Pesan dan Konfirmasi via WhatsApp' : 'Stok Habis'; ?>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div></div></div>
    
    <?php include 'footer.php'; ?>
    <script>
        function updateTotal() {
            const hargaSatuan = <?php echo isset($produk) ? $produk['harga'] : 0; ?>;
            const jumlah = document.getElementById('jumlah').value;
            const total = hargaSatuan * jumlah;
            document.getElementById('totalHargaText').textContent = total.toLocaleString('id-ID');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
