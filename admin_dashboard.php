<?php
session_start();
include 'koneksi.php';

// Cek apakah user adalah admin, jika bukan, arahkan ke halaman login.
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

// === PENGATURAN PAGINASI ===
$limit = 8; // Jumlah item per halaman

// === FUNGSI-FUNGSI CRUD (Create, Read, Update, Delete) ===

// 1. UPDATE STATUS PESANAN (MENGURANGI/MENGEMBALIKAN STOK)
if (isset($_POST['update_status'])) {
    $pesanan_id = intval($_POST['pesanan_id']);
    $new_status = $_POST['status'];

    $stmt_order_details = $conn->prepare("SELECT produk_id, jumlah, status FROM pesanan WHERE id = ?");
    $stmt_order_details->bind_param("i", $pesanan_id);
    $stmt_order_details->execute();
    $order = $stmt_order_details->get_result()->fetch_assoc();
    
    if ($order) {
        $conn->begin_transaction();
        try {
            // Update status pesanan
            $stmt_update_status = $conn->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
            $stmt_update_status->bind_param("si", $new_status, $pesanan_id);
            $stmt_update_status->execute();

            // Kurangi stok jika pesanan selesai
            if ($new_status == 'completed' && $order['status'] != 'completed') {
                $stmt_stock = $conn->prepare("UPDATE produk SET stok = stok - ? WHERE id = ? AND stok >= ?");
                $stmt_stock->bind_param("iii", $order['jumlah'], $order['produk_id'], $order['jumlah']);
                $stmt_stock->execute();
                if($stmt_stock->affected_rows == 0){
                    throw new Exception("Stok produk tidak mencukupi.");
                }
            } 
            // Kembalikan stok jika pesanan yang tadinya selesai dibatalkan
            else if ($new_status != 'completed' && $order['status'] == 'completed') {
                $stmt_stock = $conn->prepare("UPDATE produk SET stok = stok + ? WHERE id = ?");
                $stmt_stock->bind_param("ii", $order['jumlah'], $order['produk_id']);
                $stmt_stock->execute();
            }

            $conn->commit();
            $success_message = "Status pesanan #{$pesanan_id} dan stok berhasil diupdate!";
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Gagal mengupdate: " . $e->getMessage();
        }
    } else {
         $error_message = "Pesanan tidak ditemukan.";
    }
}

// 2. TAMBAH PRODUK BARU
if (isset($_POST['tambah_produk'])) {
    $nama_produk = trim($_POST['nama_produk']); $deskripsi = trim($_POST['deskripsi']); $harga = intval($_POST['harga']); $stok = intval($_POST['stok']); $id_kategori = intval($_POST['id_kategori']); $gambar_name = '';
    if (isset($_FILES["gambar"]) && $_FILES["gambar"]["error"] == 0) {
        $target_dir = "img/"; $gambar_name = time() . '_' . basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $gambar_name; $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (getimagesize($_FILES["gambar"]["tmp_name"]) && $_FILES["gambar"]["size"] <= 5000000 && in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (!move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) { $error_message = "Error saat upload file."; $gambar_name = ''; }
        } else { $error_message = "File tidak valid (bukan gambar, >5MB, atau format salah)."; $gambar_name = ''; }
    }
    if (!empty($nama_produk) && $harga > 0 && $stok >= 0 && $id_kategori > 0 && !empty($gambar_name)) {
        $stmt_insert = $conn->prepare("INSERT INTO produk (nama_produk, deskripsi, harga, stok, id_kategori, gambar) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("ssiiis", $nama_produk, $deskripsi, $harga, $stok, $id_kategori, $gambar_name);
        if ($stmt_insert->execute()) { $success_message = "Produk baru berhasil ditambahkan!"; } else { $error_message = "Gagal menambahkan produk ke database."; }
    } else { if (empty($error_message)) { $error_message = "Gagal menambahkan produk. Data tidak lengkap atau kategori belum dipilih."; } }
}

// 3. EDIT PRODUK
if (isset($_POST['edit_produk'])) {
    $produk_id = intval($_POST['produk_id']); $nama_produk = trim($_POST['nama_produk']); $deskripsi = trim($_POST['deskripsi']);
    $harga = intval($_POST['harga']); $stok = intval($_POST['stok']); $id_kategori = intval($_POST['id_kategori']); $gambar_lama = $_POST['gambar_lama']; $gambar_name = $gambar_lama;
    if (isset($_FILES["gambar"]) && !empty($_FILES["gambar"]["name"])) {
        $target_dir = "img/"; $gambar_name_new = time() . '_' . basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $gambar_name_new; $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (getimagesize($_FILES["gambar"]["tmp_name"]) && $_FILES["gambar"]["size"] <= 5000000 && in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                $gambar_name = $gambar_name_new;
                if (!empty($gambar_lama) && file_exists($target_dir . $gambar_lama)) { unlink($target_dir . $gambar_lama); }
            }
        } else { $error_message = "Gagal upload gambar baru. Perubahan lain tetap disimpan."; }
    }
    $stmt_update = $conn->prepare("UPDATE produk SET nama_produk=?, deskripsi=?, harga=?, stok=?, id_kategori=?, gambar=? WHERE id=?");
    $stmt_update->bind_param("ssiissi", $nama_produk, $deskripsi, $harga, $stok, $id_kategori, $gambar_name, $produk_id);
    if ($stmt_update->execute()) { $success_message = "Produk berhasil diupdate!"; } else { $error_message = "Gagal mengupdate produk."; }
}

