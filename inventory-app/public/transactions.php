<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/escape.php';

$success = '';
$error = '';

// Proses Simpan Transaksi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_transaction'])) {
    $item_id = intval($_POST['item_id'] ?? 0);
    $type = $_POST['type'] ?? 'in';
    $quantity = intval($_POST['quantity'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    if ($item_id > 0 && $quantity > 0) {
        $stmt = $pdo->prepare("SELECT stock FROM items WHERE id = :id");
        $stmt->execute(['id' => $item_id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($type === 'out' && $item['stock'] < $quantity) {
            $error = "Stok tidak cukup! Stok saat ini hanya " . $item['stock'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO transactions (item_id, type, quantity, notes) VALUES (:item_id, :type, :quantity, :notes)");
            $stmt->execute(['item_id' => $item_id, 'type' => $type, 'quantity' => $quantity, 'notes' => $notes]);

            $new_stock = ($type === 'in') ? ($item['stock'] + $quantity) : ($item['stock'] - $quantity);
            $update = $pdo->prepare("UPDATE items SET stock = :stock WHERE id = :id");
            $update->execute(['stock' => $new_stock, 'id' => $item_id]);

            $success = "Transaksi berhasil dicatat & stok diperbarui!";
        }
    } else {
        $error = "Pilih barang dan masukkan jumlah yang valid!";
    }
}

$items_list = $pdo->query("SELECT id, name, stock FROM items ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$stmt = $pdo->query("SELECT t.*, i.name AS item_name FROM transactions t JOIN items i ON t.item_id = i.id ORDER BY t.id DESC");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Stok - Inventory System</title>
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
        .table-orange-header {
            background: linear-gradient(135deg, #e65100 0%, #4a2c11 100%) !important;
            color: #ffffff !important;
        }
        .text-orange-dark {
            color: #4a2c11 !important;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-orange-gradient shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">Inventory System</a>
            <div class="d-flex align-items-center text-white">
                <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
                <a href="items.php" class="btn btn-outline-light btn-sm me-2">Kelola Barang</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container mt-4">
        <h2 class="fw-bold mb-4 text-orange-dark">Transaksi Barang Masuk / Keluar</h2>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success py-2"><?= e($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2"><?= e($error) ?></div>
        <?php endif; ?>

        <!-- Form Catat Transaksi -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 p-4">
            <h5 class="fw-bold mb-3 text-orange-dark">Catat Transaksi Baru</h5>
            <form method="POST">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Pilih Barang</label>
                        <select name="item_id" class="form-select" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php foreach ($items_list as $i): ?>
                                <option value="<?= $i['id'] ?>"><?= e($i['name']) ?> (Stok: <?= $i['stock'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Jenis Transaksi</label>
                        <select name="type" class="form-select" required>
                            <option value="in">Barang Masuk (+)</option>
                            <option value="out">Barang Keluar (-)</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="notes" class="form-control" placeholder="Contoh: Restok dari supplier">
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" name="save_transaction" class="btn btn-orange-theme w-100">Proses</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabel Riwayat Transaksi -->
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <h5 class="fw-bold mb-3 text-orange-dark">Riwayat Transaksi</h5>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr class="table-orange-header">
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nama Barang</th>
                            <th>Jenis</th>
                            <th>Jumlah</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($transactions) > 0): ?>
                            <?php $no = 1; foreach ($transactions as $t): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $t['created_at'] ?></td>
                                    <td><?= e($t['item_name']) ?></td>
                                    <td>
                                        <?php if ($t['type'] === 'in'): ?>
                                            <span class="badge bg-success">Masuk</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Keluar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= $t['quantity'] ?></strong></td>
                                    <td><?= e($t['notes']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada riwayat transaksi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>