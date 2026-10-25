<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$user = $_SESSION['user'];

// Ringkasan Stok
$stmt = $pdo->query("SELECT COUNT(*) AS total_items, SUM(stock) AS total_stock FROM items");
$summary = $stmt->fetch(PDO::FETCH_ASSOC);

// Ringkasan Transaksi Masuk/Keluar
$stmt_in = $pdo->query("SELECT SUM(quantity) FROM transactions WHERE type='in'");
$total_in = $stmt_in->fetchColumn() ?: 0;

$stmt_out = $pdo->query("SELECT SUM(quantity) FROM transactions WHERE type='out'");
$total_out = $stmt_out->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Inventory Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg-orange-gradient {
            background: linear-gradient(135deg, #e65100 0%, #4a2c11 100%) !important;
        }
        .btn-orange-theme {
            background: linear-gradient(135deg, #f57c00 0%, #5d3a1a 100%) !important;
            color: #ffffff !important;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-orange-theme:hover {
            background: linear-gradient(135deg, #e65100 0%, #3e2723 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .text-orange-dark {
            color: #4a2c11 !important;
        }
        .card-orange-primary {
            background: linear-gradient(135deg, #e65100 0%, #8d4004 100%) !important;
        }
        .card-orange-dark {
            background: linear-gradient(135deg, #5d3a1a 0%, #2e1706 100%) !important;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-orange-gradient shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Inventory System</a>
            <div class="d-flex align-items-center text-white">
                <span class="me-3">Halo, <strong><?= htmlspecialchars($user['name']) ?></strong></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm rounded-3 p-4">
                    <h2 class="fw-bold text-orange-dark">Selamat Datang di Dashboard! 👋</h2>
                    <p class="text-muted">Anda berhasil masuk sebagai <strong><?= htmlspecialchars($user['role']) ?></strong> (<?= htmlspecialchars($user['email']) ?>).</p>
                    <hr>
                    <div class="row mt-4">
                        <div class="col-md-3 mb-3">
                            <div class="card card-orange-primary text-white border-0 shadow-sm p-3 rounded-3">
                                <h5>Total Jenis Barang</h5>
                                <h3 class="fw-bold"><?= $summary['total_items'] ?? 0 ?></h3>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card card-orange-dark text-white border-0 shadow-sm p-3 rounded-3">
                                <h5>Total Stok Fisik</h5>
                                <h3 class="fw-bold"><?= $summary['total_stock'] ?? 0 ?> Pcs</h3>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white border-0 shadow-sm p-3 rounded-3">
                                <h5>Total Barang Masuk</h5>
                                <h3 class="fw-bold"><?= $total_in ?> Pcs</h3>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-warning text-dark border-0 shadow-sm p-3 rounded-3">
                                <h5>Total Barang Keluar</h5>
                                <h3 class="fw-bold"><?= $total_out ?> Pcs</h3>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="items.php" class="btn btn-orange-theme px-4 py-2 me-2">Kelola Data Barang &rarr;</a>
                        <a href="transactions.php" class="btn btn-outline-dark px-4 py-2">Transaksi Barang Masuk/Keluar &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>