// 4. HAPUS PRODUK
if (isset($_POST['hapus_produk'])) {
    $produk_id = intval($_POST['produk_id']);
    $query_gambar = $conn->prepare("SELECT gambar FROM produk WHERE id = ?"); $query_gambar->bind_param("i", $produk_id);
    $query_gambar->execute(); $data_gambar = $query_gambar->get_result()->fetch_assoc();
    $stmt_delete = $conn->prepare("DELETE FROM produk WHERE id = ?"); $stmt_delete->bind_param("i", $produk_id);
    if ($stmt_delete->execute()) {
        if ($data_gambar && !empty($data_gambar['gambar']) && file_exists("img/" . $data_gambar['gambar'])) { unlink("img/" . $data_gambar['gambar']); }
        $success_message = "Produk berhasil dihapus!";
    } else { $error_message = "Gagal menghapus produk."; }
}

// === STATISTIK KARTU ===
$total_produk_all = $conn->query("SELECT COUNT(*) as total FROM produk")->fetch_assoc()['total'];
$total_users = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'")->fetch_assoc()['total'];
$total_pesanan_all = $conn->query("SELECT COUNT(*) as total FROM pesanan")->fetch_assoc()['total'];
$pendapatan_result = $conn->query("SELECT SUM(total_harga) as total FROM pesanan WHERE status = 'completed'")->fetch_assoc();
$pendapatan = $pendapatan_result['total'] ?? 0;

// === LOGIKA UNTUK TAB KELOLA PESANAN ===
$search_pesanan = isset($_GET['search_pesanan']) ? trim($_GET['search_pesanan']) : '';
$page_pesanan = isset($_GET['page_pesanan']) ? (int)$_GET['page_pesanan'] : 1;
if ($page_pesanan < 1) $page_pesanan = 1;
$offset_pesanan = ($page_pesanan - 1) * $limit;
$sql_pesanan_count = "SELECT COUNT(*) as total FROM pesanan p JOIN users u ON p.user_id = u.id JOIN produk pr ON p.produk_id = pr.id";
$sql_pesanan_data = "SELECT p.*, u.username, pr.nama_produk FROM pesanan p JOIN users u ON p.user_id = u.id JOIN produk pr ON p.produk_id = pr.id";
if (!empty($search_pesanan)) {
    $search_clause = " WHERE p.id LIKE ? OR u.username LIKE ? OR pr.nama_produk LIKE ? OR p.nama_penerima LIKE ?";
    $sql_pesanan_count .= $search_clause;
    $sql_pesanan_data .= $search_clause;
}
$stmt_count_pesanan = $conn->prepare($sql_pesanan_count);
if (!empty($search_pesanan)) { $search_param = "%{$search_pesanan}%"; $stmt_count_pesanan->bind_param('ssss', $search_param, $search_param, $search_param, $search_param); }
$stmt_count_pesanan->execute();
$total_pesanan_filtered = $stmt_count_pesanan->get_result()->fetch_assoc()['total'];
$total_pages_pesanan = ceil($total_pesanan_filtered / $limit);
$sql_pesanan_data .= " ORDER BY p.tanggal_pesan DESC LIMIT ?, ?";
$stmt_data_pesanan = $conn->prepare($sql_pesanan_data);
if (!empty($search_pesanan)) { $stmt_data_pesanan->bind_param('ssssii', $search_param, $search_param, $search_param, $search_param, $offset_pesanan, $limit); } 
else { $stmt_data_pesanan->bind_param('ii', $offset_pesanan, $limit); }
$stmt_data_pesanan->execute();
$query_all_pesanan = $stmt_data_pesanan->get_result();

