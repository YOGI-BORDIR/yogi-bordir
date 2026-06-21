<?php
$page_title = 'Kontak';
require_once 'includes/header.php';
$db = getDB();
$kontak = $db->query("SELECT * FROM kontak_usaha LIMIT 1")->fetch_assoc();
?>

<div style="background:linear-gradient(135deg,var(--hijau-tua),var(--hijau-muda));padding:80px 0 60px"
    class="text-white text-center">
    <div class="container">
        <h1 class="display-5 fw-bold mb-2">
            <?= htmlspecialchars($kontak['mari_terhubung_judul'] ?? 'Mari Terhubung') ?>
        </h1>
        <p class="opacity-75 mb-0">
            <?= htmlspecialchars($kontak['narasi_kontak'] ?? 'Kami siap membantu kebutuhan bordir Anda') ?>
        </p>
    </div>
</div>

<section class="py-5">
    <div class="container py-3">
        <div class="row g-5">
            <div class="col-lg-5">
                <h4 class="fw-bold mb-4" style="color:var(--hijau-tua)">Informasi Kontak</h4>
                <div class="d-flex flex-column gap-4">

                    <?php if (!empty($kontak['alamat'])): ?>
                        <div class="d-flex gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width:48px;height:48px;background:rgba(45,90,39,0.1)">
                                <i class="bi bi-geo-alt-fill" style="color:var(--hijau-tua)"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Alamat</h6>
                                <p class="text-muted small mb-0"><?= htmlspecialchars($kontak['alamat']) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($kontak['no_whatsapp'])): ?>
                        <div class="d-flex gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width:48px;height:48px;background:rgba(37,211,102,0.1)">
                                <i class="bi bi-whatsapp" style="color:#25D366"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">WhatsApp</h6>
                                <p class="text-muted small mb-1">+<?= htmlspecialchars($kontak['no_whatsapp']) ?></p>
                                <a href="https://wa.me/<?= htmlspecialchars($kontak['no_whatsapp']) ?>"
                                    target="_blank" class="btn btn-hijau btn-sm rounded-pill px-3">
                                    <i class="bi bi-whatsapp me-1"></i>Chat Sekarang
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($kontak['email'])): ?>
                        <div class="d-flex gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width:48px;height:48px;background:rgba(45,90,39,0.1)">
                                <i class="bi bi-envelope-fill" style="color:var(--hijau-tua)"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Email</h6>
                                <a href="mailto:<?= htmlspecialchars($kontak['email']) ?>"
                                    class="text-muted small text-decoration-none">
                                    <?= htmlspecialchars($kontak['email']) ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($kontak['jam_operasional'])): ?>
                        <div class="d-flex gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width:48px;height:48px;background:rgba(45,90,39,0.1)">
                                <i class="bi bi-clock-fill" style="color:var(--hijau-tua)"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Jam Operasional</h6>
                                <p class="text-muted small mb-0"><?= htmlspecialchars($kontak['jam_operasional']) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($kontak['link_instagram'])): ?>
                        <div class="d-flex gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width:48px;height:48px;background:rgba(225,48,108,0.1)">
                                <i class="bi bi-instagram" style="color:#E1306C"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Instagram</h6>
                                <a href="<?= htmlspecialchars($kontak['link_instagram']) ?>"
                                    target="_blank" class="text-muted small text-decoration-none">
                                    <?= htmlspecialchars($kontak['link_instagram']) ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($kontak['link_facebook'])): ?>
                        <div class="d-flex gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width:48px;height:48px;background:rgba(24,119,242,0.1)">
                                <i class="bi bi-facebook" style="color:#1877F2"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Facebook</h6>
                                <a href="<?= htmlspecialchars($kontak['link_facebook']) ?>"
                                    target="_blank" class="text-muted small text-decoration-none">
                                    <?= htmlspecialchars($kontak['link_facebook']) ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <div class="col-lg-7">
                <?php
                $embed = $kontak['embed_maps'] ?? $kontak['gmaps_embed'] ?? '';
                if (!empty($embed)):
                    if (strpos($embed, '<iframe') !== false):
                        $embed = preg_replace('/width="[^"]*"/', 'width="100%"', $embed);
                        $embed = preg_replace('/height="[^"]*"/', 'height="450"', $embed);
                        $embed = preg_replace('/<iframe/', '<iframe style="border:0;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.1)"', $embed);
                        echo $embed;
                    else:
                ?>
                        <iframe src="<?= htmlspecialchars($embed) ?>"
                            width="100%" height="450"
                            style="border:0;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.1)"
                            allowfullscreen loading="lazy"></iframe>
                    <?php
                    endif;
                else:
                    ?>
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126748.74!2d109.2!3d-7.43!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7afe57a7b4f5e3%3A0x3025efb4e8df1490!2sPurwokerto!5e0!3m2!1sid!2sid!4v1"
                        width="100%" height="450"
                        style="border:0;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.1)"
                        allowfullscreen loading="lazy"></iframe>
                <?php endif; ?>

                <?php if (!empty($kontak['link_maps'])): ?>
                    <div class="text-center mt-3">
                        <a href="<?= htmlspecialchars($kontak['link_maps']) ?>"
                            target="_blank" class="btn btn-outline-hijau btn-sm rounded-pill px-4">
                            <i class="bi bi-map me-1"></i>Buka di Google Maps
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php';
$db->close(); ?>