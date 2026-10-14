<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'baglanti.php';

date_default_timezone_set('Europe/Istanbul');
$simdiki_saat = (int)date('H');
$siparis_aktif = false;

// 08:00 ile 00:00 arası açık
if ($simdiki_saat >= 8 && $simdiki_saat < 24) {
    $siparis_aktif = true;
}

$mesaj = "";
$mesaj_tur = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $siparis_aktif) {
    $ad_soyad = trim($_POST['ad_soyad']);
    $menu_id = intval($_POST['menu_id']);
    $adet = intval($_POST['adet']);

    if (!empty($ad_soyad) && $menu_id > 0 && $adet > 0) {
        $menu_sorgu = $db->prepare("SELECT * FROM menuler WHERE id = ?");
        $menu_sorgu->execute([$menu_id]);
        $menu = $menu_sorgu->fetch(PDO::FETCH_ASSOC);

        if ($menu) {
            $toplam_fiyat = $menu['fiyat'] * $adet;

            $ekle = $db->prepare("INSERT INTO siparisler (ad_soyad, menu_id, adet, toplam_fiyat) VALUES (?, ?, ?, ?)");
            $ekle->execute([$ad_soyad, $menu_id, $adet, $toplam_fiyat]);

            $mesaj = "Siparişiniz başarıyla alındı! Afiyet olsun.";
            $mesaj_tur = "success";
        } else {
            $mesaj = "Geçersiz menü seçimi!";
            $mesaj_tur = "danger";
        }
    } else {
        $mesaj = "Lütfen ad-soyad girin ve geçerli bir adet seçin!";
        $mesaj_tur = "danger";
    }
}

$menuler_sorgu = $db->query("SELECT * FROM menuler");
$menuler = $menuler_sorgu->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yemek Sipariş Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">🍲 Günlük Yemek Siparişi</a>
            <a href="admin/giris.php" class="btn btn-outline-light btn-sm">Yönetici Paneli</a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow-sm border-0 p-4">
                    <h2 class="text-center fw-bold mb-3">Günün Menüleri</h2>
                    <p class="text-center text-muted mb-4">Çalışma Saatlerimiz: <strong>08:00 - 00:00</strong> arasındadır.</p>

                    <?php if (!empty($mesaj)): ?>
                        <div class="alert alert-<?= $mesaj_tur ?>" role="alert">
                            <?= $mesaj ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($siparis_aktif): ?>
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Adınız Soyadınız:</label>
                                <input type="text" class="form-control" name="ad_soyad" required placeholder="Örn: Ahmet Yılmaz">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Menü Seçimi:</label>
                                <select class="form-select" name="menu_id" required>
                                    <option value="" selected disabled>-- Menü Seçin --</option>
                                    <?php foreach ($menuler as $menu): ?>
                                        <option value="<?= $menu['id'] ?>">
                                            <?= htmlspecialchars($menu['menu_adi']) ?> - <?= number_format($menu['fiyat'], 2, ',', '.') ?> TL
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Adet:</label>
                                <input type="number" class="form-control" name="adet" value="1" min="1" max="50" required>
                            </div>

                            <button type="submit" class="btn btn-success w-100 py-2 fw-bold">Siparişi Tamamla</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-danger text-center py-4" role="alert">
                            <h4 class="alert-heading fw-bold">Şu An Kapalıyız!</h4>
                            <p class="mb-0">Sipariş saatlerimiz sabah <strong>08:00</strong> ile gece <strong>00:00</strong> arasındadır.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>