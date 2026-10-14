<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['giris']) || $_SESSION['giris'] !== true) {
    header("Location: giris.php");
    exit;
}

require_once '../baglanti.php';

$bugun = date('Y-m-d');

$siparisler_sorgu = $db->query("
    SELECT siparisler.*, menuler.menu_adi 
    FROM siparisler 
    JOIN menuler ON siparisler.menu_id = menuler.id 
    ORDER BY siparisler.id DESC
");
$siparisler = $siparisler_sorgu->fetchAll(PDO::FETCH_ASSOC);

$ozet_sorgu = $db->prepare("
    SELECT COUNT(*) as toplam_adet, SUM(toplam_fiyat) as toplam_ciro 
    FROM siparisler 
    WHERE DATE(siparis_tarihi) = ?
");
$ozet_sorgu->execute([$bugun]);
$gunluk_ozet = $ozet_sorgu->fetch(PDO::FETCH_ASSOC);

$menu_rapor_sorgu = $db->prepare("
    SELECT menuler.menu_adi, SUM(siparisler.adet) as toplam_satilan 
    FROM siparisler 
    JOIN menuler ON siparisler.menu_id = menuler.id 
    WHERE DATE(siparisler.siparis_tarihi) = ? 
    GROUP BY siparisler.menu_id
");
$menu_rapor_sorgu->execute([$bugun]);
$menu_raporlari = $menu_rapor_sorgu->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yönetim Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="panel.php">🍲 Yemek Sipariş Yönetim Paneli</a>
            <div class="d-flex">
                <a href="../index.php" target="_blank" class="btn btn-outline-light btn-sm me-2">Siteye Git</a>
                <a href="cikis.php" class="btn btn-danger btn-sm">Çıkış Yap</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row mb-5">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-white p-4">
                    <h3 class="fw-bold mb-3 text-primary">📊 Gün Sonu Raporu (<?= date('d.m.Y') ?>)</h3>
                    <div class="row text-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="p-3 bg-light rounded">
                                <h5 class="text-muted">Bugünkü Toplam Sipariş</h5>
                                <h2 class="fw-bold text-dark"><?= intval($gunluk_ozet['toplam_adet']) ?> Adet</h2>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h5 class="text-muted">Bugünkü Toplam Ciro</h5>
                                <h2 class="fw-bold text-success"><?= number_format($gunluk_ozet['toplam_ciro'] ?? 0, 2, ',', '.') ?> TL</h2>
                            </div>
                        </div>
                    </div>

                    <h5 class="mt-4 fw-bold">Menü Bazlı Satış Dağılımı:</h5>
                    <?php if (count($menu_raporlari) > 0): ?>
                        <ul class="list-group mt-2">
                            <?php foreach ($menu_raporlari as $rapor): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($rapor['menu_adi']) ?>
                                    <span class="badge bg-primary rounded-pill"><?= $rapor['toplam_satilan'] ?> Adet</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted mt-2">Bugün henüz menü satışı gerçekleşmedi.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h3 class="fw-bold mb-4">Gelen Tüm Siparişler</h3>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ad Soyad</th>
                                <th>Seçilen Menü</th>
                                <th>Adet</th>
                                <th>Toplam Tutar</th>
                                <th>Sipariş Zamanı</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($siparisler) > 0): ?>
                                <?php foreach ($siparisler as $siparis): ?>
                                <tr>
                                    <td><?= $siparis['id'] ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($siparis['ad_soyad']) ?></td>
                                    <td><?= htmlspecialchars($siparis['menu_adi']) ?></td>
                                    <td><?= $siparis['adet'] ?></td>
                                    <td class="text-success fw-bold"><?= number_format($siparis['toplam_fiyat'], 2, ',', '.') ?> TL</td>
                                    <td class="text-muted small"><?= $siparis['siparis_tarihi'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Henüz gelen sipariş bulunmuyor.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>