<?php
session_start();
include('server.php'); 

if (isset($_POST['login_user'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
        header("location: login.php");
        exit;
    }

    // ดึงข้อมูล User และ Role
    $sql = "SELECT * FROM users WHERE username='$username' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            // ✅ เก็บข้อมูลลง Session
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = trim($user['role']); // เก็บยศ (admin หรือ user)
            
            header("location: index.php"); // ส่งไปหน้า All Games ทันที
            exit;
        } else {
            $_SESSION['error'] = "รหัสผ่านไม่ถูกต้อง";
            header("location: login.php");
        }
    } else {
        $_SESSION['error'] = "ไม่พบชื่อผู้ใช้งานนี้";
        header("location: login.php");
    }
}
?>