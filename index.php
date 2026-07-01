<?php
// Konfigurasi Database
$host = "localhost";
$db   = "parfum_db";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// ================= FUNGSI =================
function tokenize($t) {
    $t = strtolower($t);
    $t = preg_replace('/[^a-z\s]/', '', $t);
    return array_filter(explode(' ', $t));
}

function tf($terms) {
    $tf = [];
    foreach ($terms as $t) $tf[$t] = ($tf[$t] ?? 0) + 1;
    return $tf;
}

function idf($docs) {
    $idf = []; 
    $N = count($docs);
    foreach ($docs as $d) {
        foreach (array_unique($d) as $t) $idf[$t] = ($idf[$t] ?? 0) + 1;
    }
    foreach ($idf as $t => $df) $idf[$t] = log($N / $df);
    return $idf;
}

function tfidf($tf, $idf) {
    $v = [];
    foreach ($tf as $t => $f) $v[$t] = $f * ($idf[$t] ?? 0);
    return $v;
}

function cosine($a, $b) {
    $dot = $na = $nb = 0;
    foreach ($a as $t => $v) {
        $dot += $v * ($b[$t] ?? 0);
        $na += $v * $v;
    }
    foreach ($b as $v) $nb += $v * $v;
    return ($na && $nb) ? $dot / (sqrt($na) * sqrt($nb)) : 0;
}

// ================= AMBIL DATA =================
$stmt = $pdo->query("SELECT * FROM parfum ORDER BY id ASC");
$data = [];
while ($r = $stmt->fetch()) {
    $doc = $r['top_notes'] . ' ' . $r['middle_notes'] . ' ' . $r['base_notes'];
    $data[] = [
        'id'    => $r['id'],
        'nama'  => $r['nama_parfum'],
        'brand' => $r['brand'],
        'foto'  => $r['foto'],
        'doc'   => tokenize($doc)
    ];
}

// ================= LOGIKA SEARCH =================
$keyword = $_GET['aroma'] ?? '';
$recommendations = [];

if (!empty($keyword)) {
    $query_terms = tokenize($keyword);
    $docs = array_column($data, 'doc');
    $idf = idf($docs);
    $query_vec = tfidf(tf($query_terms), $idf);

    foreach ($data as $d) {
        $doc_vec = tfidf(tf($d['doc']), $idf);
        $score = cosine($query_vec, $doc_vec);
        if ($score > 0) {
            $recommendations[] = ['data' => $d, 'score' => $score];
        }
    }
    usort($recommendations, fn($a, $b) => $b['score'] <=> $a['score']);
    $recommendations = array_slice($recommendations, 0, 8);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Parfum Berdasarkan Aroma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1e3c72, #2a5298); min-height: 100vh; }
        .section-box { background: #fff; padding: 30px; border-radius: 16px; box-shadow: 0 15px 40px rgba(0,0,0,.2); }
        .card-parfum { transition: .3s; border: none; border-radius: 16px; overflow: hidden; }
        .card-parfum:hover { transform: translateY(-8px); box-shadow: 0 12px 25px rgba(0,0,0,.25); }
        .badge-rekom { position: absolute; top: 10px; left: 10px; z-index: 1; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="section-box">
        <h3 class="text-center mb-4 fw-bold">Pencarian Parfum</h3>
        <form class="mb-4" method="GET">
            <input type="text" name="aroma" class="form-control form-control-lg" 
                   placeholder="Masukkan aroma (misal: woody floral)..." 
                   value="<?= htmlspecialchars($keyword) ?>">
        </form>

        <?php if (!empty($keyword)): ?>
            <h5 class="mb-3 fw-bold text-primary">Rekomendasi Parfum Mirip</h5>
            <div class="row mb-5">
                <?php foreach ($recommendations as $r): ?>
                    <div class="col-md-3 mb-4">
                        <a href="detail.php?id=<?= $r['data']['id'] ?>" class="text-decoration-none text-dark">
                            <div class="card card-parfum h-100 border border-primary">
                                <span class="badge bg-primary badge-rekom">Skor: <?= round($r['score'], 2) ?></span>
                                <img src="uploads/<?= $r['data']['foto'] ?>" class="card-img-top" height="170" style="object-fit:cover">
                                <div class="card-body">
                                    <h6 class="card-title"><?= htmlspecialchars($r['data']['nama']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($r['data']['brand']) ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($recommendations)): ?>
                    <div class="col-12"><div class="alert alert-warning text-center">Tidak ditemukan aroma yang mirip.</div></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <h5 class="mb-3 fw-bold">Semua Parfum</h5>
        <div class="row">
            <?php foreach ($data as $p): ?>
                <div class="col-md-3 mb-4">
                    <a href="detail.php?id=<?= $p['id'] ?>" class="text-decoration-none text-dark">
                        <div class="card card-parfum h-100 border shadow-sm">
                            <img src="uploads/<?= $p['foto'] ?>" class="card-img-top" height="170" style="object-fit:cover">
                            <div class="card-body">
                                <h6 class="card-title"><?= htmlspecialchars($p['nama']) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($p['brand']) ?></small>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

</body>
</html>