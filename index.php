<?php
$page_title = 'Beranda';
require_once 'includes/header.php';
$db = getDB();

// Helper: resolve URL foto (Cloudinary atau lokal)
function fotoUrl($nama) {
    if (!$nama) return '';
    if (strpos($nama, 'http') === 0) return $nama; // sudah URL Cloudinary
    return 'public/uploads/' . $nama; // file lokal lama
}

$b = $db->query("SELECT * FROM beranda_konten LIMIT 1")->fetch_assoc();

$hero_badge   = $b['hero_badge']    ?? '✦ Seni Bordir Komputer Purwokerto';
$hero_judul_1 = $b['hero_judul_1']  ?? 'Kualitas Jahitan';
$hero_judul_2 = $b['hero_judul_2']  ?? 'Tanpa Kompromi.';
$hero_desk    = $b['hero_deskripsi'] ?? 'Solusi bordir premium di Purwokerto untuk segala jenis kebutuhan sandang Anda.';

$fitur = [
    ['icon' => $b['fitur1_icon'] ?? 'bi-patch-check-fill', 'judul' => $b['fitur1_judul'] ?? 'Mesin Bordir Modern',      'desk' => $b['fitur1_desk'] ?? 'Menggunakan mesin bordir komputer terkini untuk hasil presisi dan konsisten.'],
    ['icon' => $b['fitur2_icon'] ?? 'bi-clock-fill',       'judul' => $b['fitur2_judul'] ?? 'Pengerjaan Tepat Waktu',   'desk' => $b['fitur2_desk'] ?? 'Estimasi 3-7 hari kerja, selalu kami usahakan selesai sesuai jadwal.'],
    ['icon' => $b['fitur3_icon'] ?? 'bi-shield-fill-check', 'judul' => $b['fitur3_judul'] ?? 'Quality Control Ketat',    'desk' => $b['fitur3_desk'] ?? 'Setiap produk melalui pengecekan detail sebelum dikirimkan ke pelanggan.'],
    ['icon' => $b['fitur4_icon'] ?? 'bi-people-fill',      'judul' => $b['fitur4_judul'] ?? 'Konsultasi Desain Gratis', 'desk' => $b['fitur4_desk'] ?? 'Tim kami siap membantu mewujudkan desain bordir sesuai keinginan Anda.'],
];

// Foto keunggulan — cek Cloudinary URL dulu, fallback ke lokal, lalu ke Unsplash
$foto_keunggulan = fotoUrl($b['keunggulan_foto'] ?? '');
if (!$foto_keunggulan) {
    $foto_keunggulan = 'https://images.unsplash.com/photo-1544441893-675973e31985?w=800&q=70';
}

// Background hero
$hero_bg_resolved = fotoUrl($b['hero_bg_file'] ?? '');
if ($hero_bg_resolved) {
    $bg_style = "url('" . htmlspecialchars($hero_bg_resolved) . "')";
} elseif (!empty($b['hero_bg_url'])) {
    $bg_style = "url('" . htmlspecialchars($b['hero_bg_url']) . "')";
} else {
    $bg_style = "url('https://images.unsplash.com/photo-1598257006458-087169a1f08d?w=1600&q=60')";
}

