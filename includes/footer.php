<?php
if (!isset($kontak)) {
    $db_footer = getDB();
    $kontak = $db_footer->query("SELECT * FROM kontak_usaha LIMIT 1")->fetch_assoc();
    $db_footer->close();
}
?>

<footer class="py-5 mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5 class="text-white fw-bold mb-3"><i class="bi bi-scissors me-2"></i>Yogi Bordir</h5>
                <p class="small" style="color:rgba(255,255,255,0.65)">Solusi bordir komputer terpercaya di Purwokerto untuk segala kebutuhan pakaian Anda.</p>
                <div class="d-flex gap-3 mt-3">
                    <?php if ($kontak && $kontak['link_instagram']): ?>
                        <a href="<?= $kontak['link_instagram'] ?>" target="_blank"><i class="bi bi-instagram fs-5"></i></a>
                    <?php endif; ?>
                    <?php if ($kontak && $kontak['link_facebook']): ?>
                        <a href="<?= $kontak['link_facebook'] ?>" target="_blank"><i class="bi bi-facebook fs-5"></i></a>
                    <?php endif; ?>
                    <?php if ($kontak && $kontak['no_whatsapp']): ?>
                        <a href="https://wa.me/<?= $kontak['no_whatsapp'] ?>" target="_blank"><i class="bi bi-whatsapp fs-5"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-4">
                <h6 class="text-white fw-semibold mb-3">Halaman</h6>
                <ul class="list-unstyled small">
                    <li class="mb-1"><a href="/yogi-bordir/index.php">Beranda</a></li>
                    <li class="mb-1"><a href="/yogi-bordir/galeri.php">Galeri Produk</a></li>
                    <li class="mb-1"><a href="/yogi-bordir/layanan.php">Layanan</a></li>
                    <li class="mb-1"><a href="/yogi-bordir/profil.php">Profil Usaha</a></li>
                    <li class="mb-1"><a href="/yogi-bordir/kontak.php">Kontak</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="text-white fw-semibold mb-3">Kontak</h6>
                <ul class="list-unstyled small" style="color:rgba(255,255,255,0.65)">
                    <?php if ($kontak && $kontak['no_whatsapp']): ?>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i><?= htmlspecialchars($kontak['no_whatsapp']) ?></li>
                    <?php endif; ?>
                    <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>Purwokerto, Banyumas, Jawa Tengah</li>
                </ul>
            </div>
        </div>
        <hr style="border-color:rgba(255,255,255,0.15)" class="my-4">
        <p class="text-center small mb-0" style="color:rgba(255,255,255,0.5)">&copy; <?= date('Y') ?> Yogi Bordir Purwokerto. All rights reserved.</p>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>