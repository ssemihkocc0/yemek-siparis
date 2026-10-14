<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../baglanti.php';

$hata = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kullanici_adi = trim($_POST['kullanici_adi']);
    $sifre = trim($_POST['sifre']);

    if (!empty($kullanici_adi) && !empty($sifre)) {
        $sorgu = $db->prepare("SELECT * FROM admin WHERE kullanici_adi = ?");
        $sorgu->execute([$kullanici_adi]);
        $admin = $sorgu->fetch(PDO::FETCH_ASSOC);

        if ($admin && $sifre === $admin['sifre']) {
            $_SESSION['giris'] = true;
            $_SESSION['kullanici_adi'] = $admin['kullanici_adi'];
            header("Location: panel.php");
            exit;
        } else {
            $hata = "Kullanıcı adı veya şifre hatalı!";
        }
    } else {
        $hata = "Lütfen tüm alanları doldurun!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yönetici Girişi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center py-5" style="height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 p-4">
                    <h3 class="text-center fw-bold mb-4">Admin Girişi</h3>
                    <?php if (!empty($hata)): ?>
                        <div class="alert alert-danger py-2"><?= $hata ?></div>
                    <?php endif; ?>
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" name="kullanici_adi" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Şifre</label>
                            <input type="password" class="form-control" name="sifre" required>
                        </div>
                        <button type="submit" class="btn btn-dark w-100 py-2">Giriş Yap</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="../index.php" class="text-decoration-none text-muted small">← Siteye Dön</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>