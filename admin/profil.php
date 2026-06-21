<?php
$page_title = 'Edit Profil Usaha';
require_once 'header_admin.php';
$db = getDB();
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama         = trim($_POST['nama_usaha'] ?? '');
    $tahun        = trim($_POST['tahun_berdiri'] ?? '');
    $lokasi       = trim($_POST['lokasi'] ?? '');
    $spesialisasi = trim($_POST['spesialisasi'] ?? '');
    $pengiriman   = trim($_POST['pengiriman'] ?? '');
    $deskripsi1   = trim($_POST['deskripsi1'] ?? '');
    $deskripsi2   = trim($_POST['deskripsi2'] ?? '');
    $visi         = trim($_POST['visi'] ?? '');
    $header_judul = trim($_POST['header_judul'] ?? 'Profil Usaha');
    $header_desk  = trim($_POST['header_deskripsi'] ?? '');

    // Ambil file lama
    $lama = $db->query("SELECT header_bg_file FROM profil_usaha LIMIT 1")->fetch_assoc();
    $header_bg_file = $lama['header_bg_file'] ?? '';

    // Upload foto tentang kami
    if (isset($_FILES['header_bg_file']) && $_FILES['header_bg_file']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['header_bg_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['header_bg_file']['size'] <= 5 * 1024 * 1024) {
            if ($header_bg_file && file_exists('../public/uploads/' . $header_bg_file))
                unlink('../public/uploads/' . $header_bg_file);
            $header_bg_file = 'profil_foto_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['header_bg_file']['tmp_name'], '../public/uploads/' . $header_bg_file);
        }
    }

    // Simpan profil_usaha
    $cek = $db->query("SELECT COUNT(*) as c FROM profil_usaha")->fetch_assoc();
    if ($cek['c'] > 0) {
        $stmt = $db->prepare("UPDATE profil_usaha SET nama_usaha=?, tahun_berdiri=?, lokasi=?, spesialisasi=?, pengiriman=?, deskripsi1=?, deskripsi2=?, visi=?, header_judul=?, header_deskripsi=?, header_bg_file=? WHERE id_profil=1");
        $stmt->bind_param("sssssssssss", $nama, $tahun, $lokasi, $spesialisasi, $pengiriman, $deskripsi1, $deskripsi2, $visi, $header_judul, $header_desk, $header_bg_file);
    } else {
        $stmt = $db->prepare("INSERT INTO profil_usaha (nama_usaha, tahun_berdiri, lokasi, spesialisasi, pengiriman, deskripsi1, deskripsi2, visi, header_judul, header_deskripsi, header_bg_file) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssssssss", $nama, $tahun, $lokasi, $spesialisasi, $pengiriman, $deskripsi1, $deskripsi2, $visi, $header_judul, $header_desk, $header_bg_file);
    }
    $stmt->execute();

    // Simpan misi ke tabel misi
    $misi_input = trim($_POST['misi'] ?? '');
    if ($misi_input) {
        $db->query("DELETE FROM misi");
        $misi_arr = array_filter(array_map('trim', explode('|', $misi_input)));
        $urutan = 1;
        $stmt2 = $db->prepare("INSERT INTO misi (isi, urutan) VALUES (?, ?)");
        foreach ($misi_arr as $m) {
            $stmt2->bind_param("si", $m, $urutan);
            $stmt2->execute();
            $urutan++;
        }
    }

    $success = 'Profil usaha berhasil diperbarui!';
}

$profil = $db->query("SELECT * FROM profil_usaha LIMIT 1")->fetch_assoc();
$misi_rows = $db->query("SELECT isi FROM misi ORDER BY urutan ASC");
$misi_arr_display = [];
while ($m = $misi_rows->fetch_assoc()) $misi_arr_display[] = $m['isi'];
$misi_str = implode('|', $misi_arr_display);
?>

<?php if ($success): ?>
<div class="alert alert-success rounded-3"><i class="bi bi-check-circle me-2"></i><?= $success ?></div>
<?php endif; ?>

