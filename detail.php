<?php
$page_title = 'Detail Produk';
require_once 'includes/header.php';
$db = getDB();

$id = (int)($_GET['id'] ?? 0);
if(!$id) { header('Location: galeri.php'); exit; }

$produk = $db->query("SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON p.id_kategori=k.id_kategori WHERE p.id_produk=$id")->fetch_assoc();
if(!$produk) { header('Location: galeri.php'); exit; }

$foto_list = $db->query("SELECT * FROM produk_foto WHERE id_produk=$id ORDER BY urutan ASC");
$fotos = [];
while($f = $foto_list->fetch_assoc()) $fotos[] = $f;

$ukuran_list = !empty($produk['ukuran']) ? array_map('trim', explode(',', $produk['ukuran'])) : [];
$warna_list  = !empty($produk['warna'])  ? array_map('trim', explode(',', $produk['warna']))  : [];
?>

<div style="background: linear-gradient(135deg, var(--hijau-tua), var(--hijau-muda)); padding: 60px 0 40px;" class="text-white">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="/yogi-bordir/" class="text-white-50 text-decoration-none">Beranda</a></li>
                <li class="breadcrumb-item"><a href="/yogi-bordir/galeri.php" class="text-white-50 text-decoration-none">Galeri</a></li>
                <li class="breadcrumb-item active text-white"><?= htmlspecialchars($produk['nama_produk']) ?></li>
            </ol>
        </nav>
        <h1 class="display-6 fw-bold mb-0"><?= htmlspecialchars($produk['nama_produk']) ?></h1>
    </div>
</div>

