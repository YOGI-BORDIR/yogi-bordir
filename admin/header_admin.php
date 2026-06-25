<?php
ob_start();

session_start();
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - Admin Yogi Bordir' : 'Admin Yogi Bordir' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --hijau-tua: #2d5a27; --hijau-muda: #4a8c3f; --hijau-aksen: #6db85a; }
        body { font-family: 'Poppins', sans-serif; background: #f4f6f9; }
        .sidebar { width: 260px; min-height: 100vh; background: linear-gradient(180deg, #1e3d1a, #2d5a27); position: fixed; top: 0; left: 0; z-index: 100; transition: .3s; overflow-y: auto; height: 100vh; }
        .sidebar-brand { padding: 24px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar .nav-link { color: rgba(255,255,255,0.75); padding: 7px 20px; border-radius: 10px; margin: 2px 10px; font-size: .875rem; font-weight: 500; transition: .2s; display: flex; align-items: center; gap: 10px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: white; background: rgba(255,255,255,0.15); }
        .sidebar .nav-link i { width: 20px; text-align: center; }
        .sidebar .nav-section { color: rgba(255,255,255,0.35); font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; padding: 4px 30px 2px; margin-top: 2px; }
        .main-content { margin-left: 260px; min-height: 100vh; }
        .topbar { background: white; padding: 16px 24px; border-bottom: 1px solid #e9ecef; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 99; }
        .content-area { padding: 24px; }
        .card-stat { border: none; border-radius: 16px; padding: 24px; color: white; }
        .table th { font-weight: 600; font-size: .85rem; color: #6c757d; text-transform: uppercase; letter-spacing: .5px; }
        .badge-kat { background: rgba(45,90,39,0.1); color: var(--hijau-tua); font-size: .75rem; padding: 4px 10px; border-radius: 50px; font-weight: 500; }
        @media(max-width:768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-scissors text-white fs-4"></i>
            <div>
                <div class="text-white fw-bold">Yogi Bordir</div>
                <div class="text-white-50" style="font-size:.75rem">Panel Admin</div>
            </div>
        </div>
    </div>
    <nav class="mt-3">
        <a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':'' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="nav-section">Konten Halaman</div>
        <a href="beranda.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])=='beranda.php'?'active':'' ?>">
            <i class="bi bi-house"></i> Edit Beranda
        </a>
        <a href="layanan.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])=='layanan.php'?'active':'' ?>">
            <i class="bi bi-tools"></i> Kelola Layanan
        </a>
        <a href="profil.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])=='profil.php'?'active':'' ?>">
            <i class="bi bi-building"></i> Profil Usaha
        </a>

        <div class="nav-section">Katalog</div>
        <a href="produk.php" class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']),['produk.php','tambah_produk.php','edit_produk.php'])?'active':'' ?>">
            <i class="bi bi-grid"></i> Produk
        </a>
        <a href="kategori.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])=='kategori.php'?'active':'' ?>">
            <i class="bi bi-tags"></i> Kategori
        </a>

        <div class="nav-section">Pengaturan</div>
        <a href="kontak.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])=='kontak.php'?'active':'' ?>">
            <i class="bi bi-telephone"></i> Kontak Usaha
        </a>

        <hr style="border-color:rgba(255,255,255,0.1);margin:4px 20px">
        <a href="../index.php" class="nav-link" target="_blank">
            <i class="bi bi-box-arrow-up-right"></i> Lihat Website
        </a>
        <a href="logout.php" class="nav-link mb-4" style="color:rgba(255,100,100,0.85)">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
        <div style="height:60px"></div>
    </nav>
</div>

<!-- MAIN -->
<div class="main-content">
    <div class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-outline-secondary d-md-none" id="toggleSidebar">
                <i class="bi bi-list"></i>
            </button>
            <h6 class="mb-0 fw-semibold"><?= isset($page_title) ? $page_title : 'Dashboard' ?></h6>
        </div>
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-person-circle fs-5 text-muted"></i>
            <span class="small fw-semibold"><?= htmlspecialchars($_SESSION['admin_nama']) ?></span>
        </div>
    </div>
    <div class="content-area">