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

// 1. Proses Hapus Barang
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("DELETE FROM items WHERE id = :id");
    if ($stmt->execute(['id' => $id])) {
        $success = "Barang berhasil dihapus!";
    } else {
        $error = "Gagal menghapus barang.";
    }
}

// 2. Proses Tambah / Edit Barang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_item'])) {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $stock = intval($_POST['stock'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);

    if (!empty($name) && !empty($category)) {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE items SET name = :name, category = :category, stock = :stock, price = :price WHERE id = :id");
            if ($stmt->execute(['name' => $name, 'category' => $category, 'stock' => $stock, 'price' => $price, 'id' => $id])) {
                $success = "Data barang berhasil diperbarui!";
            } else {
                $error = "Gagal memperbarui barang.";
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO items (name, category, stock, price) VALUES (:name, :category, :stock, :price)");
            if ($stmt->execute(['name' => $name, 'category' => $category, 'stock' => $stock, 'price' => $price])) {
                $success = "Barang berhasil ditambahkan!";
            } else {
                $error = "Gagal menambahkan barang.";
            }
        }
    } else {
        $error = "Nama dan kategori barang harus diisi!";
    }
}

// Ambil data barang untuk di-Edit jika tombol edit diklik
$edit_item = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = :id");
    $stmt->execute(['id' => intval($_GET['id'])]);
    $edit_item = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 3. Fitur Pencarian Data Barang
$search = trim($_GET['search'] ?? '');
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM items WHERE name LIKE :s OR category LIKE :s ORDER BY id DESC");
    $stmt->execute(['s' => "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM items ORDER BY id DESC");
}
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Barang - Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Gradasi Oranye ke Cokelat Tua */
        .bg-orange-gradient {
            background: linear-gradient(135deg, #e65100 0%, #4a2c11 100%) !important;
        }
        
        /* Tombol Tema Gradasi */
        .btn-orange-theme {
            background: linear-gradient(135deg, #f57c00 0%, #5d3a1a 100%) !important;
            color: #ffffff !important;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-orange-theme:hover {
            background: linear-gradient(135deg, #e65100 0%, #3e2723 100%) !important;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        /* Header Tabel */
        .table-orange-header {
            background: linear-gradient(135deg, #e65100 0%, #4a2c11 100%) !important;
            color: #ffffff !important;
        }

        /* Judul Halaman */
        .text-orange-dark {
            color: #4a2c11 !important;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Navbar Gradasi Oranye - Cokelat Tua -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-orange-gradient shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">Inventory System</a>
            <div class="d-flex align-items-center text-white">
                <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
                <a href="transactions.php" class="btn btn-outline-light btn-sm me-2">Transaksi Stok</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container mt-4">
        <h2 class="fw-bold mb-4 text-orange-dark">Manajemen Data Barang</h2>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success py-2"><?= e($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2"><?= e($error) ?></div>
        <?php endif; ?>

        <!-- Form Tambah/Edit Barang -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 p-4">
            <h5 class="fw-bold mb-3 text-orange-dark"><?= $edit_item ? 'Edit Barang' : 'Tambah Barang Baru' ?></h5>
            <form method="POST" action="items.php">
                <input type="hidden" name="id" value="<?= $edit_item['id'] ?? 0 ?>">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Nama Barang</label>
                        <input type="text" name="name" class="form-control" value="<?= e($edit_item['name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Kategori</label>
                        <input type="text" name="category" class="form-control" value="<?= e($edit_item['category'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Stok</label>
                        <input type="number" name="stock" class="form-control" value="<?= e($edit_item['stock'] ?? 0) ?>" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Harga (Rp)</label>
                        <input type="number" step="0.01" name="price" class="form-control" value="<?= e($edit_item['price'] ?? 0) ?>" required>
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" name="save_item" class="btn btn-orange-theme w-100"><?= $edit_item ? 'Update' : 'Simpan' ?></button>
                        <?php if ($edit_item): ?>
                            <a href="items.php" class="btn btn-secondary ms-2">Batal</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabel Daftar Barang & Fitur Cari -->
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0 text-orange-dark">Daftar Stok Barang</h5>
                <!-- Form Pencarian -->
                <form method="GET" class="d-flex" style="max-width: 300px;">
                    <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Cari nama/kategori..." value="<?= e($search) ?>">
                    <button type="submit" class="btn btn-sm btn-orange-theme">Cari</button>
                    <?php if (!empty($search)): ?>
                        <a href="items.php" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr class="table-orange-header">
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Harga</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($items) > 0): ?>
                            <?php $no = 1; foreach ($items as $item): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= e($item['name']) ?></td>
                                    <td><?= e($item['category']) ?></td>
                                    <td><span class="badge bg-warning text-dark"><?= e($item['stock']) ?></span></td>
                                    <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <a href="items.php?action=edit&id=<?= $item['id'] ?>" class="btn btn-warning btn-sm me-1">Edit</a>
                                        <a href="items.php?action=delete&id=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus barang ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Data barang tidak ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>