<?php
$page_title = 'Galeri Produk';
require_once 'includes/header.php';
$db = getDB();

$filter_kat = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$kategori_list = $db->query("SELECT * FROM kategori ORDER BY nama_kategori");

$sql = "SELECT p.*, k.nama_kategori,
    GROUP_CONCAT(pf.nama_file ORDER BY pf.urutan SEPARATOR ',') as semua_foto
    FROM produk p
    JOIN kategori k ON p.id_kategori = k.id_kategori
    LEFT JOIN produk_foto pf ON p.id_produk = pf.id_produk";
if ($filter_kat > 0) $sql .= " WHERE p.id_kategori = $filter_kat";
$sql .= " GROUP BY p.id_produk ORDER BY p.id_produk DESC";
$produk_list = $db->query($sql);

// Helper: resolve URL foto (Cloudinary atau lokal)
function fotoUrl($nama) {
    if(!$nama) return '';
    if(strpos($nama, 'http') === 0) return $nama; // sudah URL Cloudinary
    return 'public/uploads/' . $nama; // file lokal lama
}
?>

<style>
    #modalDetail .modal-body {
        min-height: 350px;
        height: auto;
        overflow-y: auto;
    }
    #modalDetail .modal-content {
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    #modalDetail .modal-body {
        flex: 1;
        overflow-y: auto;
    }
</style>

<div style="background: linear-gradient(135deg, var(--hijau-tua), var(--hijau-muda)); padding: 80px 0 60px;" class="text-white text-center">
    <div class="container">
        <h1 class="display-5 fw-bold mb-2">Galeri Produk</h1>
        <p class="opacity-75 mb-0">Kumpulan hasil bordir terbaik dari workshop kami</p>
    </div>
</div>

