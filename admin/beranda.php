<?php
$page_title = 'Edit Beranda';
require_once 'header_admin.php';
$db = getDB();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hero_badge   = trim($_POST['hero_badge'] ?? '');
    $hero_judul_1 = trim($_POST['hero_judul_1'] ?? '');
    $hero_judul_2 = trim($_POST['hero_judul_2'] ?? '');
    $hero_desk    = trim($_POST['hero_deskripsi'] ?? '');
    $hero_bg_url  = trim($_POST['hero_bg_url'] ?? '');
    $fitur1_icon  = trim($_POST['fitur1_icon'] ?? 'bi-patch-check-fill');
    $fitur1_judul = trim($_POST['fitur1_judul'] ?? '');
    $fitur1_desk  = trim($_POST['fitur1_desk'] ?? '');
    $fitur2_icon  = trim($_POST['fitur2_icon'] ?? 'bi-clock-fill');
    $fitur2_judul = trim($_POST['fitur2_judul'] ?? '');
    $fitur2_desk  = trim($_POST['fitur2_desk'] ?? '');
    $fitur3_icon  = trim($_POST['fitur3_icon'] ?? 'bi-shield-fill-check');
    $fitur3_judul = trim($_POST['fitur3_judul'] ?? '');
    $fitur3_desk  = trim($_POST['fitur3_desk'] ?? '');
    $fitur4_icon  = trim($_POST['fitur4_icon'] ?? 'bi-people-fill');
    $fitur4_judul = trim($_POST['fitur4_judul'] ?? '');
    $fitur4_desk  = trim($_POST['fitur4_desk'] ?? '');

    // Ambil data lama
    $lama = $db->query("SELECT hero_bg_file, keunggulan_foto FROM beranda_konten LIMIT 1")->fetch_assoc();
    $hero_bg_file   = $lama['hero_bg_file'] ?? '';
    $keunggulan_foto = $lama['keunggulan_foto'] ?? '';

    // Upload background hero
    if (isset($_FILES['hero_bg_file']) && $_FILES['hero_bg_file']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['hero_bg_file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $error = 'Format foto background harus JPG, PNG, atau WEBP!';
        } elseif ($_FILES['hero_bg_file']['size'] > 10 * 1024 * 1024) {
            $error = 'Ukuran foto background maksimal 10MB!';
        } else {
            if ($hero_bg_file && file_exists('../public/uploads/' . $hero_bg_file)) unlink('../public/uploads/' . $hero_bg_file);
            $hero_bg_file = 'hero_bg_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['hero_bg_file']['tmp_name'], '../public/uploads/' . $hero_bg_file);
            $hero_bg_url = ''; // ← INI SUDAH ADA, tapi pastikan ikut ke query UPDATE
        }
    }

    // Upload foto keunggulan
    if (isset($_FILES['keunggulan_foto']) && $_FILES['keunggulan_foto']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext2 = strtolower(pathinfo($_FILES['keunggulan_foto']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext2, $allowed)) {
            $error = 'Format foto keunggulan harus JPG, PNG, atau WEBP!';
        } elseif ($_FILES['keunggulan_foto']['size'] > 10 * 1024 * 1024) {
            $error = 'Ukuran foto keunggulan maksimal 10MB!';
        } else {
            if ($keunggulan_foto && file_exists('../public/uploads/' . $keunggulan_foto)) unlink('../public/uploads/' . $keunggulan_foto);
            $keunggulan_foto = 'keunggulan_' . time() . '.' . $ext2;
            move_uploaded_file($_FILES['keunggulan_foto']['tmp_name'], '../public/uploads/' . $keunggulan_foto);
        }
    }

    if (!$error) {
        if (!$hero_judul_1 || !$hero_judul_2) {
            $error = 'Judul hero wajib diisi!';
        } else {
            $cek = $db->query("SELECT COUNT(*) as c FROM beranda_konten")->fetch_assoc();
            $sql_fields = "hero_badge=?, hero_judul_1=?, hero_judul_2=?, hero_deskripsi=?,
                hero_bg_url=?, hero_bg_file=?,
           
                fitur1_icon=?, fitur1_judul=?, fitur1_desk=?,
                fitur2_icon=?, fitur2_judul=?, fitur2_desk=?,
                fitur3_icon=?, fitur3_judul=?, fitur3_desk=?,
                fitur4_icon=?, fitur4_judul=?, fitur4_desk=?,
                keunggulan_foto=?";
            $params = [
                $hero_badge,
                $hero_judul_1,
                $hero_judul_2,
                $hero_desk,
                $hero_bg_url,
                $hero_bg_file,

                $fitur1_icon,
                $fitur1_judul,
                $fitur1_desk,
                $fitur2_icon,
                $fitur2_judul,
                $fitur2_desk,
                $fitur3_icon,
                $fitur3_judul,
                $fitur3_desk,
                $fitur4_icon,
                $fitur4_judul,
                $fitur4_desk,
                $keunggulan_foto
            ];
            $types = str_repeat('s', count($params));

            if ($cek['c'] > 0) {
                $stmt = $db->prepare("UPDATE beranda_konten SET $sql_fields");
            } else {
                $cols = "hero_badge, hero_judul_1, hero_judul_2, hero_deskripsi,
                    hero_bg_url, hero_bg_file, cta_judul, cta_deskripsi,
                    fitur1_icon, fitur1_judul, fitur1_desk,
                    fitur2_icon, fitur2_judul, fitur2_desk,
                    fitur3_icon, fitur3_judul, fitur3_desk,
                    fitur4_icon, fitur4_judul, fitur4_desk, keunggulan_foto";
                $placeholders = implode(',', array_fill(0, count($params), '?'));
                $stmt = $db->prepare("INSERT INTO beranda_konten ($cols) VALUES ($placeholders)");
            }
            $stmt->bind_param($types, ...$params);
            if ($stmt->execute()) {
                $success = 'Konten beranda berhasil diperbarui!';
                $b = $db->query("SELECT * FROM beranda_konten LIMIT 1")->fetch_assoc();
            } else {
                $error = 'Gagal menyimpan: ' . $db->error;
            }
        }
    }
}

