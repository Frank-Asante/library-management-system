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

// Fetch book details
$book = null;
$error_message = "";
if (isset($_GET['id'])) {
    $book_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    $stmt->close();
    if (!$book) {
        $error_message = "Book not found.";
    }
} else {
    $error_message = "No book ID provided.";
}

// Handle add to cart
if (isset($_POST['add_to_cart']) && $book) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (!in_array($book_id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $book_id;
    }
    $success_message = "Book added to cart!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Details - eBook Haven</title>
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

    <!-- Book Details -->
    <div class="container mx-auto py-12 px-4">
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Book Details</h2>
        <?php if ($error_message): ?>
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded text-center">
                <?php echo $error_message; ?>
                <p><a href="index.php" class="text-blue-600 underline">Return to Home</a></p>
            </div>
        <?php elseif (isset($success_message)): ?>
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded text-center">
                <?php echo $success_message; ?>
            </div>
        <?php else: ?>
            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6 flex flex-col md:flex-row">
                <div class="md:w-1/3">
                    <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="w-full h-96 object-cover rounded">
                </div>
                <div class="md:w-2/3 md:pl-6 mt-4 md:mt-0">
                    <h3 class="text-2xl font-semibold text-gray-800"><?php echo htmlspecialchars($book['title']); ?></h3>
                    <p class="text-gray-600 text-lg mt-2">by <?php echo htmlspecialchars($book['author']); ?></p>
                    <p class="text-blue-600 font-bold text-xl mt-4">₵<?php echo number_format($book['price'], 2); ?></p>
                    <p class="text-gray-600 mt-4">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                    <form method="POST" action="" class="mt-6">
                        <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                        <button type="submit" name="add_to_cart" class="bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700 transition">Add to Cart</button>
                    </form>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <div class="mt-4">
                            <a href="edit_book.php?id=<?php echo $book['id']; ?>" class="text-blue-600 hover:underline mr-4">Edit Book</a>
                            <form method="POST" action="dashboard.php" class="inline">
                                <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                <button type="submit" name="delete_book" class="text-red-600 hover:underline" onclick="return confirm('Are you sure you want to delete this book?');">Delete Book</button>
                            </form>
                        </div>
                    <?php endif; ?>
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