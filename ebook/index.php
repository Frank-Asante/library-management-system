<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "ebook_store");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    $book_id = $_POST['book_id'];
    if (!in_array($book_id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $book_id;
    }
}

// Fetch books
$result = $conn->query("SELECT * FROM books");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eBook Store - Home</title>
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
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="add_book.php" class="hover:underline font-medium">Add Book</a>
                        <a href="dashboard.php" class="hover:underline font-medium">Dashboard</a>
                    <?php endif; ?>
                    <a href="order_status.php" class="hover:underline font-medium">Order Status</a>
                    <span class="font-medium">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?><?php echo $_SESSION['role'] === 'admin' ? ' (Admin)' : ''; ?></span>
                    <a href="logout.php" class="hover:underline font-medium">Logout</a>
                <?php else: ?>
                    <a href="signin.php" class="hover:underline font-medium">Sign In</a>
                    <a href="signup.php" class="hover:underline font-medium">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="bg-blue-100 py-12 text-center">
        <div class="container mx-auto">
            <h2 class="text-4xl font-extrabold text-gray-800 mb-4">Discover Your Next Favorite eBook</h2>
            <p class="text-lg text-gray-600">Browse our collection of captivating stories and insightful reads.</p>
        </div>
    </div>

    <!-- Books Grid -->
    <div class="container mx-auto py-12 px-4">
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Featured eBooks</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php while ($book = $result->fetch_assoc()): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition duration-300">
                    <a href="book_details.php?id=<?php echo $book['id']; ?>">
                        <img src="<?php echo $book['cover_image']; ?>" alt="<?php echo $book['title']; ?>" class="w-full h-64 object-cover">
                    </a>
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-800 truncate"><?php echo $book['title']; ?></h3>
                        <p class="text-gray-600"><?php echo $book['author']; ?></p>
                        <p class="text-blue-600 font-bold mt-2">₵<?php echo number_format($book['price'], 2); ?></p>
                        <form method="POST" action="">
                            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                            <button type="submit" name="add_to_cart" class="mt-4 w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Add to Cart</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
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