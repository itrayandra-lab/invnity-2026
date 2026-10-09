<?php
// dashboard.php - Dashboard data pendaftar (sumber: data.json)
$dataFile = __DIR__ . '/data.json';
$data = [];
if (file_exists($dataFile)) {
    $decoded = json_decode(file_get_contents($dataFile), true);
    if (is_array($decoded)) $data = $decoded;
}

// Hapus satu data
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $data = array_values(array_filter($data, fn($r) => (int)($r['id'] ?? 0) !== $id));
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    header('Location: dashboard.php?msg=hapus');
    exit;
}

// Hapus semua
if (isset($_GET['hapus_semua']) && $_GET['hapus_semua'] == '1') {
    file_put_contents($dataFile, json_encode([], JSON_PRETTY_PRINT), LOCK_EX);
    header('Location: dashboard.php?msg=hapus_semua');
    exit;
}

// Search
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $data = array_values(array_filter($data, function($r) use ($q) {
        return stripos($r['nama'] ?? '', $q) !== false || stripos($r['no_hp'] ?? '', $q) !== false;
    }));
}

// Urutkan terbaru dulu
usort($data, fn($a,$b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

$total = count($data);

// Export Excel / CSV (native, tanpa library)
if (isset($_GET['export'])) {
    $mode = $_GET['export'];
    $filename = 'data-visitor-beautylatory-' . date('Ymd-His');

    if ($mode === 'csv') {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        echo "\xEF\xBB\xBF"; // BOM biar Excel baca UTF-8
        $out = fopen('php://output', 'w');
        fputcsv($out, ['No', 'Nama', 'No HP', 'Waktu Isi'], ';', '"', '\\');
        $no = 1;
        foreach ($data as $row) {
            fputcsv($out, [$no++, html_entity_decode($row['nama'] ?? '', ENT_QUOTES, 'UTF-8'), "'" . ($row['no_hp'] ?? ''), $row['created_at'] ?? ''], ';', '"', '\\');
        }
        fclose($out);
        exit;
    }

    // Default: .xls via HTML table (langsung kebuka di Excel)
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    echo "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel'><head><meta charset='UTF-8'></head><body>";
    echo "<table border='1'><tr><th>No</th><th>Nama</th><th>No HP</th><th>Waktu Isi</th></tr>";
    $no = 1;
    foreach ($data as $row) {
        echo "<tr><td>" . $no++ . "</td><td>" . htmlspecialchars(html_entity_decode($row['nama'] ?? '', ENT_QUOTES, 'UTF-8')) . "</td><td style='mso-number-format:\\@'>" . htmlspecialchars($row['no_hp'] ?? '') . "</td><td>" . htmlspecialchars($row['created_at'] ?? '') . "</td></tr>";
    }
    echo "</table></body></html>";
    exit;
}

function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Invnity x Beautylatory</title>
<style>
 *{box-sizing:border-box;margin:0;padding:0;font-family:'Segoe UI',system-ui,Arial,sans-serif}
 html{-webkit-text-size-adjust:100%}
 img{max-width:100%;height:auto;display:block}
 body{background:#f1f5f2;min-height:100vh;min-height:100svh;min-height:100dvh}
  .header{background:#0b4d2e;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;position:relative}
  .brand{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
  .brand img{background:#fff;border-radius:10px;padding:4px 8px;object-fit:contain}
  .brand img.l1{height:40px;width:auto;max-width:140px}.brand img.l2{height:40px;width:auto;max-width:120px}
  .header-title{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);color:#fff;font-size:clamp(16px,4.5vw,18px);margin:0;white-space:nowrap}
 .nav{display:flex;gap:8px;flex-wrap:wrap}
 .nav a{color:#0b4d2e;background:#ffde00;font-weight:800;text-decoration:none;padding:10px 16px;border-radius:10px;font-size:14px;display:inline-block;min-height:42px;line-height:22px}
 .nav a.ghost{background:#fff}
 .wrap{max-width:960px;margin:24px auto;padding:0 16px;padding-bottom:max(24px, env(safe-area-inset-bottom))}
 .stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px}
 .stat{background:#fff;border-radius:14px;padding:16px;border:1.5px solid #dce8e1;min-width:0}
 .stat b{font-size:clamp(20px,6vw,26px);color:#0b4d2e;display:block;word-break:break-word}
 .stat span{font-size:13px;color:#6b7d72}
 .toolbar{background:#fff;border-radius:14px;padding:14px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;border:1.5px solid #dce8e1;margin-bottom:14px}
 .toolbar form{display:flex;gap:8px;flex:1;flex-wrap:wrap;min-width:0}
 .toolbar input{flex:1;min-width:160px;padding:11px 12px;border:2px solid #d6e2d9;border-radius:10px;font-size:16px}
 .btn{padding:11px 14px;border-radius:10px;border:none;font-weight:700;cursor:pointer;font-size:14px;text-decoration:none;display:inline-block;min-height:42px;touch-action:manipulation}
  .btn-dark{background:#0b4d2e;color:#fff}
  .btn-excel{background:#217346;color:#fff}
  .btn-red{background:#fee4e2;color:#b42318}
 .btn-green{background:#e6f4ea;color:#0b4d2e;border:1px solid #34a853}
 .msg{background:#e6f4ea;border:1px solid #34a853;padding:10px 14px;border-radius:10px;margin-bottom:12px;font-size:14px}
 .table-wrap{background:#fff;border-radius:14px;overflow:auto;border:1.5px solid #dce8e1;-webkit-overflow-scrolling:touch}
 table{width:100%;border-collapse:collapse;min-width:640px}
 th{background:#0b4d2e;color:#fff;text-align:left;padding:12px;font-size:14px;white-space:nowrap}
 td{padding:12px;border-top:1px solid #e6efe9;font-size:14px}
 tr:hover td{background:#f7fbf8}
 .empty{padding:30px;text-align:center;color:#6b7d72}
 .wa{color:#0b4d2e;font-weight:700;text-decoration:none;word-break:break-all}
 /* ===== TABLET ===== */
 @media(max-width:768px){
   .header{padding:14px 16px}
   .wrap{margin:16px auto;padding:0 12px}
   .stats{grid-template-columns:repeat(3,1fr);gap:10px}
   .stat{padding:12px}
 }
 /* ===== MOBILE: ubah tabel jadi kartu ===== */
  @media(max-width:640px){
    .header{flex-direction:column;align-items:stretch;text-align:center}
    .header-title{position:static;transform:none;order:3;width:100%}
    .brand{justify-content:center;order:1}
    .brand img.l1{height:34px}.brand img.l2{height:34px}
    .nav{justify-content:center;order:2}
   .nav a{flex:1;text-align:center;padding:10px 8px;font-size:13px}
   .stats{grid-template-columns:1fr 1fr;gap:10px}
   .stats .stat:last-child{grid-column:1/-1}
   .toolbar{flex-direction:column;align-items:stretch}
   .toolbar form{width:100%}
   .toolbar input{width:100%;min-width:0}
   .toolbar .btn, .toolbar button{width:100%;text-align:center}
   .table-wrap{border:none;background:transparent;overflow:visible}
   table, thead, tbody, th, td, tr{display:block;width:100%;min-width:0}
   thead{display:none}
   tbody{display:flex;flex-direction:column;gap:10px}
   tbody tr{background:#fff;border:1.5px solid #dce8e1;border-radius:12px;padding:12px;box-shadow:0 2px 8px rgba(11,77,46,.06)}
   tbody td{border:none;padding:4px 0;display:flex;justify-content:space-between;gap:10px;align-items:center}
   tbody td::before{content:attr(data-label);font-weight:700;color:#0b4d2e;font-size:12px;text-transform:uppercase;flex-shrink:0}
   tbody td:first-child::before{content:"No"}
   tr:hover td{background:transparent}
   .btn-red{width:100%;text-align:center}
 }
  @media(max-width:380px){
    .stats{grid-template-columns:1fr}
  }
</style>
</head>
<body>
<div class="header">
  <div class="brand">
    <img src="invnity.png" class="l1" alt="Invnity">
    <img src="logo-beautylatory.png" class="l2" alt="Beautylatory">
  </div>
  <h2 class="header-title">Dashboard Pendaftar</h2>
  <div class="nav">
    <a href="index.php">+ Form Baru</a>
  </div>
</div>

<div class="wrap">
  <?php if(isset($_GET['msg'])): ?><div class="msg">✅ Berhasil: <?= e($_GET['msg']) ?></div><?php endif; ?>

  <div class="stats">
    <div class="stat"><b><?= $total ?></b><span>Total Pendaftar</span></div>
    <div class="stat"><b><?= count(array_unique(array_column($data,'no_hp'))) ?></b><span>No HP Unik</span></div>
    <div class="stat"><b><?= $total>0 ? e(substr($data[0]['created_at'] ?? '-',0,10)) : '-' ?></b><span>Terakhir Input</span></div>
  </div>

  <div class="toolbar">
    <form method="GET" style="display:flex;gap:8px;flex:1;flex-wrap:wrap">
      <input type="text" name="q" placeholder="Cari nama / no HP..." value="<?= e($q) ?>">
      <button class="btn btn-dark" type="submit">Cari</button>
      <?php if($q!==''): ?><a class="btn btn-green" href="dashboard.php">Reset</a><?php endif; ?>
    </form>
    <?php if($total>0): ?>
    <a class="btn btn-excel" href="dashboard.php?export=xls<?= $q!=='' ? '&q='.urlencode($q) : '' ?>">⬇ Export Excel</a>
    <a class="btn btn-green" href="dashboard.php?export=csv<?= $q!=='' ? '&q='.urlencode($q) : '' ?>">CSV</a>
    <a class="btn btn-red" href="dashboard.php?hapus_semua=1" onclick="return confirm('Hapus SEMUA data?')">Hapus Semua</a>
    <?php endif; ?>
  </div>

  <div class="table-wrap">
  <?php if($total===0): ?>
    <div class="empty">Belum ada data. <a href="index.php">Isi form dulu →</a></div>
  <?php else: ?>
    <table>
      <thead><tr><th>No</th><th>Nama</th><th>No HP</th><th>Waktu Isi</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php $no=1; foreach($data as $row): ?>
        <tr>
          <td data-label="No"><?= $no++ ?></td>
          <td data-label="Nama"><b><?= e($row['nama'] ?? '') ?></b></td>
          <td data-label="No HP"><a class="wa" href="https://wa.me/<?= e(ltrim(preg_replace('/[^0-9]/','',$row['no_hp'] ?? ''),'0')) ?>" target="_blank"><?= e($row['no_hp'] ?? '') ?></a></td>
          <td data-label="Waktu"><?= e($row['created_at'] ?? '') ?></td>
          <td data-label="Aksi"><a class="btn btn-red" href="dashboard.php?hapus=<?= (int)($row['id'] ?? 0) ?>" onclick="return confirm('Hapus <?= e($row['nama'] ?? '') ?>?')">Hapus</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
  </div>
</div>
</body>
</html>