$produk_unggulan = $db->query("
    SELECT p.*, k.nama_kategori,
        GROUP_CONCAT(pf.nama_file ORDER BY pf.urutan SEPARATOR ',') as semua_foto
    FROM produk p
    JOIN kategori k ON p.id_kategori = k.id_kategori
    LEFT JOIN produk_foto pf ON p.id_produk = pf.id_produk
    GROUP BY p.id_produk
    ORDER BY p.id_produk DESC LIMIT 6
");
?>

<!-- HERO -->
<section class="hero text-white" style="--hero-bg: <?= $bg_style ?>;">
    <div class="container hero-content">
        <div class="row align-items-center min-vh-100 py-5">
            <div class="col-lg-7">
                <span class="badge bg-light text-success mb-3 px-3 py-2 rounded-pill fw-semibold">
                    <?= htmlspecialchars($hero_badge) ?>
                </span>
                <h1 class="display-4 fw-bold mb-4 lh-sm">
                    <?= htmlspecialchars($hero_judul_1) ?><br>
                    <span style="color:#a8d5a2;font-style:italic"><?= htmlspecialchars($hero_judul_2) ?></span>
                </h1>
                <p class="lead mb-5 opacity-75 fw-light"><?= htmlspecialchars($hero_desk) ?></p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="galeri.php" class="btn btn-light text-success fw-bold rounded-pill px-4 py-2">
                        <i class="bi bi-grid me-2"></i>Lihat Galeri
                    </a>
                    <a href="kontak.php" class="btn btn-outline-light rounded-pill px-4 py-2">
                        <i class="bi bi-chat me-2"></i>Hubungi Kami
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- KENAPA KAMI — langsung setelah hero, tanpa statistik -->
<section class="py-5 bg-white">
    <div class="container py-3">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="section-badge">Keunggulan Kami</span>
                <h2 class="section-title">Mengapa Memilih<br>Yogi Bordir?</h2>
                <div class="divider-hijau"></div>
                <div class="d-flex flex-column gap-4 mt-3">
                    <?php foreach ($fitur as $f): ?>
                        <div class="d-flex gap-3">
                            <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center"
                                style="width:48px;height:48px;background:rgba(45,90,39,0.1)">
                                <i class="bi <?= htmlspecialchars($f['icon']) ?> fs-5" style="color:var(--hijau-tua)"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($f['judul']) ?></h6>
                                <p class="text-muted small mb-0"><?= htmlspecialchars($f['desk']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="<?= htmlspecialchars($foto_keunggulan) ?>"
                    alt="Workshop Yogi Bordir"
                    class="img-fluid rounded-4 shadow-lg w-100"
                    style="height:450px;object-fit:cover">
            </div>
        </div>
    </div>
</section>

<!-- PRODUK UNGGULAN -->
<section class="py-5">
    <div class="container py-3">
        <div class="text-center mb-5">
            <span class="section-badge">Produk Kami</span>
            <h2 class="section-title">Koleksi Bordir Pilihan</h2>
            <div class="divider-hijau mx-auto"></div>
            <p class="text-muted">Kami melayani berbagai media pakaian dengan detail bordir berkualitas tinggi.</p>
        </div>
        <div class="row g-4">
            <?php while ($p = $produk_unggulan->fetch_assoc()):
                $fotoUtama = fotoUrl($p['foto'] ?? '');
                if (!$fotoUtama) $fotoUtama = 'https://images.unsplash.com/photo-1594932224031-44f00db58ce8?w=600&q=60';

                $semuaFotoResolved = [];
                if (!empty($p['semua_foto'])) {
                    foreach (explode(',', $p['semua_foto']) as $f) {
                        $f = trim($f);
                        if ($f) $semuaFotoResolved[] = fotoUrl($f);
                    }
                }
                $semuaFotoJson = implode(',', $semuaFotoResolved);
                $fotoSCResolved = fotoUrl($p['foto_size_chart'] ?? '');
            ?>
                <div class="col-sm-6 col-lg-4">
                    <div class="card card-produk">
                        <img src="<?= htmlspecialchars($fotoUtama) ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>">
                        <div class="card-body p-4">
                            <span class="badge-cat mb-2 d-inline-block"><?= htmlspecialchars($p['nama_kategori']) ?></span>
                            <h5 class="fw-bold mb-2"><?= htmlspecialchars($p['nama_produk']) ?></h5>
                            <p class="text-muted small mb-3"><?= htmlspecialchars(substr($p['deskripsi'], 0, 80)) ?>...</p>
                            <button class="btn btn-outline-hijau btn-sm rounded-pill w-100"
                                onclick="lihatDetail(
            '<?= addslashes(htmlspecialchars($p['nama_produk'])) ?>',
            '<?= addslashes(htmlspecialchars($p['deskripsi'])) ?>',
            '<?= addslashes($semuaFotoJson) ?>',
            '<?= addslashes(htmlspecialchars($p['ukuran'] ?? '')) ?>',
            '<?= addslashes(htmlspecialchars($p['warna'] ?? '')) ?>',
            '<?= addslashes(htmlspecialchars($p['nama_kategori'])) ?>',
            '<?= addslashes($fotoSCResolved) ?>'
        )">
                                <i class="bi bi-eye me-1"></i>Detail
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center mt-5">
            <a href="galeri.php" class="btn btn-hijau px-5">Lihat Semua Produk <i class="bi bi-arrow-right ms-2"></i></a>
        </div>
    </div>
</section>


<!-- MODAL DETAIL (sama persis dengan galeri.php) -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div>
                    <h5 class="modal-title fw-bold" id="modalNama"></h5>
                    <span id="modalKategori" class="badge px-3 py-1 mt-1" style="background:rgba(45,90,39,0.1);color:#2d5a27;font-size:.75rem"></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <div class="row g-4">
                    <div class="col-md-5">
                        <div class="position-relative rounded-3 overflow-hidden mb-2"
                            style="height:320px;background:#f5f5f5;cursor:zoom-in"
                            onclick="bukaLightbox(currentIdx)">
                            <img id="modalFoto" src="" alt="" class="w-100 h-100" style="object-fit:contain;transition:.2s">
                            <span id="badgeSC" class="position-absolute top-0 end-0 m-2 badge d-none">

                            </span>
                            <div id="fotoCounter" class="position-absolute bottom-0 end-0 m-2 d-none"
                                style="background:rgba(0,0,0,0.45);color:white;font-size:.65rem;padding:2px 8px;border-radius:20px">
                                <i class="bi bi-zoom-in me-1"></i><span id="fotoNow">1</span>/<span id="fotoTotal">1</span>
                            </div>
                            <button id="btnPrev" onclick="event.stopPropagation();navigasiFoto(-1)"
                                class="position-absolute start-0 top-50 translate-middle-y ms-2 d-none"
                                style="background:rgba(0,0,0,0.45);color:white;border:none;border-radius:50%;width:34px;height:34px;font-size:1.2rem;cursor:pointer">‹</button>
                            <button id="btnNext" onclick="event.stopPropagation();navigasiFoto(1)"
                                class="position-absolute end-0 top-50 translate-middle-y me-2 d-none"
                                style="background:rgba(0,0,0,0.45);color:white;border:none;border-radius:50%;width:34px;height:34px;font-size:1.2rem;cursor:pointer">›</button>
                        </div>
                        <div id="thumbContainer" class="d-flex flex-wrap gap-2"></div>
                    </div>
                    <div class="col-md-7">
                        <div id="sectionUkuran" class="mb-3">
                            <h6 class="fw-bold mb-2" style="font-size:.85rem;color:#2d5a27">
                                <i class="bi bi-rulers me-1"></i>Ukuran Tersedia
                            </h6>
                            <div id="modalUkuran" class="d-flex flex-wrap gap-2"></div>
                        </div>
                        <div id="sectionWarna" class="mb-4">
                            <h6 class="fw-bold mb-2" style="font-size:.85rem;color:#2d5a27">
                                <i class="bi bi-palette me-1"></i>Warna Tersedia
                            </h6>
                            <div id="modalWarna" class="d-flex flex-wrap gap-2"></div>
                            <div class="p-3 rounded-3 mt-2" style="border:2px solid #2d5a27;background:rgba(45,90,39,0.04)">
                                <p id="modalDeskripsi" class="mb-0 text-muted" style="font-size:.88rem;line-height:1.6"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- LIGHTBOX -->
<div id="lightboxOverlay"
    style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.92);z-index:9999;align-items:center;justify-content:center;cursor:zoom-out"
    onclick="tutupLightbox()">
    <button onclick="event.stopPropagation();navigasiLightbox(-1)"
        style="position:absolute;left:20px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.2);color:white;border:none;border-radius:50%;width:50px;height:50px;font-size:1.6rem;cursor:pointer;z-index:10000">‹</button>
    <img id="lightboxImg" src="" onclick="event.stopPropagation()"
        style="max-height:90vh;max-width:88vw;object-fit:contain;border-radius:8px;position:relative;z-index:10000">
    <button onclick="event.stopPropagation();navigasiLightbox(1)"
        style="position:absolute;right:20px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.2);color:white;border:none;border-radius:50%;width:50px;height:50px;font-size:1.6rem;cursor:pointer;z-index:10000">›</button>
    <button onclick="tutupLightbox()"
        style="position:absolute;top:20px;right:20px;background:rgba(255,255,255,0.2);color:white;border:none;border-radius:50%;width:42px;height:42px;font-size:1.2rem;cursor:pointer;z-index:10000">✕</button>
    <div style="position:absolute;bottom:20px;left:50%;transform:translateX(-50%);background:rgba(0,0,0,0.5);color:white;padding:4px 14px;border-radius:20px;font-size:.8rem">
        <span id="lbNow">1</span>/<span id="lbTotal">1</span>
    </div>
