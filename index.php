<?php
// index.php - Form Registrasi Invnity x Beautylatory
// Penyimpanan: data.json

$dataFile = __DIR__ . '/data.json';
$errors = [];
$success = false;
$old = ['nama' => '', 'no_hp' => ''];

// Flash success dari redirect PRG
if (isset($_GET['sukses']) && $_GET['sukses'] == '1') {
    $success = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama  = trim($_POST['nama'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $old = ['nama' => $nama, 'no_hp' => $no_hp];

    // Validasi Nama
    if ($nama === '') {
        $errors['nama'] = 'Nama wajib diisi.';
    } elseif (mb_strlen($nama) < 3) {
        $errors['nama'] = 'Nama minimal 3 karakter.';
    } elseif (mb_strlen($nama) > 100) {
        $errors['nama'] = 'Nama maksimal 100 karakter.';
    }

    // Validasi No HP: boleh diawali + , hanya digit 9-14
    $hp_bersih = preg_replace('/[\s\-\.]/', '', $no_hp);
    if ($no_hp === '') {
        $errors['no_hp'] = 'No HP wajib diisi.';
    } elseif (!preg_match('/^\+?[0-9]{9,14}$/', $hp_bersih)) {
        $errors['no_hp'] = 'No HP harus 9-14 digit angka. Contoh: 081234567890';
    }

    // Simpan jika valid
    if (empty($errors)) {
        $data = [];
        if (file_exists($dataFile)) {
            $json = file_get_contents($dataFile);
            $decoded = json_decode($json, true);
            if (is_array($decoded)) $data = $decoded;
        }

        // Cegah duplikat No HP
        foreach ($data as $row) {
            if (isset($row['no_hp']) && $row['no_hp'] === $hp_bersih) {
                $errors['no_hp'] = 'No HP ini sudah terdaftar.';
                break;
            }
        }

        if (empty($errors)) {
            $data[] = [
                'id'         => count($data) > 0 ? (max(array_column($data, 'id')) + 1) : 1,
                'nama'       => htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'),
                'no_hp'      => $hp_bersih,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
            header('Location: index.php?sukses=1');
            exit;
        }
    }
}

function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Form Registrasi - Invnity x Beautylatory</title>
<style>
  * { box-sizing: border-box; margin:0; padding:0; font-family: 'Segoe UI', system-ui, -apple-system, Arial, sans-serif; }
  html { -webkit-text-size-adjust:100%; }
  img { max-width:100%; height:auto; display:block; }
  body {
    min-height: 100vh;
    min-height: 100svh;
    min-height: 100dvh;
    background:#0a2e1f;
    display:flex; align-items:center; justify-content:center;
    padding:0;
  }
  /* ===== STAGE 9:16 — kunci di desktop & tablet ===== */
  .stage {
    position:relative;
    width:min(100vw, calc(100dvh * 9 / 16));
    height:min(100dvh, calc(100vw * 16 / 9));
    aspect-ratio:9 / 16;
    background-color:#fbf8e7;
    background-image:url('bg-mobile.png');
    background-repeat:no-repeat;
    background-position:center center;
    background-size:cover;
    overflow-y:auto;
    overflow-x:hidden;
    box-shadow:0 0 80px rgba(0,0,0,.5);
    display:flex; flex-direction:column;
  }
  .form-wrap {
    width:100%;
    max-width:380px;
    margin:0 auto;
    padding:28px 24px 24px;
    /* dorong konten ke bawah */
    padding-top:clamp(70px, 14vh, 130px);
  }
  .logos {
    display:flex; align-items:center; justify-content:center;
    gap:10px; flex-wrap:nowrap;
    margin-bottom:10px;
  }
  .logos img {
    background:transparent;
    border-radius:0;
    padding:0;
    object-fit:contain;
    height:auto;
    filter:drop-shadow(0 2px 6px rgba(0,0,0,.12));
  }
  .logos img.logo-invnity { height:clamp(44px, 12vw, 58px); width:auto; max-width:52%; }
  .logos img.logo-beauty { height:clamp(38px, 10vw, 50px); width:auto; max-width:38%; }
  .divider-x {
    color:#0b4d2e; font-weight:800; font-size:clamp(18px, 5vw, 22px); line-height:1;
  }
  .page-title {
    text-align:center;
    color:#0b4d2e;
    font-size:clamp(18px, 5vw, 22px);
    font-weight:800;
    letter-spacing:.3px;
    margin:2px 0 18px;
    line-height:1.3;
  }
  form { width:100%; }
  label { display:block; font-weight:700; font-size:14px; color:#0b4d2e; margin:14px 0 6px; }
  input {
    width:100%; padding:13px 14px; font-size:16px;
    border:2px solid #0b4d2e; border-radius:12px; outline:none;
    transition:.15s; background:rgba(255,255,255,.95); max-width:100%;
  }
  input:focus { border-color:#0b4d2e; box-shadow:0 0 0 3px rgba(11,77,46,.15); background:#fff; }
  .input-error { border-color:#d92d20 !important; }
  .err { color:#d92d20; font-size:13px; margin-top:5px; font-weight:600; }
  .btn {
    width:100%; margin-top:22px; padding:14px;
    background:#0b4d2e; color:#fff; border:none; border-radius:12px;
    font-size:16px; font-weight:800; cursor:pointer; transition:.15s;
    min-height:48px; touch-action:manipulation;
  }
  .btn:hover { background:#0e613a; transform:translateY(-1px); }
  .btn:active { transform:scale(.99); }
  a.btn { display:block; text-align:center; text-decoration:none; }
  /* ===== Thanks state: form hilang, muncul animasi checklist ===== */
  .thanks { text-align:center; padding:8px 0 4px; animation:thanksIn .4s ease; }
  .thanks h2 { color:#0b4d2e; font-size:clamp(18px,5vw,22px); margin:6px 0 6px; line-height:1.35; }
  .thanks p { color:#4b6258; font-size:14px; margin-bottom:6px; }
  .check-wrap { width:112px; height:112px; margin:22px auto 14px; animation:pop .45s ease; }
  .check-wrap svg { width:100%; height:100%; }
  .check-circle { stroke:#0b4d2e; stroke-width:4; fill:#ffffff; stroke-dasharray:302; stroke-dashoffset:302; animation:drawCircle .7s ease forwards; }
  .check-mark { stroke:#0b4d2e; stroke-width:7; fill:none; stroke-linecap:round; stroke-linejoin:round; stroke-dasharray:70; stroke-dashoffset:70; animation:drawCheck .45s .65s ease forwards; }
  @keyframes drawCircle { to { stroke-dashoffset:0; } }
  @keyframes drawCheck { to { stroke-dashoffset:0; } }
  @keyframes pop { 0% { transform:scale(.5); opacity:0; } 100% { transform:scale(1); opacity:1; } }
  @keyframes thanksIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:none; } }
  .success {
    background:#e6f4ea; border:1.5px solid #34a853; color:#0b4d2e;
    padding:12px 14px; border-radius:12px; font-size:14px; margin-bottom:16px; text-align:center;
  }

  /* ===== HP: full-bleed, BG tetap cover center ===== */
  @media (max-width:480px){
    body { background:#fbf8e7; }
    .stage {
      width:100vw;
      height:100dvh;
      aspect-ratio:auto;
      box-shadow:none;
      background-position:50% 50%;
      background-size:cover;
    }
    .form-wrap { padding-top:max(90px, env(safe-area-inset-top)); padding-bottom:max(24px, env(safe-area-inset-bottom)); }
  }
</style>
</head>
<body>
  <div class="stage">
  <main class="form-wrap">
    <div class="logos">
      <img src="invnity.png" alt="Invnity" class="logo-invnity">
      <span class="divider-x">×</span>
      <img src="logo-beautylatory.png" alt="Beautylatory" class="logo-beauty">
    </div>
    <?php if ($success): ?>
      <div class="thanks">
        <div class="check-wrap">
          <svg viewBox="0 0 100 100" aria-hidden="true">
            <circle class="check-circle" cx="50" cy="50" r="46" />
            <path class="check-mark" d="M32 51 L45 64 L69 38" />
          </svg>
        </div>
        <h2>Terima kasih sudah mengisi.</h2>
        <p>Data kamu sudah tersimpan.</p>
        <a href="index.php" class="btn">Isi Form Lagi</a>
      </div>
    <?php else: ?>
    <h1 class="page-title">Data Visitor Beautylatory</h1>
    <form action="index.php" method="POST" novalidate>
      <label for="nama">Nama Lengkap</label>
      <input type="text" id="nama" name="nama" placeholder="Contoh: Putri Ayu"
             value="<?= e($old['nama']) ?>" class="<?= isset($errors['nama']) ? 'input-error' : '' ?>" required>
      <?php if (isset($errors['nama'])): ?><div class="err"><?= e($errors['nama']) ?></div><?php endif; ?>

      <label for="no_hp">No HP / WhatsApp</label>
      <input type="tel" id="no_hp" name="no_hp" placeholder="Contoh: 081234567890" inputmode="numeric" maxlength="15"
             value="<?= e($old['no_hp']) ?>" class="<?= isset($errors['no_hp']) ? 'input-error' : '' ?>" required>
      <?php if (isset($errors['no_hp'])): ?><div class="err"><?= e($errors['no_hp']) ?></div><?php endif; ?>

      <button type="submit" class="btn">Kirim Data</button>
    </form>
    <?php endif; ?>
  </main>
  </div>
</body>
</html>
