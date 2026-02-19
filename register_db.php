<?php
session_start();
include('server.php'); 

$errors = []; 

if (isset($_POST['reg_user'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    if (empty($username)) $errors[] = "กรุณากรอกชื่อผู้ใช้งาน";
    if (empty($email)) $errors[] = "กรุณากรอกอีเมล";
    if ($password !== $confirm_password) $errors[] = "รหัสผ่านไม่ตรงกัน";

    $user_check_query = "SELECT * FROM users WHERE username='$username' OR email='$email' LIMIT 1";
    $result = mysqli_query($conn, $user_check_query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        if ($user['username'] === $username) $errors[] = "ชื่อผู้ใช้นี้มีคนใช้แล้ว";
        if ($user['email'] === $email) $errors[] = "อีเมลนี้มีคนใช้แล้ว";
    }

    if (count($errors) == 0) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        // เพิ่มคอลัมน์ role และใส่ค่า 'user'
        $query = "INSERT INTO users (username, email, password, role) VALUES('$username', '$email', '$password_hashed', 'user')";
        mysqli_query($conn, $query);

        $_SESSION['success'] = "สมัครสมาชิกสำเร็จ!";
        header('location: login.php');
    } else {
        $_SESSION['error_messages'] = $errors;
        header('location: register.php');
    }
}
?>