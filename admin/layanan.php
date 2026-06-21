<?php
$page_title = 'Kelola Layanan';
require_once 'header_admin.php';
$db = getDB();

// HAPUS
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = $db->prepare("DELETE FROM layanan WHERE id_layanan = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header('Location: layanan.php?msg=hapus');
    exit;
}

// SIMPAN HEADER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_header'])) {
    $hj = trim($_POST['header_judul'] ?? '');
    $hd = trim($_POST['header_deskripsi'] ?? '');
    $cek = $db->query("SELECT COUNT(*) as c FROM layanan_konten")->fetch_assoc();
    if ($cek['c'] > 0) {
        $st = $db->prepare("UPDATE layanan_konten SET header_judul=?, header_deskripsi=?");
    } else {
        $st = $db->prepare("INSERT INTO layanan_konten (header_judul, header_deskripsi) VALUES (?,?)");
    }
    $st->bind_param("ss", $hj, $hd);
    $st->execute();
    header('Location: layanan.php?msg=simpan');
    exit;
}

// SIMPAN LAYANAN (tambah atau edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['simpan_header'])) {
    $id     = (int)($_POST['id_layanan'] ?? 0);
    $judul  = trim($_POST['judul'] ?? '');
    $desk   = trim($_POST['deskripsi'] ?? '');
    $ikon   = trim($_POST['ikon'] ?? 'bi-scissors');
    $urutan = (int)($_POST['urutan'] ?? 0);

    if ($judul) {
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE layanan SET judul=?, deskripsi=?, ikon=?, urutan=? WHERE id_layanan=?");
            $stmt->bind_param("sssii", $judul, $desk, $ikon, $urutan, $id);
        } else {
            $stmt = $db->prepare("INSERT INTO layanan (judul, deskripsi, ikon, urutan) VALUES (?,?,?,?)");
            $stmt->bind_param("sssi", $judul, $desk, $ikon, $urutan);
        }
        $stmt->execute();
        header('Location: layanan.php?msg=simpan');
        exit;
    }
}

$msg = $_GET['msg'] ?? '';
$lk = $db->query("SELECT * FROM layanan_konten LIMIT 1")->fetch_assoc();

$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM layanan WHERE id_layanan = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
}

$layanan_list = $db->query("SELECT * FROM layanan ORDER BY urutan ASC");
?>

<?php if ($msg): ?>
<div class="alert alert-success alert-dismissible fade show rounded-3">
    <i class="bi bi-check-circle me-2"></i>
    <?= $msg == 'hapus' ? 'Layanan berhasil dihapus!' : 'Layanan berhasil disimpan!' ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Kelola Layanan</h5>
        <p class="text-muted small mb-0">Atur layanan yang ditampilkan di halaman Layanan website</p>
    </div>
</div>

<!-- FORM EDIT HEADER -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex align-items-center gap-2">
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;background:rgba(45,90,39,0.1)">
            <i class="bi bi-type-h1" style="color:#2d5a27;font-size:.85rem"></i>
        </div>
        <h6 class="fw-bold mb-0">Header Halaman Layanan</h6>
    </div>
    <div class="card-body p-4">
        <form method="POST">
            <input type="hidden" name="simpan_header" value="1">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Judul Header</label>
                    <input type="text" name="header_judul" class="form-control rounded-3"
                        value="<?= htmlspecialchars($lk['header_judul'] ?? 'Layanan Kami') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Deskripsi Header</label>
                    <input type="text" name="header_deskripsi" class="form-control rounded-3"
                        value="<?= htmlspecialchars($lk['header_deskripsi'] ?? '') ?>">
                </div>
            </div>
            <button type="submit" class="btn rounded-pill px-4 fw-semibold mt-3" style="background:#2d5a27;color:white">
                <i class="bi bi-save me-2"></i>Simpan Header
            </button>
        </form>
    </div>
</div>

<div class="row g-4">
    <!-- FORM TAMBAH/EDIT LAYANAN -->
    <div class="col-lg-4">
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">
                    <?= $edit_data ? 'Edit Layanan' : 'Tambah Layanan Baru' ?>
                </h6>
                <form method="POST">
                    <?php if ($edit_data): ?>
                    <input type="hidden" name="id_layanan" value="<?= $edit_data['id_layanan'] ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Judul Layanan *</label>
                        <input type="text" name="judul" class="form-control rounded-3"
                            value="<?= htmlspecialchars($edit_data['judul'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control rounded-3" rows="4"
                            placeholder="Jelaskan layanan ini..."><?= htmlspecialchars($edit_data['deskripsi'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ikon Bootstrap Icons</label>
                        <input type="text" name="ikon" class="form-control rounded-3"
                            value="<?= htmlspecialchars($edit_data['ikon'] ?? 'bi-scissors') ?>"
                            placeholder="Contoh: bi-scissors">
                        <div class="form-text">
                            Lihat ikon di <a href="https://icons.getbootstrap.com" target="_blank">icons.getbootstrap.com</a>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Urutan Tampil</label>
                        <input type="number" name="urutan" class="form-control rounded-3"
                            value="<?= $edit_data['urutan'] ?? 0 ?>" min="0">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn rounded-pill px-4 fw-semibold flex-fill"
                            style="background:#2d5a27;color:white">
                            <i class="bi bi-check-lg me-1"></i>
                            <?= $edit_data ? 'Simpan' : 'Tambah' ?>
                        </button>
                        <?php if ($edit_data): ?>
                        <a href="layanan.php" class="btn btn-outline-secondary rounded-pill px-3">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- DAFTAR LAYANAN -->
    <div class="col-lg-8">
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-body px-0 pb-0">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4" width="40">No</th>
                            <th width="50">Ikon</th>
                            <th>Judul Layanan</th>
                            <th width="60" class="text-center">Urutan</th>
                            <th class="pe-4 text-center" width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($l = $layanan_list->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4 text-muted"><?= $no++ ?></td>
                            <td>
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                    style="width:36px;height:36px;background:rgba(45,90,39,0.1)">
                                    <i class="bi <?= htmlspecialchars($l['ikon']) ?>" style="color:#2d5a27"></i>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold small"><?= htmlspecialchars($l['judul']) ?></div>
                                <div class="text-muted" style="font-size:12px">
                                    <?= htmlspecialchars(substr($l['deskripsi'], 0, 60)) ?>...
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark"><?= $l['urutan'] ?></span>
                            </td>
                            <td class="pe-4 text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="layanan.php?edit=<?= $l['id_layanan'] ?>"
                                        class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="layanan.php?hapus=<?= $l['id_layanan'] ?>"
                                        class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                        onclick="return confirm('Yakin hapus layanan ini?')">
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
</div>

<?php require_once 'footer_admin.php'; $db->close(); ?>