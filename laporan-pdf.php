<?php
require_once('fpdf186/fpdf.php');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pendaftaran_siswa";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = $conn->query("SELECT * FROM siswa");

class PDF extends FPDF {
    function Header() {
        $this->SetFillColor(220, 220, 220); 
        $this->Rect(10, 10, 190, 277, 'D');
        
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(0, 0, 0);
        $this->SetY(20);
        $this->Cell(0, 10, 'SMK CODING', 0, 1, 'C');
        
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 7, 'LAPORAN DAFTAR SISWA', 0, 1, 'C');
        $this->Cell(0, 7, 'TAHUN PELAJARAN ' . date('Y') . '/' . (date('Y') + 1), 0, 1, 'C');
        
        $this->SetDrawColor(0, 0, 0);
        $this->Line(30, 47, 180, 47);
    }

    function Footer() {
        $this->SetY(-25);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function TableHeader() {
        $this->SetY($this->GetY() + 8);
        $this->SetX(20);

        $this->SetFont('Arial', 'B', 10);
        $this->Cell(10, 8, 'No', 1, 0, 'C');
        $this->Cell(50, 8, 'Nama', 1, 0, 'C');
        $this->Cell(30, 8, 'Alamat', 1, 0, 'C');
        $this->Cell(25, 8, 'Jenis Kelamin', 1, 0, 'C');
        $this->Cell(25, 8, 'Agama', 1, 0, 'C');
        $this->Cell(30, 8, 'Sekolah Asal', 1, 1, 'C');
    }

    function TableRow($no, $nama, $alamat, $jenis_kelamin, $agama, $sekolah_asal) {
        $this->SetX(20);
        $this->SetFont('Arial', '', 10);
        $this->Cell(10, 8, $no, 1, 0, 'C');
        $this->Cell(50, 8, $nama, 1, 0, 'L');
        $this->Cell(30, 8, $alamat, 1, 0, 'L');
        $this->Cell(25, 8, $jenis_kelamin, 1, 0, 'C');
        $this->Cell(25, 8, $agama, 1, 0, 'C');
        $this->Cell(30, 8, $sekolah_asal, 1, 1, 'L');
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();


$pdf->TableHeader();


$no = 1;
while ($siswa = mysqli_fetch_array($query)) {
    $pdf->TableRow($no++, $siswa['nama'], $siswa['alamat'], ucfirst($siswa['jenis_kelamin']), $siswa['agama'], $siswa['sekolah_asal']);
}


$pdf->Output('I', 'laporan_siswa.pdf');
?>