$b = $b ?? $db->query("SELECT * FROM beranda_konten LIMIT 1")->fetch_assoc();

$bg_aktif = '';
if (!empty($b['hero_bg_file']) && file_exists('../public/uploads/' . $b['hero_bg_file']))
    $bg_aktif = '../public/uploads/' . $b['hero_bg_file'];
elseif (!empty($b['hero_bg_url']))
    $bg_aktif = $b['hero_bg_url'];

$foto_keunggulan_aktif = '';
if (!empty($b['keunggulan_foto']) && file_exists('../public/uploads/' . $b['keunggulan_foto']))
    $foto_keunggulan_aktif = '../public/uploads/' . $b['keunggulan_foto'];

$icon_options = [
    'bi-patch-check-fill'    => 'Centang Bintang',
    'bi-clock-fill'          => 'Jam',
    'bi-shield-fill-check'   => 'Perisai',
    'bi-people-fill'         => 'Orang / Tim',
    'bi-star-fill'           => 'Bintang',
    'bi-trophy-fill'         => 'Trofi',
    'bi-heart-fill'          => 'Hati',
    'bi-lightning-fill'      => 'Kilat / Cepat',
    'bi-tools'               => 'Alat / Teknis',
    'bi-truck'               => 'Pengiriman',
    'bi-chat-fill'           => 'Konsultasi',
    'bi-gem'                 => 'Kualitas Premium',
    'bi-award-fill'          => 'Penghargaan',
    'bi-hand-thumbs-up-fill' => 'Jempol / Bagus',
];
?>

<?php if ($success): ?>
    <div class="alert alert-success rounded-3 d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i> <?= $success ?>
        <a href="../index.php" target="_blank" class="ms-auto btn btn-sm btn-outline-success rounded-pill">
            <i class="bi bi-eye me-1"></i>Lihat Beranda
        </a>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger rounded-3"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="mb-4">
    <h5 class="fw-bold mb-1">Edit Konten Beranda</h5>
    <p class="text-muted small mb-0">Ubah teks, background, keunggulan, dan CTA halaman utama website</p>
</div>

