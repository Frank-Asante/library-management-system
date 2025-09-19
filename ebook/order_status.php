<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "ebook_store");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user's orders
$stmt = $conn->prepare("SELECT o.id, o.total, o.status, o.created_at FROM orders o WHERE o.user_id = ? ORDER BY o.created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status - eBook Haven</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-blue-700 p-4 text-white shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold tracking-tight">eBook Haven</h1>
            <div class="space-x-6">
                <a href="index.php" class="hover:underline font-medium">Home</a>
                <a href="cart.php" class="hover:underline font-medium">Cart (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="add_book.php" class="hover:underline font-medium">Add Book</a>
                    <a href="dashboard.php" class="hover:underline font-medium">Dashboard</a>
                <?php endif; ?>
                <a href="order_status.php" class="hover:underline font-medium">Order Status</a>
                <span class="font-medium">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?><?php echo $_SESSION['role'] === 'admin' ? ' (Admin)' : ''; ?></span>
                <a href="logout.php" class="hover:underline font-medium">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Order Status -->
    <div class="container mx-auto py-12 px-4">
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Your Order Status</h2>
        <?php if ($orders_result->num_rows == 0): ?>
            <p class="text-center text-gray-600">You have no orders.</p>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-3">Order ID</th>
                            <th class="p-3">Total</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Created At</th>
                            <th class="p-3">Items</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="p-3"><?php echo $order['id']; ?></td>
                                <td class="p-3">$<?php echo number_format($order['total'], 2); ?></td>
                                <td class="p-3"><?php echo $order['status']; ?></td>
                                <td class="p-3"><?php echo $order['created_at']; ?></td>
                                <td class="p-3">
                                    <?php
                                    $stmt_items = $conn->prepare("SELECT title, price FROM order_items WHERE order_id = ?");
                                    $stmt_items->bind_param("i", $order['id']);
                                    $stmt_items->execute();
                                    $items_result = $stmt_items->get_result();
                                    while ($item = $items_result->fetch_assoc()) {
                                        echo htmlspecialchars($item['title']) . " ($" . number_format($item['price'], 2) . ")<br>";
                                    }
                                    $stmt_items->close();
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6">
        <div class="container mx-auto text-center">
            <p>© 2025 eBook Haven. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

<?php $conn->close(); ?>