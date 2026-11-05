<?php
// ==============================================================
// KONFIGURASI KONEKSI DATABASE
// ==============================================================

$host     = "localhost";
$username = "root";
$password = "";
$database = "petshop_db";
$port     = 3306;

// Buat koneksi menggunakan MySQLi
$conn = mysqli_connect($host, $username, $password, $database, $port);

// Cek koneksi
if (!$conn) {
    die("
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 25px; border: 1px solid #f5c6cb; background-color: #f8d7da; color: #721c24; border-radius: 8px;'>
            <h3 style='margin-top:0;'>⚠️ Gagal Terhubung ke Database MySQL!</h3>
            <p><strong>Penyebab:</strong> " . mysqli_connect_error() . "</p>
            <hr style='border: 0; border-top: 1px solid #f5c6cb;'>
            <p><strong>Langkah Mengatasi:</strong></p>
            <ol>
                <li>Pastikan MySQL di <b>XAMPP / Laragon</b> sudah dijalankan (Status: Running).</li>
                <li>Pastikan Anda sudah mengimpor file <code>database.sql</code> ke phpMyAdmin.</li>
                <li>Pastikan nama database adalah <code>petshop_db</code>.</li>
            </ol>
        </div>
    ");
}

// Set karakter encoding
mysqli_set_charset($conn, "utf8mb4");

// Mulai session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

