<?php
$page_title = 'Manajemen Kategori';
require_once 'header_admin.php';
$db = getDB();

$error = $success = '';

// HAPUS
if(isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $cek = $db->query("SELECT COUNT(*) as c FROM produk WHERE id_kategori = $id")->fetch_assoc();
    if($cek['c'] > 0) {
        $error = 'Kategori tidak bisa dihapus karena masih memiliki produk!';
    } else {
        $db->query("DELETE FROM kategori WHERE id_kategori = $id");
        $success = 'Kategori berhasil dihapus!';
    }
}

// TAMBAH / EDIT
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_kategori'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $edit_id = (int)($_POST['edit_id'] ?? 0);

    if(!$nama) {
        $error = 'Nama kategori wajib diisi!';
    } else {
        if($edit_id > 0) {
            $stmt = $db->prepare("UPDATE kategori SET nama_kategori=?, deskripsi=? WHERE id_kategori=?");
            $stmt->bind_param("ssi", $nama, $deskripsi, $edit_id);
            $stmt->execute();
            $success = 'Kategori berhasil diperbarui!';
        } else {
            $stmt = $db->prepare("INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?,?)");
            $stmt->bind_param("ss", $nama, $deskripsi);
            $stmt->execute();
            $success = 'Kategori berhasil ditambahkan!';
        }
    }
}

$edit_data = null;
if(isset($_GET['edit'])) {
    $edit_data = $db->query("SELECT * FROM kategori WHERE id_kategori = ".(int)$_GET['edit'])->fetch_assoc();
}

$kategori_list = $db->query("SELECT k.*, COUNT(p.id_produk) as jml_produk FROM kategori k LEFT JOIN produk p ON k.id_kategori=p.id_kategori GROUP BY k.id_kategori ORDER BY k.nama_kategori");
?>

<?php if($error): ?><div class="alert alert-danger rounded-3"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if($success): ?><div class="alert alert-success rounded-3"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?></div><?php endif; ?>

<div class="row g-4">
    <!-- FORM -->
    <div class="col-lg-4">
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                <h6 class="fw-bold mb-0"><?= $edit_data ? 'Edit Kategori' : 'Tambah Kategori' ?></h6>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <?php if($edit_data): ?>
                    <input type="hidden" name="edit_id" value="<?= $edit_data['id_kategori'] ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" class="form-control rounded-3" placeholder="Contoh: Bordir Baju" value="<?= htmlspecialchars($edit_data['nama_kategori'] ?? '') ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control rounded-3" rows="3" placeholder="Deskripsi kategori..."><?= htmlspecialchars($edit_data['deskripsi'] ?? '') ?></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn rounded-pill px-4 flex-grow-1 fw-semibold" style="background:#2d5a27;color:white">
                            <i class="bi bi-check-lg me-1"></i><?= $edit_data ? 'Update' : 'Simpan' ?>
                        </button>
                        <?php if($edit_data): ?>
                        <a href="kategori.php" class="btn btn-outline-secondary rounded-pill px-3">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- TABEL -->
    <div class="col-lg-8">
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                <h6 class="fw-bold mb-0">Daftar Kategori</h6>
            </div>
            <div class="card-body px-0 pb-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead><tr>
                            <th class="ps-4">Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Jml Produk</th>
                            <th class="pe-4 text-center">Aksi</th>
                        </tr></thead>
                        <tbody>
                            <?php while($k = $kategori_list->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4 fw-semibold"><?= htmlspecialchars($k['nama_kategori']) ?></td>
                                <td><div class="text-muted small" style="max-width:200px"><?= htmlspecialchars(substr($k['deskripsi'],0,50)) ?>...</div></td>
                                <td class="text-center"><span class="badge-kat"><?= $k['jml_produk'] ?> produk</span></td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="kategori.php?edit=<?= $k['id_kategori'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3"><i class="bi bi-pencil"></i></a>
                                        <a href="kategori.php?hapus=<?= $k['id_kategori'] ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Yakin hapus kategori ini?')"><i class="bi bi-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer_admin.php'; $db->close(); ?>