// === LOGIKA UNTUK TAB KELOLA PRODUK ===
$search_produk = isset($_GET['search_produk']) ? trim($_GET['search_produk']) : '';
$page_produk = isset($_GET['page_produk']) ? (int)$_GET['page_produk'] : 1;
if ($page_produk < 1) $page_produk = 1;
$offset_produk = ($page_produk - 1) * $limit;
$sql_produk_count = "SELECT COUNT(*) as total FROM produk";
$sql_produk_data = "SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.id_kategori = k.id_kategori";
if (!empty($search_produk)) {
    $search_clause_produk = " WHERE p.nama_produk LIKE ?";
    $sql_produk_count .= $search_clause_produk;
    $sql_produk_data .= $search_clause_produk;
}
$stmt_count_produk = $conn->prepare($sql_produk_count);
if (!empty($search_produk)) { $search_param_produk = "%{$search_produk}%"; $stmt_count_produk->bind_param('s', $search_param_produk); }
$stmt_count_produk->execute();
$total_produk_filtered = $stmt_count_produk->get_result()->fetch_assoc()['total'];
$total_pages_produk = ceil($total_produk_filtered / $limit);
$sql_produk_data .= " ORDER BY p.id DESC LIMIT ?, ?";
$stmt_data_produk = $conn->prepare($sql_produk_data);
if (!empty($search_produk)) { $stmt_data_produk->bind_param('sii', $search_param_produk, $offset_produk, $limit); } 
else { $stmt_data_produk->bind_param('ii', $offset_produk, $limit); }
$stmt_data_produk->execute();
$query_produk = $stmt_data_produk->get_result();

