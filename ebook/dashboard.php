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

// Handle book deletion
if (isset($_POST['delete_book'])) {
    $book_id = $_POST['book_id'];
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $stmt->close();
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    // Prevent deleting the current user
    if ($user_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Handle order status update
$success_message = "";
$error_message = "";
if (isset($_POST['update_order_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    if (!in_array($status, ['Received', 'Processing', 'Dispatched', 'Delivered'])) {
        $error_message = "Invalid status selected.";
    } else {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);
        if ($stmt->execute()) {
            $success_message = "Order status updated successfully.";
        } else {
            $error_message = "Error updating order status: " . $conn->error;
        }
        $stmt->close();
    }
}

// Handle user role update
if (isset($_POST['update_user_role'])) {
    $user_id = $_POST['user_id'];
    $role = $_POST['role'];
    if ($user_id != $_SESSION['user_id'] && in_array($role, ['admin', 'user'])) {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $role, $user_id);
        if ($stmt->execute()) {
            $success_message = "User role updated successfully.";
        } else {
            $error_message = "Error updating user role: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error_message = "Cannot change your own role or invalid role selected.";
    }
}

// Fetch books
$books_result = $conn->query("SELECT * FROM books");

// Fetch users
$users_result = $conn->query("SELECT id, username, email, role, created_at FROM users");

// Fetch orders with user details
$orders_result = $conn->query("SELECT o.id, o.user_id, o.total, o.status, o.created_at, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - eBook Haven</title>
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

    <!-- Dashboard -->
    <div class="container mx-auto py-12 px-4">
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Admin Dashboard</h2>

        <!-- Success/Error Messages -->
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

        <!-- Books Section -->
        <div class="mb-12">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Manage Books</h3>
            <div class="bg-white rounded-lg shadow-md p-6">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-3">ID</th>
                            <th class="p-3">Title</th>
                            <th class="p-3">Author</th>
                            <th class="p-3">Price</th>
                            <th class="p-3">Cover Image</th>
                            <th class="p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($book = $books_result->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="p-3"><?php echo $book['id']; ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($book['title']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($book['author']); ?></td>
                                <td class="p-3">₵<?php echo number_format($book['price'], 2); ?></td>
                                <td class="p-3">
                                    <img src="<?php echo $book['cover_image']; ?>" alt="<?php echo $book['title']; ?>" class="w-16 h-20 object-cover">
                                </td>
                                <td class="p-3">
                                    <a href="edit_book.php?id=<?php echo $book['id']; ?>" class="text-blue-600 hover:underline mr-4">Edit</a>
                                    <form method="POST" action="" class="inline">
                                        <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                        <button type="submit" name="delete_book" class="text-red-600 hover:underline" onclick="return confirm('Are you sure you want to delete this book?');">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Users Section -->
        <div class="mb-12">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Manage Users</h3>
            <div class="bg-white rounded-lg shadow-md p-6">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-3">ID</th>
                            <th class="p-3">Username</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">Role</th>
                            <th class="p-3">Created At</th>
                            <th class="p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users_result->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="p-3"><?php echo $user['id']; ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="p-3"><?php echo $user['role']; ?></td>
                                <td class="p-3"><?php echo $user['created_at']; ?></td>
                                <td class="p-3">
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" action="" class="inline mr-4">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="role" class="border rounded p-1">
                                                <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                            <button type="submit" name="update_user_role" class="text-blue-600 hover:underline" onclick="return confirm('Are you sure you want to update the role for <?php echo htmlspecialchars($user['username']); ?>?');">Update</button>
                                        </form>
                                        <form method="POST" action="" class="inline">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" name="delete_user" class="text-red-600 hover:underline" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-gray-500">Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Orders Section -->
        <div>
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Process Orders</h3>
            <div class="bg-white rounded-lg shadow-md p-6">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-3">Order ID</th>
                            <th class="p-3">User</th>
                            <th class="p-3">Total</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Created At</th>
                            <th class="p-3">Items</th>
                            <th class="p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="p-3"><?php echo $order['id']; ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($order['username']); ?></td>
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
                                <td class="p-3">
                                    <form method="POST" action="" class="inline">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" class="border rounded p-1">
                                            <option value="Received" <?php echo $order['status'] == 'Received' ? 'selected' : ''; ?>>Received</option>
                                            <option value="Processing" <?php echo $order['status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="Dispatched" <?php echo $order['status'] == 'Dispatched' ? 'selected' : ''; ?>>Dispatched</option>
                                            <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        </select>
                                        <button type="submit" name="update_order_status" class="text-blue-600 hover:underline ml-2" onclick="return confirm('Are you sure you want to update the status of order #<?php echo $order['id']; ?>?');">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
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