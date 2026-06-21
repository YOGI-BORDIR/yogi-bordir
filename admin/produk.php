<?php
$page_title = 'Manajemen Produk';
require_once 'header_admin.php';
$db = getDB();

// HAPUS
if(isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $produk = $db->query("SELECT foto FROM produk WHERE id_produk = $id")->fetch_assoc();
    if($produk && $produk['foto'] && file_exists('../public/uploads/'.$produk['foto'])) {
        unlink('../public/uploads/'.$produk['foto']);
    }
    $db->query("DELETE FROM produk WHERE id_produk = $id");
    header('Location: produk.php?msg=hapus');
    exit;
}

$msg = $_GET['msg'] ?? '';
$produk_list = $db->query("SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON p.id_kategori=k.id_kategori ORDER BY p.id_produk DESC");
?>

<?php if($msg): ?>
<div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
    <i class="bi bi-check-circle me-2"></i>
    <?= $msg=='tambah'?'Produk berhasil ditambahkan!' : ($msg=='edit'?'Produk berhasil diperbarui!' : 'Produk berhasil dihapus!') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Daftar Produk</h5>
        <p class="text-muted small mb-0">Kelola semua produk bordir yang ditampilkan di website</p>
    </div>
    <a href="tambah_produk.php" class="btn rounded-pill px-4" style="background:#2d5a27;color:white">
        <i class="bi bi-plus-lg me-2"></i>Tambah Produk
    </a>
</div>

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body px-0 pb-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-4" width="50">No</th>
                        <th width="80">Foto</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Deskripsi</th>
                        <th class="pe-4 text-center" width="140">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; while($p = $produk_list->fetch_assoc()): ?>
                    <tr>
                        <td class="ps-4 text-muted"><?= $no++ ?></td>
                        <td>
                            <?php if($p['foto'] && file_exists('../public/uploads/'.$p['foto'])): ?>
                            <img src="../public/uploads/<?= htmlspecialchars($p['foto']) ?>" alt="" class="rounded-2" style="width:56px;height:56px;object-fit:cover">
                            <?php else: ?>
                            <div class="rounded-2 d-flex align-items-center justify-content-center bg-light" style="width:56px;height:56px">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td><div class="fw-semibold"><?= htmlspecialchars($p['nama_produk']) ?></div></td>
                        <td><span class="badge-kat"><?= htmlspecialchars($p['nama_kategori']) ?></span></td>
                        <td><div class="text-muted small" style="max-width:200px"><?= htmlspecialchars(substr($p['deskripsi'],0,60)) ?>...</div></td>
                        <td class="pe-4 text-center">
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="edit_produk.php?id=<?= $p['id_produk'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="produk.php?hapus=<?= $p['id_produk'] ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                   onclick="return confirm('Yakin hapus produk ini?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer_admin.php'; $db->close(); ?>
