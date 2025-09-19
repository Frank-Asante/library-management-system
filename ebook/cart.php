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

// Handle remove from cart
if (isset($_POST['remove_from_cart'])) {
    $book_id = $_POST['book_id'];
    $_SESSION['cart'] = array_diff($_SESSION['cart'], [$book_id]);
}

// Handle checkout
$success_message = "";
$error_message = "";
if (isset($_POST['checkout'])) {
    if (empty($_SESSION['cart'])) {
        $error_message = "Your cart is empty.";
    } else {
        // Calculate total
        $total = 0;
        $cart_books = [];
        $ids = implode(",", $_SESSION['cart']);
        $result = $conn->query("SELECT * FROM books WHERE id IN ($ids)");
        while ($book = $result->fetch_assoc()) {
            $cart_books[] = $book;
            $total += $book['price'];
        }

        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'Received')");
        $stmt->bind_param("id", $_SESSION['user_id'], $total);
        if ($stmt->execute()) {
            $order_id = $conn->insert_id;

            // Insert order items
            $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, book_id, title, price) VALUES (?, ?, ?, ?)");
            foreach ($cart_books as $book) {
                $stmt_items->bind_param("iisd", $order_id, $book['id'], $book['title'], $book['price']);
                $stmt_items->execute();
            }
            $stmt_items->close();

            // Clear cart
            $_SESSION['cart'] = [];
            $success_message = "Order placed successfully! Check your order status <a href='order_status.php' class='text-blue-600 underline'>here</a>.";
        } else {
            $error_message = "Error placing order: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch cart books
$cart_books = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    $ids = implode(",", $_SESSION['cart']);
    $result = $conn->query("SELECT * FROM books WHERE id IN ($ids)");
    while ($book = $result->fetch_assoc()) {
        $cart_books[] = $book;
        $total += $book['price'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - eBook Haven</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-blue-700 p-4 text-white shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold tracking-tight">eBook Haven</h1>
            <div class="space-x-6">
                <a href="index.php" class="hover:underline font-medium">Home</a>
                <a href="cart.php" class="hover:underline font-medium">Cart (<?php echo count($_SESSION['cart']); ?>)</a>
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

    <!-- Cart -->
    <div class="container mx-auto py-12 px-4">
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Your Cart</h2>
        <?php if ($success_message): ?>
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        <?php if (empty($cart_books)): ?>
            <p class="text-center text-gray-600">Your cart is empty.</p>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <?php foreach ($cart_books as $book): ?>
                    <div class="flex items-center border-b py-4">
                        <img src="<?php echo $book['cover_image']; ?>" alt="<?php echo $book['title']; ?>" class="w-24 h-32 object-cover rounded mr-4">
                        <div class="flex-1">
                            <h3 class="text-xl font-semibold text-gray-800"><?php echo $book['title']; ?></h3>
                            <p class="text-gray-600"><?php echo $book['author']; ?></p>
                            <p class="text-blue-600 font-bold">$<?php echo number_format($book['price'], 2); ?></p>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                            <button type="submit" name="remove_from_cart" class="text-red-600 hover:underline">Remove</button>
                        </form>
                    </div>
                <?php endforeach; ?>
                <div class="text-right mt-4">
                    <p class="text-xl font-bold text-gray-800">Total: $<?php echo number_format($total, 2); ?></p>
                    <form method="POST" action="">
                        <button type="submit" name="checkout" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Proceed to Checkout (Pay in Cash)</button>
                    </form>
                </div>
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