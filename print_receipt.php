<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== 'admin') {
    die("Access Denied");
}

$order_id = $_GET['id'] ?? $_GET['order_id'] ?? 0;

if (!$order_id) { die("ไม่พบรหัสใบสั่งซื้อ"); }

$order_sql = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($order_sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) { die("ไม่พบรายการ"); }

$items_sql = "SELECT * FROM order_items WHERE order_id = ?";
$stmt_items = $conn->prepare($items_sql);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items_res = $stmt_items->get_result();

function generateGameKey() {
    return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4) . '-' . 
                      substr(md5(uniqid(mt_rand(), true)), 0, 4) . '-' . 
                      substr(md5(uniqid(mt_rand(), true)), 0, 4));
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StunShop Receipt #<?= $order_id ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Kanit', sans-serif; 
            background-color: #f3f4f6;
        }
        @media print {
            .no-print { display: none; }
            body { background-color: white; padding: 0; }
            .receipt-container { box-shadow: none !important; border: 2px solid black !important; margin: 0 auto; }
        }
        .neo-shadow {
            box-shadow: 10px 10px 0px #000;
        }
        .zigzag-bottom {
            background: linear-gradient(-45deg, transparent 16px, white 0), linear-gradient(45deg, transparent 16px, white 0);
            background-repeat: repeat-x;
            background-size: 22px 32px;
            display: block;
            height: 32px;
            width: 100%;
            position: relative;
            bottom: -32px;
            left: 0;
        }
        .game-key-box {
            background-image: radial-gradient(#000 1px, transparent 1px);
            background-size: 10px 10px;
            background-color: #FDF7C3;
        }
    </style>
</head>
<body class="p-4 md:p-10 flex flex-col items-center">

    <div class="no-print mb-8">
        <button onclick="window.print()" class="bg-blue-400 border-4 border-black px-8 py-3 font-black text-xl uppercase italic neo-shadow hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all">
            🖨️ Print Receipt
        </button>
    </div>

    <div class="receipt-container w-full max-w-md bg-white border-4 border-black p-0 relative neo-shadow">
        
        <div class="bg-black text-white p-6 text-center">
            <h1 class="text-4xl font-black tracking-tighter italic">STUNSHOP</h1>
            <p class="text-xs font-bold text-blue-400 uppercase tracking-widest">Digital Gaming Store</p>
        </div>

        <div class="p-6">
            <div class="flex justify-between items-start border-b-4 border-black pb-4 mb-4">
                <div class="text-xs font-black uppercase">
                    <p class="text-gray-400">Order ID</p>
                    <p class="text-lg italic">#<?= str_pad($order_id, 6, '0', STR_PAD_LEFT) ?></p>
                </div>
                <div class="text-xs font-black uppercase text-right">
                    <p class="text-gray-400">Date</p>
                    <p><?= date('d M Y / H:i', strtotime($order['created_at'])) ?></p>
                </div>
            </div>

            <div class="mb-6">
                <p class="text-[10px] font-black uppercase text-gray-400 leading-none mb-1">Customer Info</p>
                <h3 class="font-black text-xl truncate"><?= htmlspecialchars($order['customer_name'] ?? 'Guest Player') ?></h3>
                <p class="text-sm font-bold text-blue-600"><?= htmlspecialchars($order['email'] ?? '-') ?></p>
            </div>

            <div class="space-y-6">
                <p class="text-[10px] font-black uppercase bg-black text-white px-2 py-1 inline-block">Purchased Items</p>
                
                <?php while($item = $items_res->fetch_assoc()): ?>
                <div class="relative">
                    <div class="flex justify-between font-black text-lg">
                        <span>🎮 <?= htmlspecialchars($item['game_title']) ?></span>
                        <span>฿<?= number_format($item['price'], 2) ?></span>
                    </div>
                    <p class="text-xs font-bold text-gray-500 mb-2 italic">Qty: <?= htmlspecialchars($item['quantity'] ?? '1') ?></p>
                    
                    <div class="game-key-box border-2 border-black p-3 text-center">
                        <p class="text-[9px] font-black uppercase tracking-widest mb-1 text-gray-600">Activation Code</p>
                        <p class="font-mono font-black text-lg text-pink-600 select-all tracking-wider"><?= generateGameKey() ?></p>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="mt-10 border-t-4 border-black pt-4 flex justify-between items-end">
                <div class="font-black italic text-sm leading-tight uppercase">
                    Total<br>Amount
                </div>
                <div class="text-4xl font-black italic text-pink-500 tracking-tighter">
                    ฿<?= number_format($order['total_price'], 2) ?>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t-2 border-dashed border-gray-300 text-center">
                <p class="text-[10px] font-black uppercase leading-tight italic">
                    Thank you for supporting STUNSHOP.<br>
                    Please activate your key in the respective launcher.<br>
                    Support: @STUNSHOP_HELP
                </p>
            </div>
        </div>

        <div class="zigzag-bottom no-print"></div>
    </div>

    <p class="mt-16 text-[10px] font-black text-gray-400 no-print uppercase tracking-widest">
        &copy; 2026 STUNSHOP BACK-OFFICE SYSTEM
    </p>

</body>
</html>