<div class="mb-4">
    <h5 class="fw-bold mb-1">Edit Profil Usaha</h5>
    <p class="text-muted small mb-0">Informasi ini akan ditampilkan di halaman Profil Usaha website</p>
</div>

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" enctype="multipart/form-data">

            <h6 class="fw-bold mb-3 pb-2 border-bottom" style="color:#2d5a27">Header Halaman Profil</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Judul Header</label>
                    <input type="text" name="header_judul" class="form-control rounded-3"
                        value="<?= htmlspecialchars($profil['header_judul'] ?? 'Profil Usaha') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Deskripsi Header</label>
                    <input type="text" name="header_deskripsi" class="form-control rounded-3"
                        value="<?= htmlspecialchars($profil['header_deskripsi'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Foto Tentang Kami <small class="text-muted">(muncul di section kiri bawah header)</small></label>
                    <input type="file" name="header_bg_file" class="form-control rounded-3"
                        accept="image/jpeg,image/png,image/webp">
                    <div class="form-text">JPG/PNG/WEBP, maks 5MB.
                        <?php if (!empty($profil['header_bg_file'])): ?>
                        <span class="text-success"><i class="bi bi-check-circle"></i> Foto aktif tersimpan</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($profil['header_bg_file']) && file_exists('../public/uploads/' . $profil['header_bg_file'])): ?>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Preview Foto Aktif</label>
                    <img src="../public/uploads/<?= htmlspecialchars($profil['header_bg_file']) ?>"
                        class="img-fluid rounded-3 w-100" style="height:120px;object-fit:cover">
                </div>
                <?php endif; ?>
            </div>

            <h6 class="fw-bold mb-3 pb-2 border-bottom" style="color:#2d5a27">Informasi Dasar</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Nama Usaha</label>
                    <input type="text" name="nama_usaha" class="form-control rounded-3" value="<?= htmlspecialchars($profil['nama_usaha'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Tahun Berdiri</label>
                    <input type="text" name="tahun_berdiri" class="form-control rounded-3" value="<?= htmlspecialchars($profil['tahun_berdiri'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Lokasi</label>
                    <input type="text" name="lokasi" class="form-control rounded-3" value="<?= htmlspecialchars($profil['lokasi'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Spesialisasi</label>
                    <input type="text" name="spesialisasi" class="form-control rounded-3" value="<?= htmlspecialchars($profil['spesialisasi'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Jangkauan Pengiriman</label>
                    <input type="text" name="pengiriman" class="form-control rounded-3" value="<?= htmlspecialchars($profil['pengiriman'] ?? '') ?>">
                </div>
            </div>

            <h6 class="fw-bold mb-3 pb-2 border-bottom" style="color:#2d5a27">Deskripsi Usaha</h6>
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <label class="form-label fw-semibold small">Paragraf 1</label>
                    <textarea name="deskripsi1" class="form-control rounded-3" rows="3"><?= htmlspecialchars($profil['deskripsi1'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold small">Paragraf 2</label>
                    <textarea name="deskripsi2" class="form-control rounded-3" rows="3"><?= htmlspecialchars($profil['deskripsi2'] ?? '') ?></textarea>
                </div>
            </div>

            <h6 class="fw-bold mb-3 pb-2 border-bottom" style="color:#2d5a27">Visi & Misi</h6>
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <label class="form-label fw-semibold small">Visi</label>
                    <textarea name="visi" class="form-control rounded-3" rows="3"><?= htmlspecialchars($profil['visi'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold small">Misi</label>
                    <textarea name="misi" class="form-control rounded-3" rows="5"><?= htmlspecialchars($misi_str) ?></textarea>
                    <div class="form-text"><i class="bi bi-info-circle me-1"></i>Pisahkan setiap poin dengan <strong>|</strong> — Contoh: Misi 1|Misi 2|Misi 3</div>
                </div>
            </div>

            <button type="submit" class="btn rounded-pill px-5 fw-semibold" style="background:#2d5a27;color:white">
                <i class="bi bi-save me-2"></i>Simpan Perubahan
            </button>
        </form>
    </div>
</div>

<?php require_once 'footer_admin.php'; $db->close(); ?>