<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "parfum_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// ================= VALIDASI ID =================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Parfum tidak ditemukan.");
}

$id = (int) $_GET['id'];

// ================= QUERY DATA =================
$sql = "SELECT * FROM parfum WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die("Parfum tidak ditemukan.");
}

$parfum = $result->fetch_assoc();
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($parfum['nama_parfum']) ?> - Detail Parfum</title>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-light">

<div class="container py-5">
    <div class="card shadow-lg p-4">

        <div class="row">
            <div class="col-md-4">
                <?php if (!empty($parfum['foto'])): ?>
                    <img src="uploads/<?= htmlspecialchars($parfum['foto']) ?>"
                         class="img-fluid rounded mb-3"
                         alt="<?= htmlspecialchars($parfum['nama_parfum']) ?>">
                <?php else: ?>
                    <div class="bg-secondary text-white text-center p-5 rounded">
                        <i class="bi bi-image fs-1"></i><br>
                        Tidak ada foto
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-8">
                <h2 class="fw-bold"><?= htmlspecialchars($parfum['nama_parfum']) ?></h2>
                <p class="text-muted fs-5"><?= htmlspecialchars($parfum['brand']) ?></p>

                <hr>

                <h5><i class="bi bi-flower1 text-success"></i> Aroma Parfum</h5>
                <ul>
                    <li><strong>Top Notes:</strong> <?= htmlspecialchars($parfum['top_notes']) ?></li>
                    <li><strong>Middle Notes:</strong> <?= htmlspecialchars($parfum['middle_notes']) ?></li>
                    <li><strong>Base Notes:</strong> <?= htmlspecialchars($parfum['base_notes']) ?></li>
                </ul>

                <a href="index.php" class="btn btn-secondary mt-3">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar Parfum
                </a>
            </div>
        </div>

    </div>
</div>

</body>
</html>