<section class="py-5">
    <div class="container py-3">
        <div class="row g-5">

            <!-- FOTO PRODUK -->
            <div class="col-lg-6">
                <?php if(!empty($fotos)): ?>
                <!-- FOTO UTAMA -->
                <div class="rounded-4 overflow-hidden shadow-lg mb-3" style="height:420px">
                    <img id="fotoUtama" src="public/uploads/<?= htmlspecialchars($fotos[0]['nama_file']) ?>"
                        class="w-100 h-100" style="object-fit:cover">
                </div>
                <!-- THUMBNAIL -->
                <?php if(count($fotos) > 1): ?>
                <div class="d-flex gap-2 flex-wrap">
                    <?php foreach($fotos as $i => $f): ?>
                    <img src="public/uploads/<?= htmlspecialchars($f['nama_file']) ?>"
                        class="rounded-3 foto-thumb <?= $i===0?'active-thumb':'' ?>"
                        style="width:72px;height:72px;object-fit:cover;cursor:pointer;border:3px solid <?= $i===0?'#2d5a27':'#e0e0e0' ?>;transition:.2s"
                        onclick="gantiФото(this, 'public/uploads/<?= htmlspecialchars($f['nama_file']) ?>')">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php elseif($produk['foto'] && file_exists('public/uploads/'.$produk['foto'])): ?>
                <div class="rounded-4 overflow-hidden shadow-lg" style="height:420px">
                    <img src="public/uploads/<?= htmlspecialchars($produk['foto']) ?>" class="w-100 h-100" style="object-fit:cover">
                </div>
                <?php else: ?>
                <div class="rounded-4 bg-light d-flex align-items-center justify-content-center shadow-lg" style="height:420px">
                    <i class="bi bi-image fs-1 text-muted"></i>
                </div>
                <?php endif; ?>
            </div>

            <!-- INFO PRODUK -->
            <div class="col-lg-6">
                <span class="badge mb-3 px-3 py-2 rounded-pill" style="background:rgba(45,90,39,0.1);color:#2d5a27">
                    <?= htmlspecialchars($produk['nama_kategori']) ?>
                </span>
                <h2 class="fw-bold mb-3"><?= htmlspecialchars($produk['nama_produk']) ?></h2>

                <?php if($produk['deskripsi']): ?>
                <p class="text-muted mb-4"><?= nl2br(htmlspecialchars($produk['deskripsi'])) ?></p>
                <?php endif; ?>

                <!-- UKURAN -->
                <?php if(!empty($ukuran_list)): ?>
                <div class="mb-4">
                    <h6 class="fw-bold mb-2">Ukuran Tersedia</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach($ukuran_list as $u): ?>
                        <span class="px-3 py-1 rounded-pill border fw-semibold small" style="border-color:#2d5a27;color:#2d5a27">
                            <?= htmlspecialchars($u) ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- WARNA -->
                <?php if(!empty($warna_list)): ?>
                <div class="mb-4">
                    <h6 class="fw-bold mb-2">Warna Tersedia</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach($warna_list as $w): ?>
                        <span class="px-3 py-1 rounded-pill small fw-medium" style="background:rgba(45,90,39,0.08);color:#2d5a27">
                            <i class="bi bi-circle-fill me-1" style="font-size:.5rem"></i><?= htmlspecialchars($w) ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- TOMBOL PESAN -->
                <?php if($kontak && $kontak['no_whatsapp']): ?>
                <a href="https://wa.me/<?= $kontak['no_whatsapp'] ?>?text=Halo+Yogi+Bordir,+saya+ingin+memesan+produk:+<?= urlencode($produk['nama_produk']) ?>"
                    target="_blank" class="btn btn-hijau w-100 py-3 mb-3 fs-6">
                    <i class="bi bi-whatsapp me-2"></i>Pesan via WhatsApp
                </a>
                <?php endif; ?>

                <a href="galeri.php" class="btn btn-outline-secondary w-100 py-2 rounded-pill">
                    <i class="bi bi-arrow-left me-2"></i>Kembali ke Galeri
                </a>
            </div>
        </div>

        <!-- SIZE CHART -->
        <?php if(!empty($produk['foto_size_chart']) && file_exists('public/uploads/'.$produk['foto_size_chart'])): ?>
        <div class="mt-5 pt-3">
            <div class="text-center mb-4">
                <h4 class="fw-bold" style="color:#2d5a27">Size Chart</h4>
                <div class="divider-hijau mx-auto"></div>
            </div>
            <div class="text-center">
                <img src="public/uploads/<?= htmlspecialchars($produk['foto_size_chart']) ?>"
                    class="img-fluid rounded-4 shadow-sm" style="max-width:600px">
            </div>
        </div>
        <?php endif; ?>

        <!-- PRODUK LAINNYA -->
        <?php
        $lainnya = $db->query("SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON p.id_kategori=k.id_kategori WHERE p.id_produk != $id AND p.id_kategori={$produk['id_kategori']} ORDER BY RAND() LIMIT 3");
        if($lainnya->num_rows > 0):
        ?>
        <div class="mt-5 pt-3">
            <h4 class="fw-bold mb-4" style="color:#2d5a27">Produk Serupa</h4>
            <div class="row g-4">
                <?php while($l = $lainnya->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card card-produk">
                        <?php
                        $foto_l = $db->query("SELECT nama_file FROM produk_foto WHERE id_produk={$l['id_produk']} ORDER BY urutan ASC LIMIT 1")->fetch_assoc();
                        $src_l = $foto_l ? 'public/uploads/'.$foto_l['nama_file'] : ($l['foto'] ? 'public/uploads/'.$l['foto'] : '');
                        ?>
                        <?php if($src_l && file_exists($src_l)): ?>
                        <img src="<?= htmlspecialchars($src_l) ?>" alt="<?= htmlspecialchars($l['nama_produk']) ?>">
                        <?php else: ?>
                        <img src="https://images.unsplash.com/photo-1594932224031-44f00db58ce8?w=600&q=60" alt="Produk">
                        <?php endif; ?>
                        <div class="card-body p-4">
                            <span class="badge-cat mb-2 d-inline-block"><?= htmlspecialchars($l['nama_kategori']) ?></span>
                            <h5 class="fw-bold mb-2"><?= htmlspecialchars($l['nama_produk']) ?></h5>
                            <a href="detail.php?id=<?= $l['id_produk'] ?>" class="btn btn-hijau btn-sm w-100 mt-2">
                                <i class="bi bi-eye me-1"></i>Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
function gantiФото(el, src) {
    document.getElementById('fotoUtama').src = src;
    document.querySelectorAll('.foto-thumb').forEach(t => {
        t.style.borderColor = '#e0e0e0';
    });
    el.style.borderColor = '#2d5a27';
}
</script>

<?php require_once 'includes/footer.php'; $db->close(); ?>