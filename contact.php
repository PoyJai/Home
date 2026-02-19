<?php
session_start();
require_once 'db_config.php'; 

// 1. ตรรกะการ Logout
if (isset($_GET['logout'])) {
    session_destroy(); 
    header('location: login.php'); 
    exit;
}

$is_logged_in = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$current_username = $is_logged_in ? htmlspecialchars($_SESSION["username"]) : "Guest"; 

// 2. ดึงรูปภาพจากโฟลเดอร์ Admin
$admin_folder = 'Admin/';
$images = glob($admin_folder . "*.{jpg,jpeg,png,webp}", GLOB_BRACE);

// ข้อมูลรายชื่อและสี
$team_names = ["ภาคภูมิ (  Super_Manager  )", "จตุพร (  Main_Designer  )", "ณัฐพร (  Designer  )", "ธีรภัทร์ (  HR  )", "ชลธี (  เด็กฝึกงาน  )"];
$colors = ['bg-toy-pink', 'bg-toy-blue', 'bg-toy-yellow', 'bg-toy-purple', 'bg-toy-green'];

// ตรรกะ Contact Form
$status_message = "";
$status_type = ""; 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $message = mysqli_real_escape_string($conn, $_POST['message'] ?? '');
    
    if (!empty($name) && !empty($email) && !empty($message)) {
        $sql = "INSERT INTO contacts (name, email, subject, message) VALUES ('$name', '$email', 'General Inquiry', '$message')";
        if (mysqli_query($conn, $sql)) {
            $status_message = "ส่งข้อความเรียบร้อย! ✨";
            $status_type = "success";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About & Team | StunShop ✨</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mitr:wght@300;400;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'toy-pink': '#FFB4B4', 'toy-blue': '#B4E4FF',
                        'toy-yellow': '#FDF7C3', 'toy-purple': '#E5D1FA',
                        'toy-green': '#BFF6C3', 'pop-orange': '#FF7A00',
                    },
                }
            }
        }
    </script>
    <style>
        body { 
            font-family: 'Mitr', sans-serif;
            background-color: #FFFDF9; 
            background-image: radial-gradient(#B4E4FF 0.5px, transparent 0.5px);
            background-size: 24px 24px;
        }
        .toy-card {
            background: white; border: 4px solid #000;
            box-shadow: 8px 8px 0px #000; border-radius: 1.5rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .toy-card:hover { transform: translateY(-5px); box-shadow: 12px 12px 0px #FFB4B4; }
        .input-toy { border: 3px solid #000; border-radius: 1rem; transition: all 0.3s ease; }
        .btn-toy { border: 3px solid #000; box-shadow: 4px 4px 0px #000; transition: all 0.2s; border-radius: 1rem; }
        .btn-toy:active { transform: translate(2px, 2px); box-shadow: 0px 0px 0px #000; }
        
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        .float-anim { animation: float 4s ease-in-out infinite; }
    </style>
</head>
<body class="min-h-screen">

    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-sm border-b-4 border-black">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-3xl font-black italic tracking-tighter hover:scale-105 transition-transform">
                <span class="text-toy-pink">Stun</span><span class="text-toy-blue">Shop</span>
            </a>
            <div class="hidden md:flex items-center space-x-6 font-bold">
                <a href="index.php" class="hover:text-pop-orange transition-colors">หน้าแรก</a>
                <a href="contact.php" class="bg-toy-yellow border-2 border-black px-5 py-2 rounded-full shadow-[4px_4px_0px_#000] hover:translate-y-[-2px] active:translate-y-[1px] transition-all">ติดต่อเรา</a>
                <?php if($is_logged_in): ?>
                    <span class="bg-black text-white px-4 py-1 rounded-full text-sm">👤 <?= $current_username ?></span>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-6 py-12">
        
        <div data-aos="fade-down" class="bg-toy-blue border-4 border-black p-10 mb-16 rounded-3xl shadow-[15px_15px_0px_#000] relative overflow-hidden">
            <div class="relative z-10 text-center md:text-left">
                <h1 class="text-5xl md:text-6xl font-black mb-4 [text-shadow:4px_4px_0px_#000] text-white italic">ADMIN TEAM</h1>
                <p class="text-xl font-bold bg-white text-black inline-block px-4 py-1 border-2 border-black rotate-1">ทีมงานคุณภาพจาก VCNC</p>
            </div>
            <div class="absolute right-10 top-5 opacity-20 text-8xl float-anim hidden md:block">🚀</div>
        </div>

        <div class="grid lg:grid-cols-12 gap-10 mb-24">
            <div class="lg:col-span-5" data-aos="fade-right">
                <div class="toy-card p-8 bg-toy-green/20 h-full flex flex-col justify-center">
                    <h3 class="text-3xl font-black mb-6 italic underline decoration-toy-pink decoration-8">About Us</h3>
                    <p class="text-lg font-bold leading-relaxed mb-6">
                        พวกเราคือกลุ่มนักพัฒนาที่หลงใหลในเทคโนโลยีและต้องการสร้างประสบการณ์การช้อปปิ้งออนไลน์ที่ดีที่สุด
                    </p>
                    <div class="flex items-center gap-3 bg-white p-3 border-2 border-black rounded-xl w-fit">
                        <span class="text-2xl">🏫</span>
                        <span class="font-bold uppercase italic">VCNC Nakhon Sawan</span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7" data-aos="fade-left">
                <div class="toy-card p-8">
                    <h3 class="text-2xl font-black mb-6 uppercase">Send Message 📮</h3>
                    
                    <?php if ($status_message): ?>
                        <div class="bg-toy-green border-4 border-black p-3 rounded-xl mb-4 font-black text-center">
                            <?= $status_message ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" class="space-y-4">
                        <div class="grid md:grid-cols-2 gap-4">
                            <input type="text" name="name" placeholder="ชื่อ" class="input-toy px-4 py-3 font-bold w-full" required>
                            <input type="email" name="email" placeholder="อีเมล" class="input-toy px-4 py-3 font-bold w-full" required>
                        </div>
                        <textarea name="message" rows="3" placeholder="ข้อความ..." class="input-toy px-4 py-3 font-bold w-full" required></textarea>
                        <button type="submit" class="btn-toy w-full py-4 bg-black text-white font-black text-lg hover:bg-pop-orange transition-all">
                            ส่งข้อมูล 🚀
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="text-center">
            <h3 class="text-4xl font-black mb-16 italic uppercase" data-aos="zoom-in">Our Creative Team</h3>
            
            <div class="grid grid-cols-2 md:grid-cols-5 gap-10">
                <?php 
                if (!empty($images)):
                    foreach ($images as $index => $image_path): 
                        $color_class = $colors[$index % count($colors)];
                        $display_name = $team_names[$index] ?? "Staff Member";
                ?>
                    <div class="group" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                        <div class="relative mb-6 transition-all duration-300 group-hover:scale-105">
                            <div class="absolute inset-0 <?= $color_class ?> border-4 border-black rounded-3xl translate-x-2 translate-y-2 group-hover:translate-x-3 group-hover:translate-y-3 transition-transform"></div>
                            
                            <div class="relative bg-white border-4 border-black rounded-3xl overflow-hidden p-2 aspect-square">
                                <img src="<?= $image_path ?>" alt="team" class="w-full h-full object-cover rounded-2xl">
                            </div>
                        </div>
                        <div class="bg-black text-white px-4 py-1 rounded-full text-xs font-black inline-block uppercase tracking-tighter">
                            <?= $display_name ?>
                        </div>
                    </div>
                <?php 
                    endforeach; 
                else:
                    echo "<div class='col-span-full toy-card p-10 font-black italic'>ไม่พบรูปภาพในโฟลเดอร์ Admin/</div>";
                endif;
                ?>
            </div>
        </div>
    </main>

    <footer class="py-10 text-center border-t-4 border-black bg-white">
        <p class="font-black text-lg uppercase italic">STUNSHOP.TOY &copy; 2026</p>
        <p class="font-bold text-xs opacity-60">วิทยาลัยอาชีวศึกษานครสวรรค์ - โครงงานคอมพิวเตอร์ธุรกิจ</p>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
    </script>
</body>
</html>