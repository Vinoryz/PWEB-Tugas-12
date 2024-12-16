<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pendaftaran_siswa";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];
$sql = "SELECT * FROM siswa WHERE id=$id";
$query = $conn->query($sql);
$siswa = $query->fetch_assoc();
$jenis_kelamin = ucfirst($siswa['jenis_kelamin']);

require_once('./fpdf/fpdf.php');

class PDF extends FPDF {
    function Header() {
        $this->SetFillColor(220, 220, 220); 
        $this->Rect(10, 10, 190, 277, 'D');
        
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(0, 0, 0);
        $this->SetY(20);
        $this->Cell(0, 10, 'SMK CODING', 0, 1, 'C');
        
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 7, 'KARTU PENDAFTARAN SISWA BARU', 0, 1, 'C');
        $this->Cell(0, 7, 'TAHUN PELAJARAN ' . date('Y') . '/' . (date('Y') + 1), 0, 1, 'C');
        
        $this->SetDrawColor(0, 0, 0);
        $this->Line(30, 47, 180, 47);
    }

    function Footer() {
        $this->SetY(-25);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        
        $this->SetY(-20);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(0, 10, 'Harap membawa kartu ini saat verifikasi di sekolah', 0, 0, 'C');
    }

    function DetailRow($label, $value) {
        $this->SetFont('Arial', '', 11);
        $this->Cell(50, 8, $label, 0, 0);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 8, ': ' . $value, 0, 1); 
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages(); 
$pdf->AddPage();

$fotoPath = !empty($siswa['foto']) ? 'uploads/' . $siswa['foto'] : null;
if ($fotoPath && file_exists($fotoPath)) {
    $pdf->SetXY(154, 55);
    $pdf->Cell(40, 50, '', 1); 
    $pdf->Image($fotoPath, 155, 56, 38, 48); 
}


$pdf->SetXY(12, 55); 
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'DATA PRIBADI', 0, 1);

$pdf->SetXY(12, 65); 
$pdf->DetailRow('Nomor Pendaftaran', sprintf('%04d', $siswa['id']));
$pdf->SetXY(12, 73);
$pdf->DetailRow('Nama Lengkap', $siswa['nama']);
$pdf->SetXY(12, 81);
$pdf->DetailRow('Alamat', $siswa['alamat']);
$pdf->SetXY(12, 89);
$pdf->DetailRow('Jenis Kelamin', $jenis_kelamin);
$pdf->SetXY(12, 97); 
$pdf->DetailRow('Agama', $siswa['agama']);
$pdf->SetXY(12, 105);
$pdf->DetailRow('Sekolah Asal', $siswa['sekolah_asal']);


$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(20, 115, 190, 115); 

$pdf->SetXY(12, 220); 
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'TANDA TANGAN', 0, 1);


$pdf->SetXY(12, 230);
$pdf->Cell(80, 30, 'Peserta Didik,', 0, 0);
$pdf->Cell(80, 30, 'Petugas Pendaftaran,', 0, 1);

$pdf->SetXY(12, 260); 
$pdf->Cell(80, 10, '(_________________)', 0, 0);
$pdf->Cell(80, 10, '(_________________)', 0, 1);

$pdf->Output('F', 'kartu_pendaftaran_' . $siswa['nama'] . '.pdf');
$pdf->Output('I', 'kartu_pendaftaran_' . $siswa['nama'] . '.pdf');
?>