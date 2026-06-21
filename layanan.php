<?php
$page_title = 'Layanan';
require_once 'includes/header.php';
$db = getDB();
$layanan_list = $db->query("SELECT * FROM layanan ORDER BY urutan ASC");
$jumlah = $layanan_list ? $layanan_list->num_rows : 0;
$lk = $db->query("SELECT * FROM layanan_konten LIMIT 1")->fetch_assoc();
$header_judul = $lk['header_judul'] ?? 'Layanan Kami';
$header_desk  = $lk['header_deskripsi'] ?? 'Kami siap melayani berbagai kebutuhan bordir Anda';
?>

<div style="background: linear-gradient(135deg, var(--hijau-tua), var(--hijau-muda)); padding: 80px 0 60px;" class="text-white text-center">
    <div class="container">
        <h1 class="display-5 fw-bold mb-2"><?= htmlspecialchars($header_judul) ?></h1>
        <p class="opacity-75 mb-0"><?= htmlspecialchars($header_desk) ?></p>
    </div>
</div>

<section class="py-5">
    <div class="container py-3">
        <?php if ($jumlah == 0): ?>
            <div class="alert alert-warning">Belum ada data layanan. Tambahkan di admin panel.</div>
        <?php else: ?>
            <div class="row g-4">
                <?php while ($l = $layanan_list->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 h-100 shadow-sm rounded-4 p-4" style="transition:.3s" onmouseover="this.style.transform='translateY(-6px)'" onmouseout="this.style.transform=''">
                            <div class="mb-3 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:60px;height:60px;background:rgba(45,90,39,0.1)">
                                <i class="bi <?= htmlspecialchars($l['ikon']) ?> fs-4" style="color:var(--hijau-tua)"></i>
                            </div>
                            <h5 class="fw-bold mb-2"><?= htmlspecialchars($l['judul']) ?></h5>
                            <p class="text-muted small mb-0"><?= htmlspecialchars($l['deskripsi']) ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</section>


</div>
</section>

<?php require_once 'includes/footer.php';
$db->close(); ?>