<section class="py-5">
    <div class="container">

        <!-- FILTER KATEGORI + SORT -->
        <div class="d-flex align-items-center justify-content-between gap-3 mb-5 flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <a href="galeri.php" class="btn-filter <?= $filter_kat == 0 ? 'active' : '' ?>">Semua</a>
                <div class="position-relative">
                    <button class="btn border rounded-3 px-3 py-2 d-flex align-items-center gap-2 bg-white"
                        id="katBtn" onclick="toggleKat()"
                        style="font-size:.85rem;min-width:160px;border-color:#ddd!important;<?= $filter_kat > 0 ? 'border-color:#2d5a27!important;color:#2d5a27;font-weight:600' : '' ?>">
                        <i class="bi bi-grid me-1"></i>
                        <span id="katLabel">
                            <?php if ($filter_kat > 0):
                                $kat_aktif = $db->query("SELECT nama_kategori FROM kategori WHERE id_kategori=$filter_kat")->fetch_assoc();
                                echo htmlspecialchars($kat_aktif['nama_kategori'] ?? 'Kategori');
                            else: ?>Pilih Kategori<?php endif; ?>
                        </span>
                        <i class="bi bi-chevron-down ms-auto" id="katChevron"></i>
                    </button>
                    <div id="katDropdown" class="position-absolute start-0 bg-white rounded-3 shadow border mt-1 d-none"
                        style="min-width:200px;z-index:100;max-height:280px;overflow-y:auto">
                        <a href="galeri.php" class="d-block px-3 py-2 text-decoration-none <?= $filter_kat == 0 ? 'fw-bold' : '' ?>"
                            style="font-size:.85rem;color:#2d5a27"
                            onmouseover="this.style.background='rgba(45,90,39,0.08)'"
                            onmouseout="this.style.background=''">
                            <i class="bi bi-grid me-2"></i>Semua Kategori
                        </a>
                        <hr class="my-1">
                        <?php
                        $kat_dropdown = $db->query("SELECT * FROM kategori ORDER BY nama_kategori");
                        while ($kat = $kat_dropdown->fetch_assoc()):
                            $is_active = $filter_kat == $kat['id_kategori'];
                        ?>
                            <a href="galeri.php?kategori=<?= $kat['id_kategori'] ?>"
                                class="d-block px-3 py-2 text-decoration-none"
                                style="font-size:.85rem;color:<?= $is_active ? '#2d5a27' : '#333' ?>;font-weight:<?= $is_active ? '600' : 'normal' ?>"
                                onmouseover="this.style.background='rgba(45,90,39,0.08)'"
                                onmouseout="this.style.background=''">
                                <?= $is_active ? '<i class="bi bi-check2 me-2" style="color:#2d5a27"></i>' : '<i class="bi bi-tag me-2 text-muted"></i>' ?>
                                <?= htmlspecialchars($kat['nama_kategori']) ?>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <div class="position-relative flex-shrink-0">
                <button class="btn border rounded-3 px-3 py-2 d-flex align-items-center gap-2 bg-white"
                    id="sortBtn" onclick="toggleSort()"
                    style="font-size:.85rem;min-width:130px;border-color:#ddd!important">
                    <i class="bi bi-sort-down me-1"></i>
                    <span id="sortLabel">Terbaru</span>
                    <i class="bi bi-chevron-down ms-auto" id="sortChevron"></i>
                </button>
                <div id="sortDropdown" class="position-absolute end-0 bg-white rounded-3 shadow border mt-1 d-none"
                    style="min-width:160px;z-index:100">
                    <?php foreach ([['terbaru', 'Terbaru'], ['terlama', 'Terlama'], ['az', 'A - Z'], ['za', 'Z - A']] as $s): ?>
                        <div class="px-3 py-2 sort-item" style="cursor:pointer;font-size:.85rem"
                            onmouseover="this.style.background='rgba(45,90,39,0.08)'"
                            onmouseout="this.style.background=''"
                            onclick="pilihSort('<?= $s[0] ?>','<?= $s[1] ?>')">
                            <?= $s[1] ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- GRID PRODUK -->
        <div class="row g-4">
            <?php if ($produk_list->num_rows == 0): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted mt-3">Belum ada produk di kategori ini.</p>
                </div>
            <?php else: ?>
                <?php while ($p = $produk_list->fetch_assoc()):
                    $fotoUtama = fotoUrl($p['foto']);
                    if(!$fotoUtama) $fotoUtama = 'https://images.unsplash.com/photo-1594932224031-44f00db58ce8?w=600&q=60';

                    // Resolve semua foto — bisa mix Cloudinary + lokal
                    $semuaFotoResolved = [];
                    if($p['semua_foto']) {
                        foreach(explode(',', $p['semua_foto']) as $f) {
                            $f = trim($f);
                            if($f) $semuaFotoResolved[] = fotoUrl($f);
                        }
                    }
                    $semuaFotoJson = implode(',', $semuaFotoResolved);

                    $fotoSCResolved = fotoUrl($p['foto_size_chart'] ?? '');
                ?>
                    <div class="col-sm-6 col-lg-4">
                        <div class="card card-produk d-flex flex-column h-100">
                            <img src="<?= htmlspecialchars($fotoUtama) ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>">
                            <div class="card-body p-4 d-flex flex-column">
                                <h5 class="fw-bold mb-2"><?= htmlspecialchars($p['nama_produk']) ?></h5>
                                <p class="text-muted small mb-3" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"><?= htmlspecialchars($p['deskripsi']) ?></p>
                                <button class="btn btn-outline-hijau btn-sm rounded-pill w-100 mt-auto"
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
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- MODAL DETAIL -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
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
                    <!-- FOTO -->
                    <div class="col-md-5">
                        <div class="position-relative rounded-3 overflow-hidden mb-2"
                            style="height:280px;background:#f5f5f5;cursor:zoom-in"
                            onclick="bukaLightbox(currentIdx)">
                            <img id="modalFoto" src="" alt="" class="w-100 h-100" style="object-fit:contain;transition:.2s">
                            <span id="badgeSC" class="position-absolute top-0 end-0 m-2 badge d-none" style="background:#2d5a27;font-size:.7rem">
                                <i class="bi bi-rulers me-1"></i>Size Chart
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

                    <!-- INFO -->
                    <div class="col-md-7">
                        <div id="sectionUkuran" class="mb-3">
                            <h6 class="fw-bold mb-2" style="font-size:.85rem;color:#2d5a27">
                                <i class="bi bi-rulers me-1"></i>Ukuran Tersedia
                            </h6>
                            <div id="modalUkuran" class="d-flex flex-wrap gap-2"></div>
                        </div>
                        <div id="sectionWarna" class="mb-3">
                            <h6 class="fw-bold mb-2" style="font-size:.85rem;color:#2d5a27">
                                <i class="bi bi-palette me-1"></i>Warna Tersedia
                            </h6>
                            <div id="modalWarna" class="d-flex flex-wrap gap-2"></div>
                        </div>
                        <div id="sectionDeskripsi" class="mb-2">
                            <h6 class="fw-bold mb-2" style="font-size:.85rem;color:#2d5a27">
                                <i class="bi bi-info-circle me-1"></i>Deskripsi Produk
                            </h6>
                            <div class="p-3 rounded-3" style="background:rgba(0,0,0,0.04);border:1px solid rgba(0,0,0,0.08)">
                                <p id="modalDeskripsi" class="mb-0 text-muted" style="font-size:.88rem;line-height:1.7;white-space:pre-line"></p>
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
    let allFotos = [], currentIdx = 0, lightboxIdx = 0;

    function lihatDetail(nama, deskripsi, semuaFoto, ukuran, warna, kategori, fotoSC) {
        document.getElementById('modalNama').textContent = nama;
        document.getElementById('modalDeskripsi').textContent = deskripsi || '-';
        document.getElementById('modalKategori').textContent = kategori;

        allFotos = [];
        if (semuaFoto && semuaFoto.trim()) {
            // URL sudah di-resolve di PHP, langsung pakai
            semuaFoto.split(',').filter(f => f.trim()).forEach(f => {
                allFotos.push({ src: f.trim(), isSC: false });
            });
        }
        if (fotoSC && fotoSC.trim()) {
            allFotos.push({ src: fotoSC.trim(), isSC: true });
        }
        if (allFotos.length === 0) {
            allFotos.push({ src: 'https://images.unsplash.com/photo-1594932224031-44f00db58ce8?w=600&q=60', isSC: false });
        }

        currentIdx = 0;
        renderFoto(0);
        buildThumbs();

        const showNav = allFotos.length > 1;
        document.getElementById('btnPrev').classList.toggle('d-none', !showNav);
        document.getElementById('btnNext').classList.toggle('d-none', !showNav);
        document.getElementById('fotoCounter').classList.toggle('d-none', !showNav);

        // UKURAN
        const ukuranEl = document.getElementById('modalUkuran');
        const secUkuran = document.getElementById('sectionUkuran');
        ukuranEl.innerHTML = '';
        if (ukuran && ukuran.trim()) {
            secUkuran.style.display = 'block';
            ukuran.split(',').forEach(u => {
                u = u.trim();
                const b = document.createElement('span');
                b.className = 'px-3 py-1 rounded-pill border fw-semibold';
                b.style.cssText = 'border-color:#2d5a27!important;color:#2d5a27;font-size:.8rem;cursor:pointer;transition:all .2s';
                b.textContent = u;
                b.onclick = () => {
                    document.querySelectorAll('#modalUkuran span').forEach(el => { el.style.background=''; el.style.color='#2d5a27'; });
                    b.style.background = '#2d5a27'; b.style.color = 'white';
                };
                ukuranEl.appendChild(b);
            });
        } else { secUkuran.style.display = 'none'; }

        // WARNA
        const warnaEl = document.getElementById('modalWarna');
        const secWarna = document.getElementById('sectionWarna');
        warnaEl.innerHTML = '';
        if (warna && warna.trim()) {
            secWarna.style.display = 'block';
            warna.split(',').map(w => w.trim()).filter(w => w).forEach((w, i) => {
                const b = document.createElement('span');
                b.className = 'px-3 py-1 rounded-pill border fw-semibold';
                b.style.cssText = 'border-color:#2d5a27!important;color:#2d5a27;font-size:.8rem;cursor:pointer;transition:all .2s';
                b.textContent = w;
                b.onclick = () => {
                    document.querySelectorAll('#modalWarna span').forEach(el => { el.style.background=''; el.style.color='#2d5a27'; });
                    b.style.background = '#2d5a27'; b.style.color = 'white';
                    if (i < allFotos.length) { currentIdx = i; renderFoto(i); }
                };
                warnaEl.appendChild(b);
            });
        } else { secWarna.style.display = 'none'; }

        const modalEl = document.getElementById('modalDetail');
        const existingModal = bootstrap.Modal.getInstance(modalEl);
        if (existingModal) existingModal.dispose();
        new bootstrap.Modal(modalEl).show();
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
            img.style.cssText = 'object-fit:cover;cursor:pointer;border:2px solid ' + (i===0?'#2d5a27':'#ddd') + ';opacity:'+(i===0?'1':'0.6')+';transition:.2s';
            img.onclick = () => { currentIdx = i; renderFoto(i); };
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

    function tutupLightbox() { document.getElementById('lightboxOverlay').style.display = 'none'; }

    function navigasiLightbox(arah) {
        lightboxIdx = (lightboxIdx + arah + allFotos.length) % allFotos.length;
        document.getElementById('lightboxImg').src = allFotos[lightboxIdx].src;
        document.getElementById('lbNow').textContent = lightboxIdx + 1;
    }

    function toggleKat() {
        const dd = document.getElementById('katDropdown');
        const ch = document.getElementById('katChevron');
        document.getElementById('sortDropdown').classList.add('d-none');
        dd.classList.toggle('d-none');
        ch.className = dd.classList.contains('d-none') ? 'bi bi-chevron-down ms-auto' : 'bi bi-chevron-up ms-auto';
    }

    function toggleSort() {
        const dd = document.getElementById('sortDropdown');
        const ch = document.getElementById('sortChevron');
        document.getElementById('katDropdown').classList.add('d-none');
        dd.classList.toggle('d-none');
        ch.className = dd.classList.contains('d-none') ? 'bi bi-chevron-down ms-auto' : 'bi bi-chevron-up ms-auto';
    }

    function pilihSort(val, label) {
        document.getElementById('sortLabel').textContent = label;
        document.getElementById('sortDropdown').classList.add('d-none');
        document.getElementById('sortChevron').className = 'bi bi-chevron-down ms-auto';
        const grid = document.querySelector('.row.g-4');
        const cards = [...grid.querySelectorAll('.col-sm-6')];
        cards.sort((a, b) => {
            const na = a.querySelector('h5')?.textContent.trim() ?? '';
            const nb = b.querySelector('h5')?.textContent.trim() ?? '';
            if (val === 'az') return na.localeCompare(nb);
            if (val === 'za') return nb.localeCompare(na);
            return 0;
        });
        cards.forEach(c => grid.appendChild(c));
    }

    document.addEventListener('click', e => {
        if (!e.target.closest('#katBtn') && !e.target.closest('#katDropdown')) {
            document.getElementById('katDropdown').classList.add('d-none');
            document.getElementById('katChevron').className = 'bi bi-chevron-down ms-auto';
        }
        if (!e.target.closest('#sortBtn') && !e.target.closest('#sortDropdown')) {
            document.getElementById('sortDropdown').classList.add('d-none');
            document.getElementById('sortChevron').className = 'bi bi-chevron-down ms-auto';
        }
    });

    document.addEventListener('keydown', e => {
        if (document.getElementById('lightboxOverlay').style.display === 'flex') {
            if (e.key === 'ArrowLeft') navigasiLightbox(-1);
            if (e.key === 'ArrowRight') navigasiLightbox(1);
            if (e.key === 'Escape') tutupLightbox();
        }
    });

    let touchStartX = 0;
    document.getElementById('modalFoto')?.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; });
    document.getElementById('modalFoto')?.addEventListener('touchend', e => {
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 40) navigasiFoto(diff > 0 ? 1 : -1);
    });
</script>

<?php require_once 'includes/footer.php'; $db->close(); ?>