<form method="POST" enctype="multipart/form-data">
    <div class="row g-4">

        <!-- ===== HERO ===== -->
        <div class="col-12">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;background:rgba(45,90,39,0.1)">
                        <i class="bi bi-house-fill" style="color:#2d5a27;font-size:.85rem"></i>
                    </div>
                    <h6 class="fw-bold mb-0">Hero Section (Banner Utama)</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Badge / Label Kecil</label>
                            <input type="text" name="hero_badge" class="form-control rounded-3"
                                value="<?= htmlspecialchars($b['hero_badge'] ?? '') ?>">
                            <div class="form-text">Teks label di atas judul utama</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Background Hero</label>
                            <div class="d-flex gap-2 mb-2">
                                <button type="button" class="btn btn-sm rounded-pill px-3" id="btnTabFile"
                                    style="background:#2d5a27;color:white" onclick="switchTab('file')">
                                    <i class="bi bi-upload me-1"></i>Upload File
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="btnTabUrl"
                                    onclick="switchTab('url')">
                                    <i class="bi bi-link-45deg me-1"></i>Pakai URL
                                </button>
                            </div>
                            <div id="tab-file">
                                <input type="file" name="hero_bg_file" class="form-control rounded-3"
                                    accept="image/jpeg,image/png,image/webp" onchange="previewUpload(event,'bgPreviewImg','bgPreviewWrap')">
                                <div class="form-text">JPG/PNG/WEBP, maks 5MB.
                                    <?php if (!empty($b['hero_bg_file'])): ?>
                                        <span class="text-success"><i class="bi bi-check-circle"></i> File aktif tersimpan</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div id="tab-url" style="display:none">
                                <input type="url" name="hero_bg_url" class="form-control rounded-3"
                                    placeholder="https://..."
                                    value="<?= htmlspecialchars($b['hero_bg_url'] ?? '') ?>"
                                    oninput="previewUrl(this.value,'bgPreviewImg','bgPreviewWrap')">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Judul Baris 1 <span class="text-danger">*</span></label>
                            <input type="text" name="hero_judul_1" class="form-control rounded-3"
                                value="<?= htmlspecialchars($b['hero_judul_1'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Judul Baris 2 <small class="text-muted">(italic hijau muda)</small> <span class="text-danger">*</span></label>
                            <input type="text" name="hero_judul_2" class="form-control rounded-3"
                                value="<?= htmlspecialchars($b['hero_judul_2'] ?? '') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Deskripsi Hero</label>
                            <textarea name="hero_deskripsi" class="form-control rounded-3" rows="2"><?= htmlspecialchars($b['hero_deskripsi'] ?? '') ?></textarea>
                        </div>
                        <!-- Preview bg -->
                        <div class="col-12" id="bgPreviewWrap" style="<?= $bg_aktif ? '' : 'display:none' ?>">
                            <label class="form-label fw-semibold small">Preview Background</label>
                            <div class="rounded-3 d-flex align-items-center justify-content-center text-white"
                                style="height:90px;background:linear-gradient(135deg,#2d5a27,#4a8c3f);position:relative;overflow:hidden">
                                <img id="bgPreviewImg" src="<?= htmlspecialchars($bg_aktif) ?>"
                                    style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.5"
                                    onerror="this.style.display='none'">
                                <span style="position:relative;z-index:2;font-size:.8rem;opacity:.8">Preview Background Hero</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== KEUNGGULAN ===== -->
        <div class="col-12">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;background:rgba(45,90,39,0.1)">
                        <i class="bi bi-star-fill" style="color:#2d5a27;font-size:.85rem"></i>
                    </div>
                    <h6 class="fw-bold mb-0">Keunggulan Kami — "Mengapa Memilih Yogi Bordir?"</h6>
                </div>
                <div class="card-body p-4">

                    <!-- Foto samping -->
                    <div class="row g-3 mb-4 pb-3 border-bottom">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Foto Samping Kanan</label>
                            <input type="file" name="keunggulan_foto" class="form-control rounded-3"
                                accept="image/jpeg,image/png,image/webp"
                                onchange="previewUpload(event,'fotoKeunggulanImg','fotoKeunggulanWrap')">
                            <div class="form-text">JPG/PNG/WEBP, maks 5MB. Foto ini muncul di sebelah kanan daftar keunggulan.</div>
                        </div>
                        <div class="col-md-6" id="fotoKeunggulanWrap" style="<?= $foto_keunggulan_aktif ? '' : 'display:none' ?>">
                            <label class="form-label fw-semibold small">Preview Foto Aktif</label>
                            <img id="fotoKeunggulanImg" src="<?= htmlspecialchars($foto_keunggulan_aktif) ?>"
                                class="img-fluid rounded-3 w-100" style="height:140px;object-fit:cover"
                                onerror="this.parentElement.style.display='none'">
                        </div>
                    </div>

                    <!-- 4 Fitur -->
                    <div class="row g-4">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 border h-100">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                            id="iconPreview<?= $i ?>"
                                            style="width:40px;height:40px;background:rgba(45,90,39,0.1)">
                                            <i class="bi <?= htmlspecialchars($b["fitur{$i}_icon"] ?? 'bi-patch-check-fill') ?> fs-5" style="color:#2d5a27"></i>
                                        </div>
                                        <span class="fw-semibold small text-muted">Fitur <?= $i ?></span>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold mb-1">Icon</label>
                                        <select name="fitur<?= $i ?>_icon" class="form-select form-select-sm rounded-3"
                                            onchange="previewIcon(this,<?= $i ?>)">
                                            <?php foreach ($icon_options as $val => $lbl): ?>
                                                <option value="<?= $val ?>" <?= ($b["fitur{$i}_icon"] ?? '') == $val ? 'selected' : '' ?>><?= $lbl ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold mb-1">Judul</label>
                                        <input type="text" name="fitur<?= $i ?>_judul" class="form-control form-control-sm rounded-3"
                                            value="<?= htmlspecialchars($b["fitur{$i}_judul"] ?? '') ?>">
                                    </div>
                                    <div>
                                        <label class="form-label small fw-semibold mb-1">Deskripsi</label>
                                        <textarea name="fitur<?= $i ?>_desk" class="form-control form-control-sm rounded-3" rows="2"><?= htmlspecialchars($b["fitur{$i}_desk"] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>


        <!-- SUBMIT -->
        <div class="col-12 pb-3">
            <div class="d-flex gap-3">
                <button type="submit" class="btn rounded-pill px-5 fw-semibold py-2" style="background:#2d5a27;color:white">
                    <i class="bi bi-save me-2"></i>Simpan Semua Perubahan
                </button>
                <a href="../index.php" target="_blank" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-box-arrow-up-right me-2"></i>Lihat Beranda
                </a>
            </div>
        </div>

    </div>
</form>

<script>
    function switchTab(tab) {
        const isFile = tab === 'file';
        document.getElementById('tab-file').style.display = isFile ? '' : 'none';
        document.getElementById('tab-url').style.display = isFile ? 'none' : '';
        document.getElementById('btnTabFile').style.cssText = isFile ? 'background:#2d5a27;color:white' : '';
        document.getElementById('btnTabFile').className = isFile ? 'btn btn-sm rounded-pill px-3' : 'btn btn-sm btn-outline-secondary rounded-pill px-3';
        document.getElementById('btnTabUrl').style.cssText = !isFile ? 'background:#2d5a27;color:white' : '';
        document.getElementById('btnTabUrl').className = !isFile ? 'btn btn-sm rounded-pill px-3' : 'btn btn-sm btn-outline-secondary rounded-pill px-3';
    }

    function previewUpload(e, imgId, wrapId) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = ev => {
            const wrap = document.getElementById(wrapId);
            const img = document.getElementById(imgId);
            if (wrap) wrap.style.display = '';
            if (img) {
                img.src = ev.target.result;
                img.style.display = '';
            }
        };
        reader.readAsDataURL(file);
    }

    function previewUrl(url, imgId, wrapId) {
        const wrap = document.getElementById(wrapId);
        const img = document.getElementById(imgId);
        if (wrap && url) wrap.style.display = '';
        if (img && url) {
            img.src = url;
            img.style.display = '';
        }
    }

    function previewIcon(sel, num) {
        const box = document.getElementById('iconPreview' + num);
        if (box) box.innerHTML = `<i class="bi ${sel.value} fs-5" style="color:#2d5a27"></i>`;
    }
</script>

<?php require_once 'footer_admin.php';
$db->close(); ?>