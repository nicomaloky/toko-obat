<?php
// Konfigurasi database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "el_pharmacy";

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset untuk mencegah masalah encoding
$conn->set_charset("utf8");
?>