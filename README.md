El's Pharmacy - Sistem E-Commerce Apotek Sederhana
Selamat datang di El's Pharmacy, sebuah proyek aplikasi web e-commerce yang dirancang untuk apotek atau toko obat. Sistem ini dibangun menggunakan PHP native dan MySQL, dengan antarmuka yang responsif berkat Bootstrap. Proyek ini mencakup fungsionalitas lengkap mulai dari manajemen produk oleh admin hingga proses pemesanan oleh pelanggan yang terintegrasi dengan WhatsApp.


(Ganti gambar di atas dengan screenshot aplikasi Anda)

Fitur Utama
Untuk Pengguna (Pelanggan)
Registrasi & Login: Sistem autentikasi untuk pengguna dan admin.

Katalog Produk: Menampilkan semua produk dengan gambar, deskripsi, harga, dan informasi stok.

Pencarian & Filter: Pengguna dapat mencari produk berdasarkan nama dan memfilter berdasarkan kategori.

Informasi Stok: Status stok yang jelas (Tersedia, Terbatas, Habis) pada setiap produk.

Proses Checkout:

Menyimpan pesanan ke database.

Mengarahkan pengguna ke WhatsApp untuk konfirmasi manual ke admin dengan detail pesanan yang sudah terisi otomatis.

Dasbor Pengguna: Pengguna dapat melihat riwayat dan status pesanan mereka.

Untuk Admin
Dasbor Admin Terpusat: Halaman khusus untuk mengelola seluruh aspek toko.

Manajemen Produk (CRUD): Admin dapat menambah, melihat, mengedit, dan menghapus produk.

Manajemen Kategori & Stok: Stok dan kategori dapat diatur untuk setiap produk.

Manajemen Pesanan:

Melihat semua pesanan yang masuk dengan detail lengkap.

Memperbarui status pesanan (Pending, Completed, Cancelled).

Stok produk akan berkurang otomatis saat status diubah menjadi Completed.

Stok produk akan kembali otomatis jika status pesanan yang tadinya Completed diubah ke status lain.

Pencarian & Paginasi: Fitur pencarian dan pembagian halaman pada tabel pesanan dan produk untuk memudahkan manajemen data.

Laporan Penjualan: Halaman khusus dengan grafik visual untuk melihat produk terlaris per bulan, yang dapat difilter berdasarkan kategori.

Teknologi yang Digunakan
Backend: PHP (Native)

Database: MySQL / MariaDB

Frontend: HTML, CSS

Framework & Library:

Bootstrap 5 untuk desain antarmuka yang responsif.

Bootstrap Icons untuk ikon.

Chart.js для визуализации данных в виде графиков.

Google Fonts (Poppins) untuk tipografi.

Instalasi & Konfigurasi
Ikuti langkah-langkah berikut untuk menjalankan proyek ini di lingkungan lokal Anda.

Prasyarat
Web Server (misalnya XAMPP, WAMP)

PHP (disarankan versi 7.4 atau lebih baru)

MySQL atau MariaDB

1. Clone Repositori
Clone repositori ini ke dalam direktori htdocs (untuk XAMPP) atau www (untuk WAMP) Anda.

git clone https://github.com/username/nama-repo.git

Atau cukup unduh dan ekstrak file ZIP ke dalam direktori tersebut.

2. Konfigurasi Database
Buka phpMyAdmin (atau tools database lain).

Buat database baru, misalnya dengan nama db_apotek.

Impor file .sql yang berisi struktur tabel ke dalam database yang baru dibuat. Struktur yang dibutuhkan adalah:

users: Untuk menyimpan data pengguna dan admin.

kategori: Untuk menyimpan daftar kategori produk.

produk: Untuk menyimpan detail produk, termasuk stok dan relasi ke id_kategori.

pesanan: Untuk menyimpan riwayat transaksi.

3. Koneksi Database
Buka file koneksi.php.

Sesuaikan konfigurasi berikut dengan pengaturan database Anda.

<?php
$hostname = 'localhost';
$username = 'root'; // Ganti jika username database Anda berbeda
$password = '';     // Ganti jika Anda menggunakan password
$database = 'db_apotek'; // Sesuaikan dengan nama database Anda

$conn = mysqli_connect($hostname, $username, $password, $database);

if (mysqli_connect_errno()) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>

4. Konfigurasi Kustom
Nomor WhatsApp Admin: Buka file checkout.php dan ubah nomor telepon pada baris berikut:

$adminNumber = "628xxxxxxxxxx"; // GANTI DENGAN NOMOR WA ADMIN ANDA

Folder Gambar: Pastikan ada folder bernama img di direktori utama proyek untuk menyimpan gambar-gambar produk yang di-upload.

5. Jalankan Aplikasi
Nyalakan server Apache dan MySQL Anda melalui XAMPP/WAMP.

Buka browser dan akses http://localhost/nama-folder-proyek/.

Struktur Proyek
/
├── img/                  # Folder untuk menyimpan gambar produk
├── admin_dashboard.php   # Halaman dasbor untuk admin
├── checkout.php          # Halaman proses checkout
├── index.php             # Halaman utama
├── koneksi.php           # File konfigurasi koneksi database
├── laporan_penjualan.php # Halaman grafik laporan
├── login.php             # Halaman login pengguna
├── produk.php            # Halaman katalog produk
├── register.php          # Halaman registrasi pengguna
├── user_dashboard.php    # Halaman dasbor untuk pengguna
├── navbar.php            # Komponen navigasi
├── footer.php            # Komponen footer
└── style.css             # File CSS kustom

Terima kasih telah menggunakan proyek ini!
