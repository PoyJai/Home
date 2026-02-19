<?php
session_start();
require_once 'db_config.php'; 

// ตรวจสอบการ Login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header('location: login.php');
    exit;
}

// ระบบ Logout (จัดการผ่าน SweetAlert2 ในฝั่ง JS)
if (isset($_GET['logout'])) {
    session_destroy();
    header('location: login.php'); 
    exit;
}

$is_logged_in = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$current_username = $is_logged_in ? htmlspecialchars($_SESSION["username"]) : "Guest"; 
$current_role = $_SESSION["role"] ?? 'user';

// ดึงรูปมาทำ Slider
$sql = "SELECT image_url FROM games LIMIT 5";
$result = $conn->query($sql);
$slider_images = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) { $slider_images[] = $row['image_url']; }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>StunShop - About Our World! 🎮✨</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700;900&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'pop-yellow': '#FFEF00', 'pop-blue': '#00C2FF',
                        'pop-pink': '#FF48B0', 'pop-green': '#2DFF81', 'pop-orange': '#FF7A00',
                    },
                }
            }
        }
    </script>
    <style>
        * { border-radius: 0 !important; font-family: 'Kanit', sans-serif; }
        .pop-btn { border: 3px solid #000; box-shadow: 4px 4px 0px #000; transition: all 0.1s; cursor: pointer; }
        .pop-card { background: white; border: 3px solid #000; box-shadow: 6px 6px 0px #000; }
        .pop-btn:active { transform: translate(3px, 3px); box-shadow: 0px 0px 0px #000; }
        .slider-container { position: relative; overflow: hidden; border: 4px solid #000; box-shadow: 10px 10px 0px #000; }
        
        /* สไตล์ปุ่ม Pill Capsule */
        .pill-capsule { border-radius: 50px !important; border: 2.5px solid #000; }
        .admin-badge { font-size: 10px; color: #FF48B0; font-weight: 900; position: relative; padding-left: 12px; }
        .admin-badge::before { content: ''; position: absolute; left: 0; top: 4px; width: 6px; height: 6px; background: #FF48B0; border-radius: 50% !important; }
    </style>
</head>
<body class="text-black bg-white selection:bg-pop-pink selection:text-white">

    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b-4 border-black">
        <nav class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="index.php" class="text-2xl md:text-4xl font-black tracking-tighter flex items-center group">
                <span class="bg-pop-yellow border-2 border-black px-2 py-0.5 italic shadow-[3px_3px_0px_#000]">STUN</span>
                <span class="ml-1 uppercase">Shop</span>
            </a>

            <div class="hidden md:flex space-x-6 items-center font-bold italic">
                <a href="index.php" class="hover:text-pop-pink transition underline underline-offset-4">หน้าแรก</a>
                <a href="allgame.php" class="hover:text-pop-blue transition">คลังเกม</a>
                <a href="contact.php" class="hover:text-pop-green transition">ติดต่อเรา</a>

                <?php if ($current_role === 'admin'): ?>
                    <div class="flex items-center space-x-3 bg-[#F3F4F6] pill-capsule px-4 py-1.5 shadow-[3px_3px_0px_#000]">
                        <div class="flex flex-col leading-none">
                            <span class="admin-badge uppercase">Admin Mode</span>
                            <span class="text-sm font-black italic"><?= $current_username ?></span>
                        </div>
                        <a href="admin_panel.php" class="bg-pop-yellow border-2 border-black p-1.5 shadow-[2px_2px_0px_#000] hover:scale-110 transition-transform" style="border-radius: 8px !important;">
                            <img src="https://img.icons8.com/ios-filled/20/000000/settings.png" class="w-4 h-4">
                        </a>
                        <button onclick="confirmLogout()" class="hover:opacity-70 transition-opacity">
                            <img src="https://img.icons8.com/ios/24/FF48B0/logout-rounded-left.png" class="w-6 h-6">
                        </button>
                    </div>
                <?php else: ?>
                    <button onclick="confirmLogout()" class="flex items-center space-x-3 bg-black text-white pill-capsule px-5 py-2 hover:scale-105 transition shadow-[4px_4px_0px_#FF48B0]">
                        <img src="https://img.icons8.com/ios-filled/20/7E57C2/user-male-circle.png" class="w-5 h-5">
                        <span class="text-sm font-black italic uppercase"><?= $current_username ?></span>
                    </button>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-4 py-8 md:py-12">
        <div class="bg-pop-orange border-4 border-black p-6 md:p-12 mb-10 relative overflow-hidden shadow-[10px_10px_0px_#000]" data-aos="zoom-in">
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between">
                <div class="text-center md:text-left">
                    <h1 class="text-5xl md:text-8xl font-black text-white uppercase mb-4 [text-shadow:5px_5px_0px_#000] leading-tight">
                        STUN<br>INSIDE!
                    </h1>
                    <p class="text-sm md:text-2xl font-bold text-black bg-white inline-block px-4 py-1 border-2 border-black rotate-2 shadow-[4px_4px_0px_#000]">
                        ข้อมูลเจาะลึกของร้านเรา 📂
                    </p>
                </div>
                <div class="mt-8 md:mt-0 w-full md:w-1/2 max-w-lg">
                    <div class="slider-container aspect-video bg-black">
                        <img id="slider-img" src="<?= !empty($slider_images) ? $slider_images[0] : 'https://placehold.co/600x400?text=STUNSHOP' ?>" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
            <div class="pop-card p-6 bg-pop-blue" data-aos="fade-up" data-aos-delay="100">
                <h3 class="text-3xl font-black italic mb-4 uppercase border-b-4 border-black inline-block">Stats 📈</h3>
                <div class="space-y-4 mt-4 font-black">
                    <div class="bg-white border-2 border-black p-3 flex justify-between items-center">
                        <span>เกมในคลัง</span>
                        <span class="text-2xl text-pop-pink">100+</span>
                    </div>
                    <div class="bg-white border-2 border-black p-3 flex justify-between items-center">
                        <span>ออเดอร์ทั้งหมด</span>
                        <span class="text-2xl text-pop-pink">10,000+</span>
                    </div>
                </div>
            </div>

            <div class="pop-card p-6 bg-pop-green" data-aos="fade-up" data-aos-delay="200">
                <h3 class="text-3xl font-black italic mb-4 uppercase border-b-4 border-black inline-block">Service ✨</h3>
                <ul class="space-y-3 mt-4 font-bold text-sm">
                    <li class="flex items-center gap-2"><span class="bg-black text-white px-2 italic font-black">01</span> ระบบออโต้ 24 ชั่วโมง</li>
                    <li class="flex items-center gap-2"><span class="bg-black text-white px-2 italic font-black">02</span> รับประกัน ID ตลอดชีพ</li>
                    <li class="flex items-center gap-2"><span class="bg-black text-white px-2 italic font-black">03</span> ราคาถูกที่สุดในย่านนี้</li>
                </ul>
            </div>

            <div class="pop-card p-6 bg-pop-yellow" data-aos="fade-up" data-aos-delay="300">
                <h3 class="text-3xl font-black italic mb-4 uppercase border-b-4 border-black inline-block">Support 📞</h3>
                <div class="space-y-3 mt-4">
                    <a href="#" class="pop-btn bg-white w-full p-2 flex items-center justify-center gap-2 font-black italic text-sm">FACEBOOK: STUNSHOP</a>
                    <a href="#" class="pop-btn bg-pop-pink text-white w-full p-2 flex items-center justify-center gap-2 font-black italic text-sm">JOIN DISCORD</a>
                </div>
            </div>
        </div>

        <div class="pop-card p-8 bg-white mb-10" data-aos="fade-right">

            <h2 class="text-4xl font-black italic uppercase mb-6 underline decoration-pop-pink">Mission & Vision 🚀</h2>

            <p class="text-lg font-bold leading-relaxed">

                <span class="bg-pop-blue px-2">StunShop</span> เริ่มต้นจากการเป็นโปรเจกต์เล็กๆ ในวิทยาลัยอาชีวศึกษานครสวรรค์ 

                โดยมีเป้าหมายเพื่อสร้างแพลตฟอร์มการซื้อขายเกมที่ปลอดภัย รวดเร็ว และเป็นมิตรกับผู้ใช้งานมากที่สุด 

                เราไม่เพียงแค่ขายเกม แต่เราสร้าง "สังคมเกมเมอร์" ที่ดีและมีคุณภาพ

            </p>

            <div class="mt-8 flex flex-wrap gap-4">

                <div class="bg-black text-white px-4 py-2 font-black italic">- รวดเร็ว -</div>

                <div class="bg-black text-white px-4 py-2 font-black italic">- ปลอดภัย -</div>

                <div class="bg-black text-white px-4 py-2 font-black italic">- จริงใจ -</div>

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

        // ระบบ Logout ด้วย SweetAlert2
        function confirmLogout() {
            Swal.fire({
                title: 'ยืนยันการออกจากระบบ?',
                text: "คุณต้องการออกจากเซสชันปัจจุบันใช่หรือไม่",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#000000',
                cancelButtonColor: '#FF48B0',
                confirmButtonText: 'ใช่, ออกจากระบบ',
                cancelButtonText: 'ยกเลิก',
                background: '#ffffff',
                color: '#000000',
                customClass: {
                    popup: 'border-4 border-black shadow-[8px_8px_0px_#000]'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'index.php?logout=1';
                }
            })
        }
        
        // Slider Logic
        const sliderImg = document.getElementById('slider-img');
        const gameImages = <?= json_encode($slider_images) ?>;
        let idx = 0;
        if(gameImages && gameImages.length > 0) {
            setInterval(() => {
                idx = (idx + 1) % gameImages.length;
                sliderImg.style.opacity = 0;
                setTimeout(() => {
                    sliderImg.src = gameImages[idx];
                    sliderImg.style.opacity = 1;
                }, 200);
            }, 4000);
        }
    </script>
</body>
</html>