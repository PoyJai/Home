<?php
session_start();
require_once 'db_config.php';

// 1. เช็คสิทธิ์ Admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== 'admin') {
    header("location: index.php");
    exit;
}

// --- ส่วนจัดการการยืนยันออเดอร์ ---
$success_msg = "";
if (isset($_GET['action']) && $_GET['action'] == 'confirm_order' && isset($_GET['order_id'])) {
    $order_id = mysqli_real_escape_string($conn, $_GET['order_id']);
    $update_sql = "UPDATE orders SET status = 'completed' WHERE id = '$order_id'";
    
    if (mysqli_query($conn, $update_sql)) {
        $res = mysqli_query($conn, "SELECT email FROM orders WHERE id = '$order_id'");
        $order_data = mysqli_fetch_assoc($res);
        $target_email = $order_data['email'] ?? 'ลูกค้า';
        $success_msg = "ยืนยันออเดอร์ #$order_id และส่งเมลไปที่ $target_email เรียบร้อย!";
    }
}

// รับค่า Tab
$tab = $_GET['tab'] ?? 'games';

// ดึงข้อมูลตาม Tab
if ($tab == 'orders') {
    $sql = "SELECT * FROM orders ORDER BY created_at DESC";
    $result = $conn->query($sql);
} elseif ($tab == 'contacts') {
    $sql = "SELECT * FROM contacts ORDER BY created_at DESC";
    $result = $conn->query($sql);
} else {
    $sql = "SELECT * FROM games ORDER BY id ASC";
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin System | StunShop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f3f4f6; }
        .neo-brutal { border: 3px solid #000; box-shadow: 6px 6px 0px #000; }
        .sidebar-item { transition: all 0.2s; border: 2px solid transparent; }
        .sidebar-item.active { background: #000; color: white; border: 2px solid #000; transform: translate(4px, 0); }
        .btn-action { border: 2px solid #000; box-shadow: 2px 2px 0px #000; transition: all 0.1s; cursor: pointer; }
        .btn-action:active { transform: translate(1px, 1px); box-shadow: 0px 0px 0px #000; }
        .swal-neo { border: 4px solid #000 !important; border-radius: 20px !important; box-shadow: 10px 10px 0px #000 !important; }
    </style>
</head>
<body class="bg-gray-100 flex flex-col md:flex-row min-h-screen">

    <aside class="w-full md:w-64 bg-white border-r-4 border-black p-6 flex flex-col no-print">
        <div class="mb-10 text-center">
            <h2 class="text-3xl font-black italic border-4 border-black inline-block px-4 py-1 bg-yellow-300 shadow-[4px_4px_0px_#000]">STUN</h2>
            <p class="text-xs font-black mt-2 uppercase tracking-widest text-gray-400">Admin Control</p>
        </div>
        <nav class="flex-grow space-y-4">
            <a href="?tab=games" class="sidebar-item flex items-center gap-3 p-3 font-black italic uppercase <?= $tab == 'games' ? 'active' : 'hover:bg-gray-100' ?>">🎮 คลังสินค้า</a>
            <a href="?tab=orders" class="sidebar-item flex items-center gap-3 p-3 font-black italic uppercase <?= $tab == 'orders' ? 'active' : 'hover:bg-gray-100' ?>">📦 ออเดอร์</a>
            <a href="?tab=contacts" class="sidebar-item flex items-center gap-3 p-3 font-black italic uppercase <?= $tab == 'contacts' ? 'active' : 'hover:bg-gray-100' ?>">✉️ สนับสนุน</a>
        </nav>
        <div class="mt-auto pt-6 border-t-2 border-black">
            <a href="index.php" class="text-sm font-black text-red-500 hover:underline">← กลับหน้าบ้าน</a>
        </div>
    </aside>

    <main class="flex-grow p-4 md:p-8">
        <div class="max-w-6xl mx-auto bg-white neo-brutal p-6 md:p-8 rounded-3xl">
            
            <?php if ($tab == 'games'): ?>
                <div class="flex justify-between items-end mb-6 border-b-4 border-black pb-4">
                    <h1 class="text-3xl font-black italic uppercase underline decoration-yellow-400">GAME MANAGEMENT 🕹️</h1>
                    <a href="admin_manage_game.php" class="btn-action bg-green-400 px-4 py-2 font-black rounded-xl text-sm">+ เพิ่มเกม</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-black text-white text-[10px] uppercase font-black">
                                <th class="p-3 border-2 border-black">ID</th>
                                <th class="p-3 border-2 border-black">รูป</th>
                                <th class="p-3 border-2 border-black">ชื่อเกม</th>
                                <th class="p-3 border-2 border-black text-right">ราคา</th>
                                <th class="p-3 border-2 border-black text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="font-bold text-sm">
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="border-b-2 border-black hover:bg-gray-50">
                                <td class="p-3 border border-black text-gray-400">#<?= $row['id'] ?></td>
                                <td class="p-3 border border-black"><img src="<?= $row['image_url'] ?>" class="w-16 h-10 object-cover border-2 border-black"></td>
                                <td class="p-3 border border-black"><?= htmlspecialchars($row['title']) ?></td>
                                <td class="p-3 border border-black text-right">฿<?= number_format($row['price'], 2) ?></td>
                                <td class="p-3 border border-black text-center font-black">
                                    <a href="admin_manage_game.php?id=<?= $row['id'] ?>" class="text-blue-500 hover:underline mr-2">แก้ไข</a>
                                    <button onclick="confirmDelete(<?= $row['id'] ?>)" class="text-red-500 hover:underline">ลบ</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($tab == 'orders'): ?>
                <div class="mb-6 border-b-4 border-black pb-4 text-blue-600">
                    <h1 class="text-3xl font-black italic uppercase underline decoration-black">ORDER LISTS 📦</h1>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-black text-white text-[10px] uppercase font-black">
                                <th class="p-3 border-2 border-black">No.</th>
                                <th class="p-3 border-2 border-black">ผู้ซื้อ</th>
                                <th class="p-3 border-2 border-black text-right">ยอดรวม</th>
                                <th class="p-3 border-2 border-black text-center">หลักฐาน</th>
                                <th class="p-3 border-2 border-black text-center">สถานะ</th>
                                <th class="p-3 border-2 border-black text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="font-bold text-sm">
                            <?php if($result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
                            <tr class="border-b-2 border-black hover:bg-gray-50">
                                <td class="p-3 border border-black text-gray-400">#<?= $row['id'] ?></td>
                                <td class="p-3 border border-black">
                                    <div class="flex flex-col">
                                        <span class="uppercase"><?= htmlspecialchars($row['customer_name'] ?? 'Guest') ?></span>
                                        <span class="text-[10px] text-blue-500 font-normal"><?= htmlspecialchars($row['email']) ?></span>
                                    </div>
                                </td>
                                <td class="p-3 border border-black text-right text-lg font-black">฿<?= number_format($row['total_price'], 2) ?></td>
                                <td class="p-3 border border-black text-center">
                                    <?php if(!empty($row['slip_image'])): ?>
                                        <a href="uploads/slips/<?= $row['slip_image'] ?>" target="_blank" class="btn-action bg-green-400 px-2 py-1 text-[10px] rounded inline-block">🖼️ ดูสลิป</a>
                                    <?php else: ?>
                                        <span class="text-gray-300 italic text-[10px]">ไม่มีสลิป</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3 border border-black text-center">
                                    <?php if($row['status'] == 'waiting_verify'): ?>
                                        <span class="bg-yellow-300 px-2 py-1 border-2 border-black text-[10px]">รอตรวจสอบ</span>
                                    <?php elseif($row['status'] == 'completed'): ?>
                                        <span class="bg-blue-500 text-white px-2 py-1 border-2 border-black text-[10px]">ส่งเมลแล้ว</span>
                                    <?php else: ?>
                                        <span class="bg-gray-100 px-2 py-1 border-2 border-black text-[10px]"><?= $row['status'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3 border border-black text-center">
                                    <div class="flex gap-2 justify-center">
                                        <?php if($row['status'] == 'waiting_verify'): ?>
                                            <button onclick="confirmOrder(<?= $row['id'] ?>)" class="btn-action bg-black text-white px-3 py-1 text-[10px] uppercase font-black">✅ ยืนยัน & ส่งเมล</button>
                                        <?php endif; ?>
                                        <a href="print_receipt.php?id=<?= $row['id'] ?>" target="_blank" class="btn-action bg-yellow-300 px-3 py-1 text-[10px] font-black italic">📄 ใบเสร็จ</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="6" class="p-10 text-center italic opacity-50 font-black">ไม่มีข้อมูลออเดอร์</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($tab == 'contacts'): ?>
                <div class="mb-6 border-b-4 border-black pb-4 text-orange-500">
                    <h1 class="text-3xl font-black italic uppercase underline decoration-black">SUPPORT TICKETS ✉️</h1>
                </div>
                <div class="grid grid-cols-1 gap-6">
                    <?php if($result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
                    <div class="border-4 border-black p-5 bg-white shadow-[6px_6px_0px_#000]">
                        <div class="flex justify-between items-start mb-3 border-b-2 border-black pb-2">
                            <div>
                                <h3 class="font-black text-lg uppercase"><?= htmlspecialchars($row['name']) ?></h3>
                                <p class="text-[10px] text-blue-500 font-bold"><?= htmlspecialchars($row['email']) ?></p>
                            </div>
                            <span class="text-[9px] font-black text-gray-400 italic"><?= $row['created_at'] ?></span>
                        </div>
                        <div class="text-sm font-black italic text-pink-500 mb-2">TOPIC: <?= htmlspecialchars($row['subject']) ?></div>
                        <div class="text-sm bg-yellow-50 p-4 border-2 border-dashed border-black mb-4 font-bold text-gray-700">"<?= htmlspecialchars($row['message']) ?>"</div>
                        <a href="mailto:<?= $row['email'] ?>?subject=Re: <?= $row['subject'] ?>" class="btn-action bg-black text-white px-4 py-2 text-xs font-black inline-block italic uppercase">📧 REPLY TO CUSTOMER</a>
                    </div>
                    <?php endwhile; else: ?>
                        <div class="text-center py-20 opacity-30 italic border-4 border-dashed border-black font-black uppercase">ไม่มีข้อความสนับสนุนใหม่</div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <script>
    // แจ้งเตือนยืนยันออเดอร์
    function confirmOrder(orderId) {
        Swal.fire({
            title: 'ยืนยันการชำระเงิน?',
            text: "ระบบจะทำการอัปเดตสถานะและส่งใบเสร็จไปที่อีเมลลูกค้า",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#000',
            cancelButtonColor: '#ff4b4b',
            confirmButtonText: 'ยืนยันและส่งเมล',
            cancelButtonText: 'ยกเลิก',
            customClass: { popup: 'swal-neo' }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '?action=confirm_order&order_id=' + orderId + '&tab=orders';
            }
        })
    }

    // แจ้งเตือนลบเกม
    function confirmDelete(gameId) {
        Swal.fire({
            title: 'ต้องการลบเกมนี้?',
            text: "เมื่อลบแล้วจะไม่สามารถกู้คืนข้อมูลได้",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#ff4b4b',
            cancelButtonColor: '#000',
            confirmButtonText: 'ลบเลย!',
            cancelButtonText: 'ยกเลิก',
            customClass: { popup: 'swal-neo' }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'admin_delete.php?id=' + gameId;
            }
        })
    }

    // แสดงผลความสำเร็จเมื่อ PHP ทำงานเสร็จ
    <?php if ($success_msg): ?>
    Swal.fire({
        title: 'สำเร็จ!',
        text: '<?= $success_msg ?>',
        icon: 'success',
        confirmButtonColor: '#000',
        customClass: { popup: 'swal-neo' }
    });
    <?php endif; ?>
    </script>
</body>
</html>