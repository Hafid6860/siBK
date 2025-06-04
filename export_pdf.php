<?php
// export_pdf.php
require_once 'config.php';
require('fpdf182/fpdf.php');

if (!isLoggedIn() || !isGuru()) {
    header('Location: login.php');
    exit;
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'harian';
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$minggu_awal = isset($_GET['minggu_awal']) ? $_GET['minggu_awal'] : '';
$minggu_akhir = isset($_GET['minggu_akhir']) ? $_GET['minggu_akhir'] : '';

// Ambil data sesuai filter, dengan JOIN siswa_profile → users untuk nama siswa
$data_laporan = [];

if ($filter === 'harian') {
    $stmt = $pdo->prepare('
        SELECT 
            k.id,
            u.nama_lengkap AS siswa_nama,
            k.jam,
            k.catatan_sesi
        FROM konseling k
        JOIN siswa_profile sp ON k.siswa_id = sp.id
        JOIN users u ON sp.user_id = u.id
        WHERE k.guru_id = ? 
          AND k.tanggal = ?
        ORDER BY k.jam ASC
    ');
    $stmt->execute([$_SESSION['user_id'], $tanggal]);
    $data_laporan = $stmt->fetchAll(PDO::FETCH_ASSOC);

} elseif ($filter === 'mingguan' && $minggu_awal && $minggu_akhir) {
    $stmt = $pdo->prepare('
        SELECT 
            k.id,
            k.tanggal,
            u.nama_lengkap AS siswa_nama,
            k.jam,
            k.catatan_sesi
        FROM konseling k
        JOIN siswa_profile sp ON k.siswa_id = sp.id
        JOIN users u ON sp.user_id = u.id
        WHERE k.guru_id = ? 
          AND k.tanggal BETWEEN ? AND ?
        ORDER BY k.tanggal ASC, k.jam ASC
    ');
    $stmt->execute([$_SESSION['user_id'], $minggu_awal, $minggu_akhir]);
    $data_laporan = $stmt->fetchAll(PDO::FETCH_ASSOC);

} elseif ($filter === 'bulanan') {
    $stmt = $pdo->prepare('
        SELECT 
            k.id,
            k.tanggal,
            u.nama_lengkap AS siswa_nama,
            k.jam,
            k.catatan_sesi
        FROM konseling k
        JOIN siswa_profile sp ON k.siswa_id = sp.id
        JOIN users u ON sp.user_id = u.id
        WHERE k.guru_id = ? 
          AND DATE_FORMAT(k.tanggal, "%Y-%m") = ?
        ORDER BY k.tanggal ASC, k.jam ASC
    ');
    $stmt->execute([$_SESSION['user_id'], $bulan]);
    $data_laporan = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Buat PDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Laporan Konseling - ' . ucfirst($filter), 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);

// Tampilkan periode
$periode_text = '';
if ($filter === 'harian') {
    $periode_text = 'Periode: ' . date('d-m-Y', strtotime($tanggal));
} elseif ($filter === 'mingguan') {
    $periode_text = 'Periode: ' . date('d-m-Y', strtotime($minggu_awal)) . ' s/d ' . date('d-m-Y', strtotime($minggu_akhir));
} else { // bulanan
    $periode_text = 'Periode: ' . date('F Y', strtotime($bulan . '-01'));
}
$pdf->Cell(0, 8, $periode_text, 0, 1);

// Header tabel
$pdf->SetFont('Arial', 'B', 10);
if ($filter !== 'harian') {
    $pdf->Cell(30, 8, 'Tanggal', 1);
}
$pdf->Cell(20, 8, 'Jam', 1);
$pdf->Cell(50, 8, 'Siswa', 1);
$pdf->Cell(90, 8, 'Catatan Sesi', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 10);
foreach ($data_laporan as $d) {
    if ($filter !== 'harian') {
        $pdf->Cell(30, 8, date('d-m-Y', strtotime($d['tanggal'])), 1);
    }
    $pdf->Cell(20, 8, substr($d['jam'], 0, 5), 1);
    $pdf->Cell(50, 8, $d['siswa_nama'], 1);
    // Kolom Catatan Sesi pakai MultiCell
    $width = 90;
    $height = 8;
    $yBefore = $pdf->GetY();
    $xBefore = $pdf->GetX();
    $pdf->MultiCell($width, 4, $d['catatan_sesi'], 1);
    $yAfter = $pdf->GetY();
    $diff = $yAfter - $yBefore;
    if ($diff < $height) {
        $pdf->SetXY($xBefore + $width, $yBefore + $height);
    }
}

$pdf->Output('D', 'laporan_konseling.pdf');
exit;