</div>

<script>
    const noWA = '<?= $kontak['no_whatsapp'] ?? '' ?>';
    let allFotos = [],
        currentIdx = 0,
        lightboxIdx = 0;

    function lihatDetail(nama, deskripsi, semuaFoto, ukuran, warna, kategori, fotoSC) {
        document.getElementById('modalNama').textContent = nama;
        document.getElementById('modalDeskripsi').textContent = deskripsi || '-';
        document.getElementById('modalKategori').textContent = kategori;

        // URL sudah di-resolve di PHP (Cloudinary full URL atau path lokal), tinggal pakai langsung
        allFotos = [];
        if (semuaFoto && semuaFoto.trim()) {
            semuaFoto.split(',').filter(f => f.trim()).forEach(f => {
                allFotos.push({
                    src: f.trim(),
                    isSC: false
                });
            });
        }
        if (fotoSC && fotoSC.trim()) {
            allFotos.push({
                src: fotoSC.trim(),
                isSC: true
            });
        }
        if (allFotos.length === 0) {
            allFotos.push({
                src: 'https://images.unsplash.com/photo-1594932224031-44f00db58ce8?w=600&q=60',
                isSC: false
            });
        }

        currentIdx = 0;
        renderFoto(0);
        buildThumbs();

        const showNav = allFotos.length > 1;
        document.getElementById('btnPrev').classList.toggle('d-none', !showNav);
        document.getElementById('btnNext').classList.toggle('d-none', !showNav);
        document.getElementById('fotoCounter').classList.toggle('d-none', !showNav);

        const ukuranEl = document.getElementById('modalUkuran');
        const secUkuran = document.getElementById('sectionUkuran');
        ukuranEl.innerHTML = '';
        if (ukuran && ukuran.trim()) {
            secUkuran.style.display = 'block';
            ukuran.split(',').forEach(u => {
                const b = document.createElement('span');
                b.className = 'px-3 py-1 rounded-pill border fw-semibold';
                b.style.cssText = 'border-color:#2d5a27!important;color:#2d5a27;font-size:.8rem';
                b.textContent = u.trim();
                ukuranEl.appendChild(b);
            });
        } else {
            secUkuran.style.display = 'none';
        }

        const warnaEl = document.getElementById('modalWarna');
        const secWarna = document.getElementById('sectionWarna');
        warnaEl.innerHTML = '';
        if (warna && warna.trim()) {
            secWarna.style.display = 'block';
            warna.split(',').forEach(w => {
                const b = document.createElement('span');
                b.className = 'px-3 py-1 rounded-pill border fw-semibold';
                b.style.cssText = 'border-color:#2d5a27!important;color:#2d5a27;font-size:.8rem';
                b.textContent = w.trim();
                warnaEl.appendChild(b);
            });
        } else {
            secWarna.style.display = 'none';
        }

        new bootstrap.Modal(document.getElementById('modalDetail')).show();
    }

    function renderFoto(idx) {
        const foto = allFotos[idx];
        document.getElementById('modalFoto').src = foto.src;
        document.getElementById('badgeSC').classList.toggle('d-none', !foto.isSC);
        document.getElementById('fotoNow').textContent = idx + 1;
        document.getElementById('fotoTotal').textContent = allFotos.length;
        document.querySelectorAll('#thumbContainer img').forEach((t, i) => {
            t.style.borderColor = i === idx ? '#2d5a27' : '#ddd';
            t.style.opacity = i === idx ? '1' : '0.6';
        });
    }

    function buildThumbs() {
        const container = document.getElementById('thumbContainer');
        container.innerHTML = '';
        allFotos.forEach((f, i) => {
            const wrap = document.createElement('div');
            wrap.className = 'position-relative';
            wrap.style.cssText = 'width:60px;height:60px';
            const img = document.createElement('img');
            img.src = f.src;
            img.className = 'rounded-2 w-100 h-100';
            img.style.cssText = 'object-fit:cover;cursor:pointer;border:2px solid ' + (i === 0 ? '#2d5a27' : '#ddd') + ';opacity:' + (i === 0 ? '1' : '0.6') + ';transition:.2s';
            img.onclick = () => {
                currentIdx = i;
                renderFoto(i);
            };
            if (f.isSC) {
                const badge = document.createElement('span');
                badge.style.cssText = 'position:absolute;bottom:2px;left:2px;background:#2d5a27;color:white;font-size:.55rem;padding:1px 4px;border-radius:3px;pointer-events:none';
                badge.textContent = 'SC';
                wrap.appendChild(badge);
            }
            wrap.appendChild(img);
            container.appendChild(wrap);
        });
    }

    function navigasiFoto(arah) {
        currentIdx = (currentIdx + arah + allFotos.length) % allFotos.length;
        renderFoto(currentIdx);
    }

    function bukaLightbox(idx) {
        lightboxIdx = idx;
        document.getElementById('lightboxImg').src = allFotos[idx].src;
        document.getElementById('lbNow').textContent = idx + 1;
        document.getElementById('lbTotal').textContent = allFotos.length;
        document.getElementById('lightboxOverlay').style.display = 'flex';
    }

    function tutupLightbox() {
        document.getElementById('lightboxOverlay').style.display = 'none';
    }

    function navigasiLightbox(arah) {
        lightboxIdx = (lightboxIdx + arah + allFotos.length) % allFotos.length;
        document.getElementById('lightboxImg').src = allFotos[lightboxIdx].src;
        document.getElementById('lbNow').textContent = lightboxIdx + 1;
    }

    document.addEventListener('keydown', e => {
        if (document.getElementById('lightboxOverlay').style.display === 'flex') {
            if (e.key === 'ArrowLeft') navigasiLightbox(-1);
            if (e.key === 'ArrowRight') navigasiLightbox(1);
            if (e.key === 'Escape') tutupLightbox();
        }
    });
</script>
<?php require_once 'includes/footer.php';
$db->close(); ?>