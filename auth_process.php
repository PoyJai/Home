<?php
session_start();
header('Content-Type: application/json');
require_once 'server.php';

$response = ['success' => false, 'message' => 'เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['message' => 'Invalid request method.']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$mode = $data['mode'] ?? '';
$email = $conn->real_escape_string($data['email'] ?? '');
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['message' => 'กรุณากรอกข้อมูลให้ครบถ้วน.']);
    exit;
}

if ($mode === 'signup') {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $username = explode('@', $email)[0];
    // เพิ่มการบันทึก role เป็น user สำหรับคนสมัครใหม่
    $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $password_hash);
    
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'สมัครสมาชิกสำเร็จ! เข้าสู่ระบบได้เลย'];
    } else {
        $response['message'] = ($conn->errno === 1062) ? 'อีเมลนี้มีผู้ใช้งานแล้ว.' : 'เกิดข้อผิดพลาด: ' . $stmt->error;
    }
    $stmt->close();

} elseif ($mode === 'login') {
    // ดึงค่า role มาด้วย
    $sql = "SELECT id, username, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION["loggedin"] = true;
            $_SESSION["id"] = $user['id'];
            $_SESSION["username"] = $user['username'];
            $_SESSION["role"] = $user['role']; // เก็บยศ admin/user ลง Session
            
            $response = [
                'success' => true, 
                'message' => 'เข้าสู่ระบบสำเร็จ!',
                'redirect' => 'index.php'
            ];
        } else {
            $response['message'] = 'รหัสผ่านไม่ถูกต้อง.';
        }
    } else {
        $response['message'] = 'ไม่พบอีเมลนี้ในระบบ.';
    }
    $stmt->close();
}

echo json_encode($response);