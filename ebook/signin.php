<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "ebook_store");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
$error_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validation
    if (empty($email) || empty($password)) {
        $error_message = "Email and password are required.";
    } else {
        // Check user credentials
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header("Location: index.php");
                exit;
            } else {
                $error_message = "Invalid email or password.";
            }
        } else {
            $error_message = "Invalid email or password.";
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
    <title>Sign In - eBook Haven</title>
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
                <a href="signin.php" class="hover:underline font-medium">Sign In</a>
                <a href="signup.php" class="hover:underline font-medium">Sign Up</a>
            </div>
        </div>
    </nav>

    <!-- Sign-In Form -->
    <div class="container mx-auto py-12 px-4">
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Sign In</h2>
        <div class="max-w-lg mx-auto bg-white rounded-lg shadow-lg p-6">
            <?php if ($error_message): ?>
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                    <input type="" id="email" name="email" class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                    <input type="password" id="password" name="password" class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-3 rounded hover:bg-blue-700 transition">Sign In</button>
            </form>
            <p class="mt-4 text-center text-gray-600">Don't have an account? <a href="signup.php" class="text-blue-600 underline">Sign Up</a></p>
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