// Ambil semua kategori untuk dropdown
$query_kategori_all = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");
$kategori_list = $query_kategori_all->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Admin Dashboard - El's Pharmacy</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .card { border-radius: 0.75rem; }
        .pagination .page-link { color: #0d6efd; }
        .pagination .page-item.active .page-link { z-index: 3; color: #fff; background-color: #0d6efd; border-color: #0d6efd; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5 mb-5">
        <h2 class="mb-4"><i class="bi bi-speedometer2"></i> Admin Dashboard</h2>
        
        <?php if (!empty($success_message)): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo $success_message; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>
        <?php if (!empty($error_message)): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo $error_message; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>

        <!-- Kartu Statistik -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3"><div class="card text-white bg-info"><div class="card-body"><h5 class="card-title"><i class="bi bi-cart-fill"></i> Total Pesanan</h5><p class="card-text fs-4 fw-bold"><?php echo $total_pesanan_all; ?></p></div></div></div>
            <div class="col-lg-3 col-md-6 mb-3"><div class="card text-white bg-success"><div class="card-body"><h5 class="card-title"><i class="bi bi-cash-stack"></i> Pendapatan</h5><p class="card-text fs-4 fw-bold">Rp <?php echo number_format($pendapatan, 0, ',', '.'); ?></p></div></div></div>
            <div class="col-lg-3 col-md-6 mb-3"><div class="card text-white bg-primary"><div class="card-body"><h5 class="card-title"><i class="bi bi-box-seam"></i> Total Produk</h5><p class="card-text fs-4 fw-bold"><?php echo $total_produk_all; ?></p></div></div></div>
            <div class="col-lg-3 col-md-6 mb-3"><div class="card text-white bg-secondary"><div class="card-body"><h5 class="card-title"><i class="bi bi-people-fill"></i> Total Pengguna</h5><p class="card-text fs-4 fw-bold"><?php echo $total_users; ?></p></div></div></div>
        </div>

        <!-- Navigasi Tab -->
        <?php $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'pesanan'; ?>
        <ul class="nav nav-tabs" id="adminTab" role="tablist">
            <li class="nav-item" role="presentation"><button class="nav-link <?php if($active_tab == 'pesanan') echo 'active'; ?>" id="pesanan-tab" data-bs-toggle="tab" data-bs-target="#pesanan-tab-pane" type="button">Kelola Pesanan</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link <?php if($active_tab == 'produk') echo 'active'; ?>" id="produk-tab" data-bs-toggle="tab" data-bs-target="#produk-tab-pane" type="button">Kelola Produk</button></li>
            <li class="nav-item" role="presentation"><a class="nav-link" href="laporan_penjualan.php"><i class="bi bi-bar-chart-line"></i> Laporan</a></li>
        </ul>

        <!-- Konten Tab -->
        <div class="tab-content pt-3" id="adminTabContent">
            <!-- Tab Kelola Pesanan -->
            <div class="tab-pane fade <?php if($active_tab == 'pesanan') echo 'show active'; ?>" id="pesanan-tab-pane" role="tabpanel">
                <div class="card"><div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h4 class="card-title mb-0">Daftar Pesanan Masuk</h4>
                        <form method="GET" class="d-flex ms-auto" style="max-width: 400px;">
                            <input type="hidden" name="tab" value="pesanan">
                            <input type="text" class="form-control me-2" name="search_pesanan" placeholder="Cari ID, User, Produk..." value="<?php echo htmlspecialchars($search_pesanan); ?>">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark"><tr><th>No.</th><th>User</th><th>Produk</th><th>Total</th><th>Penerima</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
                            <tbody>
                            <?php 
                            $nomor_pesanan = $offset_pesanan + 1;
                            if ($query_all_pesanan && $query_all_pesanan->num_rows > 0): while($p = $query_all_pesanan->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $nomor_pesanan++; ?></td>
                                    <td><?php echo htmlspecialchars($p['username']); ?></td>
                                    <td><?php echo htmlspecialchars($p['nama_produk']); ?> (x<?php echo $p['jumlah']; ?>)</td>
                                    <td>Rp <?php echo number_format($p['total_harga'], 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($p['nama_penerima']); ?><br><small class="text-muted"><?php echo htmlspecialchars($p['telepon_penerima']); ?></small></td>
                                    <td>
                                        <?php $s_class = 'bg-secondary'; if($p['status']=='pending')$s_class='bg-warning text-dark'; if($p['status']=='completed')$s_class='bg-success'; if($p['status']=='cancelled')$s_class='bg-danger';?>
                                        <span class="badge <?php echo $s_class; ?>"><?php echo ucfirst($p['status']); ?></span>
                                    </td>
                                    <td><?php echo date('d M Y, H:i', strtotime($p['tanggal_pesan'])); ?></td>
                                    <td><form method="POST"><input type="hidden" name="pesanan_id" value="<?php echo $p['id']; ?>"><div class="input-group"><select name="status" class="form-select form-select-sm"><option value="pending" <?php if($p['status']=='pending') echo 'selected'; ?>>Pending</option><option value="completed" <?php if($p['status']=='completed') echo 'selected'; ?>>Completed</option><option value="cancelled" <?php if($p['status']=='cancelled') echo 'selected'; ?>>Cancelled</option></select><button type="submit" name="update_status" class="btn btn-primary btn-sm"><i class="bi bi-check-lg"></i></button></div></form></td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="8" class="text-center p-5 text-muted"><?php if(!empty($search_pesanan)): ?>Pesanan dengan kata kunci "<?php echo htmlspecialchars($search_pesanan); ?>" tidak ditemukan.<?php else: ?>Belum ada pesanan masuk.<?php endif; ?></td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <nav class="mt-3">
                      <ul class="pagination justify-content-center">
                        <?php if($page_pesanan > 1): ?><li class="page-item"><a class="page-link" href="?tab=pesanan&page_pesanan=<?php echo $page_pesanan - 1; ?>&search_pesanan=<?php echo urlencode($search_pesanan); ?>">Sebelumnya</a></li><?php endif; ?>
                        <?php for($i = 1; $i <= $total_pages_pesanan; $i++): ?><li class="page-item <?php if($i == $page_pesanan) echo 'active'; ?>"><a class="page-link" href="?tab=pesanan&page_pesanan=<?php echo $i; ?>&search_pesanan=<?php echo urlencode($search_pesanan); ?>"><?php echo $i; ?></a></li><?php endfor; ?>
                        <?php if($page_pesanan < $total_pages_pesanan): ?><li class="page-item"><a class="page-link" href="?tab=pesanan&page_pesanan=<?php echo $page_pesanan + 1; ?>&search_pesanan=<?php echo urlencode($search_pesanan); ?>">Selanjutnya</a></li><?php endif; ?>
                      </ul>
                    </nav>
                </div></div>
            </div>
            
            <div class="tab-pane fade <?php if($active_tab == 'produk') echo 'show active'; ?>" id="produk-tab-pane" role="tabpanel">
                <div class="card">
                    <div class="card-header"><div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                       <h4 class="mb-0">Daftar Produk</h4>
                       <form method="GET" class="d-flex" style="max-width: 400px;"><input type="hidden" name="tab" value="produk"><input type="text" class="form-control me-2" name="search_produk" placeholder="Cari nama produk..." value="<?php echo htmlspecialchars($search_produk); ?>"><button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button></form>
                       <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahProdukModal"><i class="bi bi-plus-circle"></i> Tambah Produk</button>
                    </div></div>
                    <div class="card-body"><div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark"><tr><th>No.</th><th>Gambar</th><th>Nama Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Aksi</th></tr></thead>
                            <tbody>
                            <?php 
                            $nomor_produk = $offset_produk + 1;
                            if ($query_produk && $query_produk->num_rows > 0): while ($produk = $query_produk->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $nomor_produk++; ?></td>
                                    <td><img src="img/<?php echo htmlspecialchars($produk['gambar']); ?>" width="80" class="img-thumbnail" onerror="this.src='https://placehold.co/80x80/EFEFEF/AAAAAA?text=N/A'"></td>
                                    <td><?php echo htmlspecialchars($produk['nama_produk']); ?></td>
                                    <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($produk['nama_kategori'] ?? 'N/A'); ?></span></td>
                                    <td>Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></td>
                                    <td><?php $stok = $produk['stok']; if($stok<=0){echo '<span class="badge bg-danger">Habis</span>';}elseif($stok<=10){echo '<span class="badge bg-warning text-dark">'.$stok.'</span>';}else{echo '<span class="badge bg-success">'.$stok.'</span>';}?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editProdukModal<?php echo $produk['id']; ?>"><i class="bi bi-pencil-square"></i></button>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus produk ini?');"><input type="hidden" name="produk_id" value="<?php echo $produk['id']; ?>"><button type="submit" name="hapus_produk" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button></form>
                                    </td>
                                </tr>
                                <div class="modal fade" id="editProdukModal<?php echo $produk['id']; ?>" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="POST" enctype="multipart/form-data"><div class="modal-header"><h5 class="modal-title">Edit Produk</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="produk_id" value="<?php echo $produk['id']; ?>"><input type="hidden" name="gambar_lama" value="<?php echo htmlspecialchars($produk['gambar']); ?>"><div class="mb-3"><label class="form-label">Nama Produk</label><input type="text" name="nama_produk" class="form-control" value="<?php echo htmlspecialchars($produk['nama_produk']); ?>" required></div><div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="4" required><?php echo htmlspecialchars($produk['deskripsi']); ?></textarea></div><div class="mb-3"><label class="form-label">Kategori</label><select name="id_kategori" class="form-select" required><option value="">-- Pilih Kategori --</option><?php foreach ($kategori_list as $kategori): ?><option value="<?php echo $kategori['id_kategori']; ?>" <?php if ($produk['id_kategori'] == $kategori['id_kategori']) echo 'selected'; ?>><?php echo htmlspecialchars($kategori['nama_kategori']); ?></option><?php endforeach; ?></select></div><div class="row"><div class="col-md-6 mb-3"><label class="form-label">Harga</label><input type="number" name="harga" class="form-control" value="<?php echo $produk['harga']; ?>" required></div><div class="col-md-6 mb-3"><label class="form-label">Stok</label><input type="number" name="stok" class="form-control" value="<?php echo $produk['stok']; ?>" required min="0"></div></div><div class="mb-3"><label class="form-label">Ganti Gambar (kosongkan jika tidak diubah)</label><input type="file" name="gambar" class="form-control" accept="image/*"></div></div><div class="modal-footer"><button type="button" class="btn-close" data-bs-dismiss="modal">Batal</button><button type="submit" name="edit_produk" class="btn btn-primary">Simpan</button></div></form></div></div></div>
                            <?php endwhile; else: ?>
                                <tr><td colspan="7" class="text-center p-5 text-muted"><?php if(!empty($search_produk)): ?>Produk dengan nama "<?php echo htmlspecialchars($search_produk); ?>" tidak ditemukan.<?php else: ?>Belum ada produk.<?php endif; ?></td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <nav class="mt-3">
                      <ul class="pagination justify-content-center">
                        <?php if($page_produk > 1): ?><li class="page-item"><a class="page-link" href="?tab=produk&page_produk=<?php echo $page_produk - 1; ?>&search_produk=<?php echo urlencode($search_produk); ?>">Sebelumnya</a></li><?php endif; ?>
                        <?php for($i = 1; $i <= $total_pages_produk; $i++): ?><li class="page-item <?php if($i == $page_produk) echo 'active'; ?>"><a class="page-link" href="?tab=produk&page_produk=<?php echo $i; ?>&search_produk=<?php echo urlencode($search_produk); ?>"><?php echo $i; ?></a></li><?php endfor; ?>
                        <?php if($page_produk < $total_pages_produk): ?><li class="page-item"><a class="page-link" href="?tab=produk&page_produk=<?php echo $page_produk + 1; ?>&search_produk=<?php echo urlencode($search_produk); ?>">Selanjutnya</a></li><?php endif; ?>
                      </ul>
                    </nav>
                </div></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="tambahProdukModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="POST" enctype="multipart/form-data"><div class="modal-header"><h5 class="modal-title">Tambah Produk Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="mb-3"><label class="form-label">Nama Produk</label><input type="text" name="nama_produk" class="form-control" required></div><div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="4" required></textarea></div><div class="mb-3"><label class="form-label">Kategori</label><select name="id_kategori" class="form-select" required><option value="" disabled selected>-- Pilih Kategori --</option><?php foreach ($kategori_list as $kategori): ?><option value="<?php echo $kategori['id_kategori']; ?>"><?php echo htmlspecialchars($kategori['nama_kategori']); ?></option><?php endforeach; ?></select></div><div class="row"><div class="col-md-6 mb-3"><label class="form-label">Harga</label><input type="number" name="harga" class="form-control" required></div><div class="col-md-6 mb-3"><label class="form-label">Stok Awal</label><input type="number" name="stok" class="form-control" required min="0"></div></div><div class="mb-3"><label class="form-label">Upload Gambar</label><input type="file" name="gambar" class="form-control" accept="image/*" required></div></div><div class="modal-footer"><button type="button" class="btn-close" data-bs-dismiss="modal">Batal</button><button type="submit" name="tambah_produk" class="btn btn-primary">Tambah</button></div></form></div></div></div>
    
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
