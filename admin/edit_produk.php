<?php
// =====================================================
// PROSES POST DULUAN sebelum require header_admin.php
// agar header('Location: ...') masih bisa dipanggil
// =====================================================
session_start();
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/database.php';

// ── Cloudinary helper (tanpa SDK, pakai HTTP langsung) ──
function uploadToCloudinary($fileTmpPath, $fileName) {
    $cloudName = 'dwzvzz5af';
    $apiKey    = '815678683111228';
    $apiSecret = 'L6AQtz2C7hUZVhqcBdbUvnj1uYo';

    $timestamp = time();
    $params    = ['timestamp' => $timestamp];
    ksort($params);
    $paramStr  = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    $signature = sha1($paramStr . $apiSecret);

    $url  = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";
    $post = [
        'file'      => new CURLFile($fileTmpPath, mime_content_type($fileTmpPath), $fileName),
        'api_key'   => $apiKey,
        'timestamp' => $timestamp,
        'signature' => $signature,
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    $err    = curl_error($ch);
    curl_close($ch);

    if($err) return null;
    $data = json_decode($result, true);
    return $data['secure_url'] ?? null;
}

// ── Hapus gambar dari Cloudinary pakai public_id ──
function deleteFromCloudinary($publicId) {
    $cloudName = 'dwzvzz5af';
    $apiKey    = '815678683111228';
    $apiSecret = 'L6AQtz2C7hUZVhqcBdbUvnj1uYo';

    $timestamp = time();
    $params    = ['public_id' => $publicId, 'timestamp' => $timestamp];
    ksort($params);
    $paramStr  = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    $signature = sha1($paramStr . $apiSecret);

    $url  = "https://api.cloudinary.com/v1_1/{$cloudName}/image/destroy";
    $post = [
        'public_id' => $publicId,
        'api_key'   => $apiKey,
        'timestamp' => $timestamp,
        'signature' => $signature,
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

// ── Ambil public_id dari URL Cloudinary ──
function getPublicIdFromUrl($url) {
    // URL bentuk: https://res.cloudinary.com/cloud/image/upload/v123456/public_id.ext
    if(!$url || strpos($url, 'cloudinary.com') === false) return null;
    $parts = explode('/upload/', $url);
    if(count($parts) < 2) return null;
    $path = preg_replace('/^v\d+\//', '', $parts[1]); // hapus versi v123456/
    return preg_replace('/\.[^.]+$/', '', $path);      // hapus ekstensi
}

$db    = getDB();
$id    = (int)($_GET['id'] ?? 0);
$error = '';

$produk = $db->query("SELECT * FROM produk WHERE id_produk = $id")->fetch_assoc();
if(!$produk) { header('Location: produk.php'); exit; }

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = trim($_POST['nama_produk'] ?? '');
    $id_kat    = (int)($_POST['id_kategori'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $ukuran    = trim($_POST['ukuran'] ?? '');
    $warna     = trim($_POST['warna'] ?? '');
    $foto_sc   = $produk['foto_size_chart'] ?? '';

    if(!$nama || !$id_kat) {
        $error = 'Nama produk dan kategori wajib diisi!';
    } else {

        // ── Upload foto size chart baru ke Cloudinary ──
        if(isset($_FILES['foto_size_chart']) && $_FILES['foto_size_chart']['error'] === 0) {
            $allowed = ['jpg','jpeg','png','webp'];
            $ext = strtolower(pathinfo($_FILES['foto_size_chart']['name'], PATHINFO_EXTENSION));
            if(in_array($ext, $allowed) && $_FILES['foto_size_chart']['size'] <= 3*1024*1024) {
                // Hapus size chart lama dari Cloudinary
                $oldPid = getPublicIdFromUrl($foto_sc);
                if($oldPid) deleteFromCloudinary($oldPid);

                $url = uploadToCloudinary($_FILES['foto_size_chart']['tmp_name'], $_FILES['foto_size_chart']['name']);
                if($url) $foto_sc = $url;
                else $error = 'Upload size chart ke Cloudinary gagal.';
            }
        }

        // ── Hapus foto size chart jika dicentang ──
        if(!$error && isset($_POST['hapus_size_chart']) && $foto_sc) {
            $oldPid = getPublicIdFromUrl($foto_sc);
            if($oldPid) deleteFromCloudinary($oldPid);
            $foto_sc = '';
        }

        // ── Hapus foto produk yang dicentang ──
        if(!$error && isset($_POST['hapus_foto']) && is_array($_POST['hapus_foto'])) {
            foreach($_POST['hapus_foto'] as $id_foto) {
                $id_foto = (int)$id_foto;
                $row = $db->query("SELECT nama_file FROM produk_foto WHERE id_foto=$id_foto")->fetch_assoc();
                if($row) {
                    $pid = getPublicIdFromUrl($row['nama_file']);
                    if($pid) deleteFromCloudinary($pid);
                    $db->query("DELETE FROM produk_foto WHERE id_foto=$id_foto");
                }
            }
        }

        // ── Upload foto baru ke Cloudinary ──
        if(!$error && isset($_FILES['foto_baru']) && is_array($_FILES['foto_baru']['name'])) {
            $allowed = ['jpg','jpeg','png','webp'];

            // Hitung urutan dari database (lebih aman dari count($fotos))
            $result     = $db->query("SELECT COALESCE(MAX(urutan),-1)+1 AS next_urutan FROM produk_foto WHERE id_produk=$id");
            $row        = $result->fetch_assoc();
            $urutan_max = (int)$row['next_urutan'];

            foreach($_FILES['foto_baru']['name'] as $i => $fname) {
                if($_FILES['foto_baru']['error'][$i] !== 0) continue;
                $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
                if(!in_array($ext, $allowed)) continue;
                if($_FILES['foto_baru']['size'][$i] > 3*1024*1024) continue;

                $url = uploadToCloudinary($_FILES['foto_baru']['tmp_name'][$i], $fname);
                if(!$url) continue;

                $stmt2 = $db->prepare("INSERT INTO produk_foto (id_produk, nama_file, urutan) VALUES (?,?,?)");
                $stmt2->bind_param("isi", $id, $url, $urutan_max);
                $stmt2->execute();
                $urutan_max++;
            }
        }

        if(!$error) {
            // ── Update foto utama dari foto pertama yang tersisa ──
            $first = $db->query("SELECT nama_file FROM produk_foto WHERE id_produk=$id ORDER BY urutan ASC LIMIT 1")->fetch_assoc();
            $foto_utama = $first ? $first['nama_file'] : ($produk['foto'] ?? '');

            $stmt = $db->prepare("UPDATE produk SET id_kategori=?, nama_produk=?, deskripsi=?, ukuran=?, warna=?, foto_size_chart=?, foto=? WHERE id_produk=?");
            $stmt->bind_param("issssssi", $id_kat, $nama, $deskripsi, $ukuran, $warna, $foto_sc, $foto_utama, $id);
            if(!$stmt->execute()) {
                $error = 'Gagal update produk: ' . $stmt->error;
            } else {
                header('Location: produk.php?msg=edit');
                exit;
            }
        }
    }
}

// ── Baru tampilkan HTML ──
$page_title = 'Edit Produk';
require_once 'header_admin.php';

$kategori_list = $db->query("SELECT * FROM kategori ORDER BY nama_kategori");

// Refresh foto list
$foto_list = $db->query("SELECT * FROM produk_foto WHERE id_produk=$id ORDER BY urutan ASC");
$fotos = [];
while($f = $foto_list->fetch_assoc()) $fotos[] = $f;
?>

<?php if($error): ?>
<div class="alert alert-danger rounded-3"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="produk.php" class="btn btn-sm btn-outline-secondary rounded-pill"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h5 class="fw-bold mb-0">Edit Produk</h5>
        <p class="text-muted small mb-0">Perbarui informasi produk</p>
    </div>
</div>

<form method="POST" enctype="multipart/form-data">
<div class="row g-4">
    <div class="col-lg-8">
        <!-- INFO DASAR -->
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">Informasi Produk</h6>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" name="nama_produk" class="form-control rounded-3"
                        value="<?= htmlspecialchars($produk['nama_produk']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                    <select name="id_kategori" class="form-select rounded-3" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php while($kat = $kategori_list->fetch_assoc()): ?>
                        <option value="<?= $kat['id_kategori'] ?>"
                            <?= $kat['id_kategori']==$produk['id_kategori']?'selected':'' ?>>
                            <?= htmlspecialchars($kat['nama_kategori']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control rounded-3" rows="4"><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="bi bi-rulers me-1"></i>Ukuran Tersedia</label>
                    <input type="text" name="ukuran" class="form-control rounded-3"
                        placeholder="Contoh: S, M, L, XL, XXL"
                        value="<?= htmlspecialchars($produk['ukuran'] ?? '') ?>">
                    <div class="form-text">Pisahkan dengan koma</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="bi bi-palette me-1"></i>Warna Tersedia</label>
                    <input type="text" name="warna" class="form-control rounded-3"
                        placeholder="Contoh: Hitam, Putih, Navy, Merah"
                        value="<?= htmlspecialchars($produk['warna'] ?? '') ?>">
                    <div class="form-text">Pisahkan dengan koma</div>
                </div>
            </div>
        </div>

        <!-- FOTO PRODUK EXISTING -->
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-1">Foto Produk</h6>
                <p class="text-muted small mb-3">Centang foto untuk dihapus. Foto pertama jadi foto utama.</p>

                <?php if(!empty($fotos)): ?>
                <div class="d-flex flex-wrap gap-3 mb-3">
                    <?php foreach($fotos as $fi => $f): ?>
                    <div class="position-relative">
                        <?php
                        // nama_file sekarang berupa URL Cloudinary
                        $imgSrc = htmlspecialchars($f['nama_file']);
                        ?>
                        <img src="<?= $imgSrc ?>"
                            class="rounded-3" style="width:100px;height:100px;object-fit:cover">
                        <?php if($fi === 0): ?>
                        <span class="badge position-absolute top-0 start-0 m-1"
                            style="background:#2d5a27;font-size:.65rem">Utama</span>
                        <?php endif; ?>
                        <div class="position-absolute bottom-0 end-0 m-1">
                            <input type="checkbox" name="hapus_foto[]"
                                value="<?= $f['id_foto'] ?>"
                                class="form-check-input" title="Hapus foto ini"
                                style="width:18px;height:18px;cursor:pointer"
                                onclick="this.parentElement.parentElement.style.opacity=this.checked?'0.4':'1'">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-text text-warning mb-3">
                    <i class="bi bi-info-circle me-1"></i>Foto yang dicentang akan dihapus saat simpan.
                </div>
                <?php endif; ?>

                <label class="form-label fw-semibold small">Tambah Foto Baru</label>
                <input type="file" name="foto_baru[]" class="form-control rounded-3"
                    accept="image/jpeg,image/png,image/webp" multiple
                    onchange="previewFotosBaru(event)">
                <div class="form-text">Bisa pilih beberapa foto sekaligus. Maks 3MB per foto.</div>
                <div id="newFotoPreview" class="d-flex flex-wrap gap-2 mt-2"></div>
            </div>
        </div>

        <!-- FOTO SIZE CHART -->
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-1"><i class="bi bi-table me-1"></i>Foto Size Chart</h6>
                <p class="text-muted small mb-3">Upload foto tabel ukuran khusus produk ini.</p>

                <?php if(!empty($produk['foto_size_chart'])): ?>
                <div class="mb-3">
                    <img src="<?= htmlspecialchars($produk['foto_size_chart']) ?>"
                        class="img-fluid rounded-3" style="max-height:200px;object-fit:contain">
                    <div class="mt-2">
                        <label class="d-flex align-items-center gap-2 text-danger small" style="cursor:pointer">
                            <input type="checkbox" name="hapus_size_chart" value="1">
                            Hapus foto size chart ini
                        </label>
                    </div>
                </div>
                <?php endif; ?>

                <input type="file" name="foto_size_chart" class="form-control rounded-3"
                    accept="image/jpeg,image/png,image/webp" onchange="previewSC(event)">
                <div class="form-text">Upload baru untuk mengganti. Format: JPG, PNG, WEBP. Maks 3MB.</div>
                <img id="scPreview" src="" class="mt-3 rounded-3 d-none" style="max-height:200px;object-fit:contain">
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-3 pb-4">
    <button type="submit" class="btn rounded-pill px-5 fw-semibold py-2" style="background:#2d5a27;color:white">
        <i class="bi bi-check-lg me-2"></i>Simpan Perubahan
    </button>
    <a href="produk.php" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
</div>
</form>

<script>
function previewFotosBaru(e) {
    const container = document.getElementById('newFotoPreview');
    container.innerHTML = '';
    Array.from(e.target.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = ev => {
            const img = document.createElement('img');
            img.src = ev.target.result;
            img.className = 'rounded-3';
            img.style.cssText = 'width:80px;height:80px;object-fit:cover';
            container.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}
function previewSC(e) {
    const file = e.target.files[0];
    if(file) {
        const reader = new FileReader();
        reader.onload = ev => {
            const img = document.getElementById('scPreview');
            img.src = ev.target.result;
            img.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }
}
</script>

<?php require_once 'footer_admin.php'; $db->close(); ?>