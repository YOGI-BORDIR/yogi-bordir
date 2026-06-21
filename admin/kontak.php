<?php
$page_title = 'Edit Kontak Usaha';
require_once 'header_admin.php';
$db = getDB();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wa          = trim($_POST['no_whatsapp'] ?? '');
    $ig          = trim($_POST['link_instagram'] ?? '');
    $fb          = trim($_POST['link_facebook'] ?? '');
    $alamat      = trim($_POST['alamat'] ?? '');
    $maps        = trim($_POST['link_maps'] ?? '');
    $narasi      = trim($_POST['narasi_kontak'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $jam         = trim($_POST['jam_operasional'] ?? '');
    $mari_judul  = trim($_POST['mari_terhubung_judul'] ?? 'Mari Terhubung');

    // Auto-extract src dari iframe jika user paste full iframe tag
    $embed_raw = trim($_POST['embed_maps'] ?? '');
    if (stripos($embed_raw, '<iframe') !== false) {
        preg_match('/src=["\']([^"\']+)["\']/', $embed_raw, $match);
        $embed = $match[1] ?? '';
    } else {
        $embed = $embed_raw;
    }

    $cek = $db->query("SELECT COUNT(*) as c FROM kontak_usaha")->fetch_assoc();
    if ($cek['c'] > 0) {
        // UPDATE — sudah ada data
        $stmt = $db->prepare("UPDATE kontak_usaha SET
            no_whatsapp=?, link_instagram=?, link_facebook=?,
            alamat=?, link_maps=?, embed_maps=?,
            narasi_kontak=?, email=?, jam_operasional=?,
            mari_terhubung_judul=?
            WHERE id_kontak=1");
        $stmt->bind_param("ssssssssss",
            $wa, $ig, $fb, $alamat, $maps, $embed,
            $narasi, $email, $jam, $mari_judul
        );
    } else {
        // INSERT — belum ada data sama sekali
        $stmt = $db->prepare("INSERT INTO kontak_usaha
            (no_whatsapp, link_instagram, link_facebook, alamat, link_maps, embed_maps,
            narasi_kontak, email, jam_operasional, mari_terhubung_judul)
            VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssssss",
            $wa, $ig, $fb, $alamat, $maps, $embed,
            $narasi, $email, $jam, $mari_judul
        );
    }

    if ($stmt->execute()) {
        $success = 'Informasi kontak berhasil diperbarui!';
    } else {
        $error = 'Gagal menyimpan: ' . $db->error;
    }
}

$k = $db->query("SELECT * FROM kontak_usaha WHERE id_kontak=1 LIMIT 1")->fetch_assoc();
?>

<?php if ($success): ?>
<div class="alert alert-success rounded-3"><i class="bi bi-check-circle me-2"></i><?= $success ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger rounded-3"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="mb-4">
    <h5 class="fw-bold mb-1">Edit Kontak Usaha</h5>
    <p class="text-muted small mb-0">Informasi ini ditampilkan di halaman kontak dan footer website</p>
</div>

<form method="POST">
    <div class="row g-4">

        <!-- SOSMED & KONTAK -->
        <div class="col-lg-6">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                        style="width:32px;height:32px;background:rgba(45,90,39,0.1)">
                        <i class="bi bi-share-fill" style="color:#2d5a27;font-size:.85rem"></i>
                    </div>
                    <h6 class="fw-bold mb-0">Sosial Media & Kontak</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nomor WhatsApp</label>
                        <div class="input-group">
                            <span class="input-group-text rounded-start-3"><i class="bi bi-whatsapp text-success"></i></span>
                            <input type="text" name="no_whatsapp" class="form-control rounded-end-3"
                                placeholder="6281234567890"
                                value="<?= htmlspecialchars($k['no_whatsapp'] ?? '') ?>">
                        </div>
                        <div class="form-text">Format internasional tanpa tanda +</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Email</label>
                        <div class="input-group">
                            <span class="input-group-text rounded-start-3"><i class="bi bi-envelope"></i></span>
                            <input type="text" name="email" class="form-control rounded-end-3"
                                placeholder="contoh@email.com"
                                value="<?= htmlspecialchars($k['email'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Link Instagram</label>
                        <div class="input-group">
                            <span class="input-group-text rounded-start-3"><i class="bi bi-instagram" style="color:#e1306c"></i></span>
                            <input type="url" name="link_instagram" class="form-control rounded-end-3"
                                placeholder="https://instagram.com/namaakun"
                                value="<?= htmlspecialchars($k['link_instagram'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Link Facebook</label>
                        <div class="input-group">
                            <span class="input-group-text rounded-start-3"><i class="bi bi-facebook" style="color:#1877f2"></i></span>
                            <input type="url" name="link_facebook" class="form-control rounded-end-3"
                                placeholder="https://facebook.com/namahalaman"
                                value="<?= htmlspecialchars($k['link_facebook'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NARASI & JAM -->
        <div class="col-lg-6">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                        style="width:32px;height:32px;background:rgba(45,90,39,0.1)">
                        <i class="bi bi-chat-quote-fill" style="color:#2d5a27;font-size:.85rem"></i>
                    </div>
                    <h6 class="fw-bold mb-0">Teks & Jam Operasional</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Judul Halaman Kontak</label>
                        <input type="text" name="mari_terhubung_judul" class="form-control rounded-3"
                            placeholder="Mari Terhubung"
                            value="<?= htmlspecialchars($k['mari_terhubung_judul'] ?? 'Mari Terhubung') ?>">
                        <div class="form-text">Judul besar yang muncul di bagian atas halaman kontak</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Deskripsi di bawah judul</label>
                        <textarea name="narasi_kontak" class="form-control rounded-3" rows="3"
                            placeholder="Teks deskripsi di bagian atas halaman kontak..."><?= htmlspecialchars($k['narasi_kontak'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Jam Operasional</label>
                        <input type="text" name="jam_operasional" class="form-control rounded-3"
                            placeholder="Senin - Sabtu: 08.00 - 17.00 WIB"
                            value="<?= htmlspecialchars($k['jam_operasional'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Alamat Lengkap</label>
                        <textarea name="alamat" class="form-control rounded-3" rows="2"
                            placeholder="Jl. Contoh No.1, Purwokerto"><?= htmlspecialchars($k['alamat'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- GOOGLE MAPS -->
        <div class="col-12">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                        style="width:32px;height:32px;background:rgba(45,90,39,0.1)">
                        <i class="bi bi-geo-alt-fill" style="color:#2d5a27;font-size:.85rem"></i>
                    </div>
                    <h6 class="fw-bold mb-0">Google Maps</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Link Maps
                                <small class="text-muted">(tombol "Buka di Maps")</small>
                            </label>
                            <input type="url" name="link_maps" class="form-control rounded-3"
                                placeholder="https://maps.google.com/..."
                                value="<?= htmlspecialchars($k['link_maps'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Embed Maps</label>
                            <textarea name="embed_maps" class="form-control rounded-3" rows="3"
                                placeholder="Paste full &lt;iframe&gt; atau URL src saja"><?= htmlspecialchars($k['embed_maps'] ?? '') ?></textarea>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Bisa paste <strong>full iframe</strong> atau <strong>URL src saja</strong> — otomatis diproses.
                            </div>
                        </div>
                        <?php if (!empty($k['embed_maps'])): ?>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Preview Maps</label>
                            <?php
                            $prev = $k['embed_maps'];
                            if(strpos($prev, '<iframe') !== false) {
                                preg_match('/src=["\']([^"\']+)["\']/', $prev, $m);
                                $prev = $m[1] ?? '';
                            }
                            ?>
                            <?php if($prev): ?>
                            <iframe src="<?= htmlspecialchars($prev) ?>"
                                width="100%" height="220"
                                style="border:0;border-radius:12px"
                                allowfullscreen loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- SUBMIT -->
        <div class="col-12 pb-3">
            <div class="d-flex gap-3">
                <button type="submit" class="btn rounded-pill px-5 fw-semibold py-2"
                    style="background:#2d5a27;color:white">
                    <i class="bi bi-save me-2"></i>Simpan Semua Perubahan
                </button>
                <a href="../kontak.php" target="_blank"
                    class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-box-arrow-up-right me-2"></i>Lihat Halaman Kontak
                </a>
            </div>
        </div>

    </div>
</form>

<?php require_once 'footer_admin.php'; $db->close(); ?>