<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== 'admin') {
    exit("Access Denied");
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM games WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
header("location: admin_panel.php");
exit;