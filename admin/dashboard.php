<?php
$page_title = 'Dashboard';
require_once 'header_admin.php';
$db = getDB();

$total_produk   = $db->query("SELECT COUNT(*) as c FROM produk")->fetch_assoc()['c'];
$total_kategori = $db->query("SELECT COUNT(*) as c FROM kategori")->fetch_assoc()['c'];
$produk_terbaru = $db->query("SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON p.id_kategori=k.id_kategori ORDER BY p.created_at DESC LIMIT 5");
?>

<!-- STAT CARDS -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card-stat" style="background:linear-gradient(135deg,#2d5a27,#4a8c3f)">
            <i class="bi bi-grid fs-2 opacity-75 mb-2 d-block"></i>
            <div class="fs-2 fw-bold"><?= $total_produk ?></div>
            <div class="opacity-75 small">Total Produk</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card-stat" style="background:linear-gradient(135deg,#1a6b5a,#2a9e85)">
            <i class="bi bi-tags fs-2 opacity-75 mb-2 d-block"></i>
            <div class="fs-2 fw-bold"><?= $total_kategori ?></div>
            <div class="opacity-75 small">Total Kategori</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card-stat" style="background:linear-gradient(135deg,#1a3a6b,#2a5ea8)">
            <i class="bi bi-person-circle fs-2 opacity-75 mb-2 d-block"></i>
            <div class="fs-2 fw-bold">1</div>
            <div class="opacity-75 small">Admin Aktif</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card-stat" style="background:linear-gradient(135deg,#6b3a1a,#a85e2a)">
            <i class="bi bi-eye fs-2 opacity-75 mb-2 d-block"></i>
            <div class="fs-2 fw-bold">Online</div>
            <div class="opacity-75 small">Status Website</div>
        </div>
    </div>
</div>

<!-- PRODUK TERBARU + AKSI CEPAT -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Produk Terbaru</h6>
                <a href="produk.php" class="btn btn-sm btn-outline-success rounded-pill">Lihat Semua</a>
            </div>
            <div class="card-body px-0 pb-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="border-0">
                            <tr>
                                <th class="ps-4">Produk</th>
                                <th>Kategori</th>
                                <th>Foto</th>
                                <th class="pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($p = $produk_terbaru->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold small"><?= htmlspecialchars($p['nama_produk']) ?></div>
                                    <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars(substr($p['deskripsi'],0,40)) ?>...</div>
                                </td>
                                <td><span class="badge-kat"><?= htmlspecialchars($p['nama_kategori']) ?></span></td>
                                <td>
                                    <?php if($p['foto']): ?>
                                    <span class="badge bg-success-subtle text-success rounded-pill small"><i class="bi bi-check"></i> Ada</span>
                                    <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning rounded-pill small"><i class="bi bi-x"></i> Belum</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4">
                                    <a href="edit_produk.php?id=<?= $p['id_produk'] ?>" class="btn btn-sm btn-outline-primary rounded-pill py-0 px-2">Edit</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 rounded-4 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                <h6 class="fw-bold mb-0">Aksi Cepat</h6>
            </div>
            <div class="card-body d-flex flex-column gap-3">
                <a href="tambah_produk.php" class="btn py-3 text-start rounded-3" style="background:rgba(45,90,39,0.08);color:#2d5a27">
                    <i class="bi bi-plus-circle me-2"></i><strong>Tambah Produk Baru</strong>
                </a>
                <a href="kategori.php" class="btn py-3 text-start rounded-3" style="background:rgba(26,107,90,0.08);color:#1a6b5a">
                    <i class="bi bi-tags me-2"></i><strong>Kelola Kategori</strong>
                </a>
                <a href="kontak.php" class="btn py-3 text-start rounded-3" style="background:rgba(26,58,107,0.08);color:#1a3a6b">
                    <i class="bi bi-telephone me-2"></i><strong>Edit Info Kontak</strong>
                </a>
                <a href="../index.php" target="_blank" class="btn py-3 text-start rounded-3" style="background:rgba(107,58,26,0.08);color:#6b3a1a">
                    <i class="bi bi-globe me-2"></i><strong>Lihat Website</strong>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer_admin.php'; $db->close(); ?>
