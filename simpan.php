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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $jenis_kelamin = ucwords(strtolower(mysqli_real_escape_string($conn, $_POST['jenis_kelamin'])));
    $agama = mysqli_real_escape_string($conn, $_POST['agama']);
    $sekolah_asal = mysqli_real_escape_string($conn, $_POST['sekolah_asal']);

    $response = ['status' => 'error', 'message' => 'Gagal mendaftarkan siswa'];

    // Handle photo upload
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $foto = $_FILES['foto'];
        $targetDir = "uploads/";
        
        // Create uploads directory if it doesn't exist
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($foto['type'], $allowedTypes)) {
            $response['message'] = "Tipe file tidak diizinkan. Gunakan JPG, JPEG, atau PNG.";
            echo json_encode($response);
            exit();
        }

        // Validate file size (5MB max)
        if ($foto['size'] > 5 * 1024 * 1024) {
            $response['message'] = "Ukuran file terlalu besar. Maksimal 5MB.";
            echo json_encode($response);
            exit();
        }

        $fotoName = uniqid() . "-" . basename($foto['name']);
        $targetFile = $targetDir . $fotoName;

        // Move uploaded file
        if (!move_uploaded_file($foto['tmp_name'], $targetFile)) {
            $response['message'] = "Gagal mengunggah foto.";
            echo json_encode($response);
            exit();
        }
    } else {
        $response['message'] = "Foto harus diunggah.";
        echo json_encode($response);
        exit();
    }

    // Insert into database with foto
    $stmt = $conn->prepare("INSERT INTO siswa (nama, alamat, jenis_kelamin, agama, sekolah_asal, foto) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $nama, $alamat, $jenis_kelamin, $agama, $sekolah_asal, $fotoName);

    try {
        if ($stmt->execute()) {
            tambahLog($conn, $_SESSION['username'], "Mendaftarkan Siswa Baru: $nama");
            $response = [
                'status' => 'success',
                'message' => 'Pendaftaran berhasil'
            ];
        }
        $stmt->close();
    } catch (Exception $e) {
        // Delete uploaded file if database insert fails
        if (isset($targetFile) && file_exists($targetFile)) {
            unlink($targetFile);
        }
        $response['message'] = "Error: " . $e->getMessage();
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

function tambahLog($conn, $username, $aktivitas) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $conn->prepare("INSERT INTO log_aktivitas (username, aktivitas, waktu, ip_address) VALUES (?, ?, NOW(), ?)");
    $stmt->bind_param("sss", $username, $aktivitas, $ip_address);
    
    try {
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Logging error: " . $e->getMessage());
    }
    $stmt->close();
}
?>