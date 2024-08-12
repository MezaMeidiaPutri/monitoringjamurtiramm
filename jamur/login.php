<?php
session_start();
$error = "";

// Database credentials
$servername = "localhost";
$dbusername = "root"; // Update with your database username
$dbpassword = "meza12345"; // Update with your database password
$dbname = "jamur";

// Create connection
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputUsername = $_POST['username'];
    $inputPassword = $_POST['password'];

    // Prepare and execute SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $inputUsername, $inputPassword); // Bind parameters
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user exists
    if ($result->num_rows > 0) {
        // User is authenticated
        $_SESSION['loggedin'] = true;
        header("Location: index.php"); // Redirect to index.php
        exit(); // Make sure to exit after the redirection
    } else {
        // User not found
        $error = "Invalid username or password.";
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f4f4; }
        .login-container { max-width: 400px; width: 100%; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); background-color: #fff; }
        h2 { margin-bottom: 20px; font-size: 28px; color: #333; text-align: center; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        input { width: calc(100% - 22px); padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
        button { width: 100%; padding: 10px; background-color: #ffcc00; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; transition: background-color 0.3s; }
        button:hover { background-color: #e6b800; }
        .error { color: red; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <label>Username:</label>
            <input type="text" name="username" required>
            <label>Password:</label>
            <input type="password" name="password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
