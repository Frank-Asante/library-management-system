<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in or not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "ebook_store");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch book details
$book = null;
if (isset($_GET['id'])) {
    $book_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    $stmt->close();
    if (!$book) {
        header("Location: dashboard.php");
        exit;
    }
} else {
    header("Location: dashboard.php");
    exit;
}

// Handle form submission
$success_message = "";
$error_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $price = floatval($_POST['price']);
    $cover_image = trim($_POST['cover_image']);

    // Basic validation
    if (empty($title) || empty($author) || $price <= 0 || empty($cover_image)) {
        $error_message = "All fields are required, and price must be greater than 0.";
    } else {
        // Sanitize inputs
        $title = $conn->real_escape_string($title);
        $author = $conn->real_escape_string($author);
        $cover_image = $conn->real_escape_string($cover_image);

        // Update book
        $stmt = $conn->prepare("UPDATE books SET title = ?, author = ?, price = ?, cover_image = ? WHERE id = ?");
        $stmt->bind_param("ssdsi", $title, $author, $price, $cover_image, $book_id);
        if ($stmt->execute()) {
            $success_message = "Book updated successfully! <a href='dashboard.php' class='text-blue-600 underline'>Back to Dashboard</a>";
        } else {
            $error_message = "Error updating book: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book - eBook Haven</title>
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
                <a href="add_book.php" class="hover:underline font-medium">Add Book</a>
                <a href="dashboard.php" class="hover:underline font-medium">Dashboard</a>
                <a href="order_status.php" class="hover:underline font-medium">Order Status</a>
                <span class="font-medium">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (Admin)</span>
                <a href="logout.php" class="hover:underline font-medium">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Edit Book Form -->
    <div class="container mx-auto py-12 px-4">
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Edit Book</h2>
        <div class="max-w-lg mx-auto bg-white rounded-lg shadow-lg p-6">
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
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="title" class="block text-gray-700 font-medium mb-2">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                </div>
                <div class="mb-4">
                    <label for="author" class="block text-gray-700 font-medium mb-2">Author</label>
                    <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                </div>
                <div class="mb-4">
                    <label for="price" class="block text-gray-700 font-medium mb-2">Price ($)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $book['price']; ?>" class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                </div>
                <div class="mb-4">
                    <label for="cover_image" class="block text-gray-700 font-medium mb-2">Cover Image URL</label>
                    <input type="url" id="cover_image" name="cover_image" value="<?php echo htmlspecialchars($book['cover_image']); ?>" class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-3 rounded hover:bg-blue-700 transition">Update Book</button>
            </form>
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