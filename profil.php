<?php
$page_title = 'Profil Usaha';
require_once 'includes/header.php';
$db = getDB();

$profil = $db->query("SELECT * FROM profil_usaha WHERE id_profil=1 LIMIT 1")->fetch_assoc();
$misi_list = $db->query("SELECT * FROM misi ORDER BY urutan ASC");
$nilai_list = $db->query("SELECT * FROM nilai_usaha ORDER BY urutan ASC");

$header_judul = $profil['header_judul'] ?? 'Profil Usaha';
$header_desk  = $profil['header_deskripsi'] ?? 'Mengenal lebih dekat ' . ($profil['nama_usaha'] ?? 'Yogi Bordir') . ' Purwokerto';

// Foto tentang kami
if (!empty($profil['header_bg_file']) && file_exists('public/uploads/' . $profil['header_bg_file'])) {
    $foto_tentang = 'public/uploads/' . htmlspecialchars($profil['header_bg_file']);
} else {
    $foto_tentang = 'https://images.unsplash.com/photo-1598257006458-087169a1f08d?w=800&q=70';
}
?>

<!-- PAGE HEADER -->
<div style="background:linear-gradient(135deg,var(--hijau-tua),var(--hijau-muda));padding:80px 0 60px"
    class="text-white text-center">
    <div class="container">
        <h1 class="display-5 fw-bold mb-2"><?= htmlspecialchars($header_judul) ?></h1>
        <p class="opacity-75 mb-0"><?= htmlspecialchars($header_desk) ?></p>
    </div>
</div>

<!-- TENTANG KAMI -->
<section class="py-5">
    <div class="container py-3">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <img src="<?= $foto_tentang ?>"
                    alt="Workshop" class="img-fluid rounded-4 shadow-lg w-100"
                    style="height:400px;object-fit:cover">
            </div>
            <div class="col-lg-6">
                <span class="section-badge">Tentang Kami</span>
                <h2 class="section-title"><?= htmlspecialchars($profil['nama_usaha'] ?? 'Yogi Bordir') ?></h2>
                <div class="divider-hijau"></div>
                <p class="text-muted mb-3"><?= htmlspecialchars($profil['deskripsi1'] ?? '') ?></p>
                <p class="text-muted mb-4"><?= htmlspecialchars($profil['deskripsi2'] ?? '') ?></p>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 rounded-3" style="background:rgba(45,90,39,0.08)">
                            <div class="fw-bold" style="color:var(--hijau-tua)">Berdiri Sejak</div>
                            <div class="text-muted small"><?= htmlspecialchars($profil['tahun_berdiri'] ?? '2010') ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3" style="background:rgba(45,90,39,0.08)">
                            <div class="fw-bold" style="color:var(--hijau-tua)">Lokasi</div>
                            <div class="text-muted small"><?= htmlspecialchars($profil['lokasi'] ?? 'Purwokerto') ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3" style="background:rgba(45,90,39,0.08)">
                            <div class="fw-bold" style="color:var(--hijau-tua)">Spesialisasi</div>
                            <div class="text-muted small"><?= htmlspecialchars($profil['spesialisasi'] ?? 'Bordir Komputer') ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3" style="background:rgba(45,90,39,0.08)">
                            <div class="fw-bold" style="color:var(--hijau-tua)">Pengiriman</div>
                            <div class="text-muted small"><?= htmlspecialchars($profil['pengiriman'] ?? 'Seluruh Indonesia') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- VISI -->
<section class="py-5 bg-white">
    <div class="container py-3">
        <div class="text-center mb-5">
            <span class="section-badge">Arah Kami</span>
            <h2 class="section-title">Visi & Misi</h2>
            <div class="divider-hijau mx-auto"></div>
        </div>
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="p-4 rounded-4 h-100"
                    style="background:linear-gradient(135deg,var(--hijau-tua),var(--hijau-muda));color:white">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="bi bi-eye-fill fs-3"></i>
                        <h4 class="fw-bold mb-0">Visi</h4>
                    </div>
                    <p class="mb-0 opacity-90"><?= htmlspecialchars($profil['visi'] ?? '') ?></p>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="p-4 rounded-4 h-100 border">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="bi bi-bullseye fs-3" style="color:var(--hijau-tua)"></i>
                        <h4 class="fw-bold mb-0" style="color:var(--hijau-tua)">Misi</h4>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <?php $no = 1;
                        while ($m = $misi_list->fetch_assoc()): ?>
                            <div class="d-flex gap-3">
                                <span class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold text-white"
                                    style="width:28px;height:28px;background:var(--hijau-tua);font-size:12px">
                                    <?= $no++ ?>
                                </span>
                                <p class="text-muted small mb-0 pt-1"><?= htmlspecialchars($m['isi']) ?></p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



<?php require_once 'includes/footer.php';
$db->close(); ?>