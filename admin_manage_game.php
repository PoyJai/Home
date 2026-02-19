<?php
session_start();
require_once 'db_config.php';

// 1. เช็คสิทธิ์ Admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== 'admin') {
    header("location: allgame.php");
    exit;
}

$id = $_GET['id'] ?? '';
// เตรียมค่าเริ่มต้นสำหรับกรณีเพิ่มเกมใหม่
$game = [
    'title' => '', 
    'genre' => '', 
    'price' => '0.00', 
    'image_url' => '', 
    'description' => '',
    'video_url' => '',
    'image_url_2' => '',
    'image_url_3' => ''
];

// 2. ถ้ามี ID ดึงข้อมูลเดิมมาแสดง
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM games WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $game = $result->fetch_assoc();
    }
}

// 3. เมื่อมีการกดปุ่ม Submit (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $price = $_POST['price'];
    $image_url = $_POST['image_url'];
    $description = $_POST['description'];
    $video_url = $_POST['video_url'];
    $image_url_2 = $_POST['image_url_2'];
    $image_url_3 = $_POST['image_url_3'];

    if ($id) {
        // กรณี: แก้ไขข้อมูลเดิม (ครบทุก Field)
        $sql = "UPDATE games SET title=?, genre=?, price=?, image_url=?, description=?, video_url=?, image_url_2=?, image_url_3=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsssssi", $title, $genre, $price, $image_url, $description, $video_url, $image_url_2, $image_url_3, $id);
    } else {
        // กรณี: เพิ่มเกมใหม่
        $sql = "INSERT INTO games (title, genre, price, image_url, description, video_url, image_url_2, image_url_3) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsssss", $title, $genre, $price, $image_url, $description, $video_url, $image_url_2, $image_url_3);
    }

    if ($stmt->execute()) {
        header("location: admin_panel.php?success=1");
        exit;
    } else {
        $error = "เกิดข้อผิดพลาด: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Game | StunShop Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f0f0f0; }
        .neo-input {
            border: 3px solid #000;
            box-shadow: 4px 4px 0px #000;
            transition: all 0.2s;
        }
        .neo-input:focus {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0px #000;
            outline: none;
            border-color: #FF48B0;
        }
        .neo-btn {
            border: 3px solid #000;
            box-shadow: 5px 5px 0px #000;
            transition: all 0.1s;
        }
        .neo-btn:active {
            transform: translate(3px, 3px);
            box-shadow: 0px 0px 0px #000;
        }
    </style>
</head>
<body class="p-4 md:p-10">
    <div class="max-w-4xl mx-auto bg-white border-4 border-black p-6 md:p-10 shadow-[12px_12px_0px_#000] rounded-3xl">
        
        <div class="flex items-center justify-between mb-10 border-b-4 border-black pb-5">
            <h1 class="text-3xl md:text-5xl font-black uppercase italic">
                <?= $id ? 'Edit Game' : 'Add New Game' ?> 🕹️
            </h1>
            <a href="admin_panel.php" class="neo-btn bg-gray-200 px-4 py-2 font-bold rounded-xl text-sm">กลับหน้าแอดมิน</a>
        </div>

        <?php if(isset($error)): ?>
            <div class="bg-red-400 border-4 border-black p-4 mb-6 font-bold shadow-[4px_4px_0px_#000]">
                ⚠️ <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div class="md:col-span-2">
                <label class="block font-black text-lg mb-1 italic">TITLE (ชื่อเกม)</label>
                <input type="text" name="title" value="<?= htmlspecialchars($game['title']) ?>" 
                       class="w-full neo-input p-3 rounded-xl font-bold text-xl" required>
            </div>

            <div>
                <label class="block font-black text-lg mb-1 italic">GENRE (หมวดหมู่)</label>
                <input type="text" name="genre" value="<?= htmlspecialchars($game['genre']) ?>" 
                       class="w-full neo-input p-3 rounded-xl font-bold" placeholder="เช่น Action, RPG" required>
            </div>

            <div>
                <label class="block font-black text-lg mb-1 italic">PRICE (ราคาบาท)</label>
                <input type="number" step="0.01" name="price" value="<?= $game['price'] ?>" 
                       class="w-full neo-input p-3 rounded-xl font-bold text-pop-pink" required>
            </div>

            <div class="md:col-span-2">
                <label class="block font-black text-lg mb-1 italic">DESCRIPTION (รายละเอียดเกม)</label>
                <textarea name="description" rows="4" 
                          class="w-full neo-input p-3 rounded-xl font-medium"><?= htmlspecialchars($game['description']) ?></textarea>
            </div>

            <div class="md:col-span-2">
                <label class="block font-black text-lg mb-1 italic text-blue-600">MAIN IMAGE URL (รูปหลัก)</label>
                <input type="text" name="image_url" value="<?= htmlspecialchars($game['image_url']) ?>" 
                       class="w-full neo-input p-3 rounded-xl font-mono text-sm" placeholder="https://..." required>
            </div>

            <div>
                <label class="block font-black text-sm mb-1 italic">IMAGE URL 2 (รอง)</label>
                <input type="text" name="image_url_2" value="<?= htmlspecialchars($game['image_url_2']) ?>" 
                       class="w-full neo-input p-3 rounded-xl font-mono text-sm" placeholder="URL รูปที่ 2">
            </div>

            <div>
                <label class="block font-black text-sm mb-1 italic">IMAGE URL 3 (รอง)</label>
                <input type="text" name="image_url_3" value="<?= htmlspecialchars($game['image_url_3']) ?>" 
                       class="w-full neo-input p-3 rounded-xl font-mono text-sm" placeholder="URL รูปที่ 3">
            </div>

            <div class="md:col-span-2">
                <label class="block font-black text-lg mb-1 italic text-red-500">VIDEO URL (YouTube/MP4)</label>
                <input type="text" name="video_url" value="<?= htmlspecialchars($game['video_url']) ?>" 
                       class="w-full neo-input p-3 rounded-xl font-mono text-sm" placeholder="https://youtube.com/embed/...">
            </div>

            <div class="md:col-span-2 pt-6 flex flex-col md:flex-row gap-4">
                <button type="submit" 
                        class="neo-btn flex-1 bg-green-400 hover:bg-green-300 py-4 rounded-2xl font-black text-2xl uppercase italic tracking-wider">
                    SAVE GAME DATA 💾
                </button>
                <a href="admin_panel.php" 
                   class="neo-btn bg-white hover:bg-gray-100 px-10 py-4 rounded-2xl font-black text-2xl text-center italic">
                    CANCEL
                </a>
            </div>

        </form>
    </div>

    <footer class="text-center mt-10 font-bold opacity-30 uppercase tracking-widest text-xs">
        StunShop Management System &copy; 2026
    </footer>
</body>
</html>