<?php
$page_title = 'Tambah Produk';
require_once 'header_admin.php';
$db = getDB();

$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = trim($_POST['nama_produk'] ?? '');
    $id_kat    = (int)($_POST['id_kategori'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $ukuran    = trim($_POST['ukuran'] ?? '');
    $warna     = trim($_POST['warna'] ?? '');
    $foto_sc   = '';

    if(!$nama || !$id_kat) {
        $error = 'Nama produk dan kategori wajib diisi!';
    } else {
        // Upload foto size chart
        if(isset($_FILES['foto_size_chart']) && $_FILES['foto_size_chart']['error'] === 0) {
            $allowed = ['jpg','jpeg','png','webp'];
            $ext = strtolower(pathinfo($_FILES['foto_size_chart']['name'], PATHINFO_EXTENSION));
            if(in_array($ext, $allowed) && $_FILES['foto_size_chart']['size'] <= 3*1024*1024) {
                $foto_sc = 'sizechart_'.time().'_'.rand(100,999).'.'.$ext;
                move_uploaded_file($_FILES['foto_size_chart']['tmp_name'], '../public/uploads/'.$foto_sc);
            }
        }

        // Insert produk
        $stmt = $db->prepare("INSERT INTO produk (id_kategori, nama_produk, deskripsi, ukuran, warna, foto_size_chart, foto) VALUES (?,?,?,?,?,?,?)");
        $foto_utama = '';
        $stmt->bind_param("issssss", $id_kat, $nama, $deskripsi, $ukuran, $warna, $foto_sc, $foto_utama);
        $stmt->execute();
        $new_id = $db->insert_id;

        // Upload multiple foto produk
        if(isset($_FILES['foto_produk']) && is_array($_FILES['foto_produk']['name'])) {
            $allowed = ['jpg','jpeg','png','webp'];
            $urutan = 0;
            foreach($_FILES['foto_produk']['name'] as $i => $fname) {
                if($_FILES['foto_produk']['error'][$i] !== 0) continue;
                $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
                if(!in_array($ext, $allowed)) continue;
                if($_FILES['foto_produk']['size'][$i] > 3*1024*1024) continue;
                $new_name = 'produk_'.$new_id.'_'.time().'_'.$urutan.'.'.$ext;
                move_uploaded_file($_FILES['foto_produk']['tmp_name'][$i], '../public/uploads/'.$new_name);
                $stmt2 = $db->prepare("INSERT INTO produk_foto (id_produk, nama_file, urutan) VALUES (?,?,?)");
                $stmt2->bind_param("isi", $new_id, $new_name, $urutan);
                $stmt2->execute();
                // Set foto utama dari foto pertama
                if($urutan === 0) {
                    $db->query("UPDATE produk SET foto='$new_name' WHERE id_produk=$new_id");
                }
                $urutan++;
            }
        }

        header('Location: produk.php?msg=tambah');
        exit;
    }
}

$kategori_list = $db->query("SELECT * FROM kategori ORDER BY nama_kategori");
?>

<?php if($error): ?>
<div class="alert alert-danger rounded-3"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="produk.php" class="btn btn-sm btn-outline-secondary rounded-pill"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h5 class="fw-bold mb-0">Tambah Produk Baru</h5>
        <p class="text-muted small mb-0">Isi form berikut untuk menambahkan produk baru</p>
    </div>
</div>

<form method="POST" enctype="multipart/form-data">
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">Informasi Produk</h6>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" name="nama_produk" class="form-control rounded-3"
                        placeholder="Contoh: Seragam PDH Custom"
                        value="<?= htmlspecialchars($_POST['nama_produk'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                    <select name="id_kategori" class="form-select rounded-3" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php while($kat = $kategori_list->fetch_assoc()): ?>
                        <option value="<?= $kat['id_kategori'] ?>"
                            <?= (isset($_POST['id_kategori']) && $_POST['id_kategori']==$kat['id_kategori'])?'selected':'' ?>>
                            <?= htmlspecialchars($kat['nama_kategori']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control rounded-3" rows="4"
                        placeholder="Deskripsikan produk ini..."><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-rulers me-1"></i>Ukuran Tersedia
                    </label>
                    <input type="text" name="ukuran" class="form-control rounded-3"
                        placeholder="Contoh: S, M, L, XL, XXL"
                        value="<?= htmlspecialchars($_POST['ukuran'] ?? '') ?>">
                    <div class="form-text">Pisahkan dengan koma</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-palette me-1"></i>Warna Tersedia
                    </label>
                    <input type="text" name="warna" class="form-control rounded-3"
                        placeholder="Contoh: Hitam, Putih, Navy, Merah"
                        value="<?= htmlspecialchars($_POST['warna'] ?? '') ?>">
                    <div class="form-text">Pisahkan dengan koma</div>
                </div>
            </div>
        </div>

        <!-- FOTO PRODUK MULTIPLE -->
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-1">Foto Produk</h6>
                <p class="text-muted small mb-3">Bisa upload lebih dari 1 foto. Foto pertama jadi foto utama.</p>
                <input type="file" name="foto_produk[]" class="form-control rounded-3"
                    accept="image/jpeg,image/png,image/webp"
                    multiple id="fotoInput" onchange="previewFotos(event)">
                <div class="form-text">Format: JPG, PNG, WEBP. Maks 3MB per foto.</div>
                <div id="fotoPreviewContainer" class="d-flex flex-wrap gap-2 mt-3"></div>
            </div>
        </div>

        <!-- FOTO SIZE CHART -->
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-1">
                    <i class="bi bi-table me-1"></i>Foto Size Chart
                </h6>
                <p class="text-muted small mb-3">Upload foto tabel ukuran khusus untuk produk ini.</p>
                <input type="file" name="foto_size_chart" class="form-control rounded-3"
                    accept="image/jpeg,image/png,image/webp" id="scInput" onchange="previewSC(event)">
                <div class="form-text">Format: JPG, PNG, WEBP. Maks 3MB.</div>
                <img id="scPreview" src="" class="mt-3 rounded-3 d-none" style="max-height:200px;max-width:100%;object-fit:contain">
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-3 pb-4">
    <button type="submit" class="btn rounded-pill px-5 fw-semibold py-2" style="background:#2d5a27;color:white">
        <i class="bi bi-check-lg me-2"></i>Simpan Produk
    </button>
    <a href="produk.php" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
</div>
</form>

<script>
function previewFotos(e) {
    const container = document.getElementById('fotoPreviewContainer');
    container.innerHTML = '';
    Array.from(e.target.files).forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = (ev) => {
            const wrap = document.createElement('div');
            wrap.className = 'position-relative';
            wrap.innerHTML = `
                <img src="${ev.target.result}" class="rounded-3"
                    style="width:100px;height:100px;object-fit:cover">
                ${i===0 ? '<span class="badge position-absolute top-0 start-0 m-1" style="background:#2d5a27;font-size:.65rem">Utama</span>' : ''}
            `;
            container.appendChild(wrap);
        };
        reader.readAsDataURL(file);
    });
}
function previewSC(e) {
    const file = e.target.files[0];
    if(file) {
        const reader = new FileReader();
        reader.onload = (ev) => {
            const img = document.getElementById('scPreview');
            img.src = ev.target.result;
            img.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }
}
</script>

<?php require_once 'footer_admin.php'; $db->close(); ?>