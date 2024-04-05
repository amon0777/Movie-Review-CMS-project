<?php
include "db_connection.php";
session_start();

// Check if the user is an admin
if ($_SESSION["role"] !== "admin") {
    // Redirect to a different page or show an error message
    echo "You don't have permission to access this page.";
    exit;
}

// Check if the user ID is provided in the URL
if(isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch user information from the database using the provided user ID
    $sql = "SELECT username, email FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $username = $row['username'];
        $email = $row['email'];
    } else {
        echo "User not found.";
        exit;
    }
} else {
    echo "User ID not provided.";
    exit;
}

// Check if the "Update User" form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_user"])) {
    $new_username = $_POST['new_username'] ?? '';
    $update_email = $_POST['update_email'] ?? '';
    $update_password = $_POST['update_password'] ?? '';

    // Perform validation here (e.g., check for empty fields)

    // Check if the password field is not empty and hash the password
    if (!empty($update_password)) {
        $hashed_password = password_hash($update_password, PASSWORD_DEFAULT);

        // Update user's email and password in the database
        $sql = "UPDATE users SET email = ?, password = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $update_email, $hashed_password, $user_id);
    } else {
        // Update user's email without changing the password
        $sql = "UPDATE users SET email = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $update_email, $user_id);
    }

    // Execute the SQL statement
    $stmt->execute();

    // Check if username needs to be updated
    if (!empty($new_username)) {
        // Update user's username in the database
        $sql_update_username = "UPDATE users SET username = ? WHERE user_id = ?";
        $stmt_update_username = $conn->prepare($sql_update_username);
        $stmt_update_username->bind_param("si", $new_username, $user_id);
        $stmt_update_username->execute();
    }

    // Refresh the page to display the updated user list
    header("location: admin_dashboard.php");
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Update Existing User</title>
    <link rel="stylesheet" type="text/css" href="main.css">
</head>
<body>
    <div class="updateuser">
        <h2>Update User</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?user_id=<?php echo $user_id; ?>" method="post">
            <label for="update_username">Username:</label>
            <input type="text" id="update_username" name="new_username" value="<?php echo htmlspecialchars($username); ?>" required><br>
            <label for="update_email">New Email:</label>
            <input type="email" id="update_email" name="update_email" value="<?php echo htmlspecialchars($email); ?>" required><br>
            <label for="update_password">New Password:</label>
            <input type="password" id="update_password" name="update_password"><br>
            <button type="submit" name="update_user">Update User</button>
        </form>
    </div>
    <script type="text/javascript" src="script.js"></script>
</body>
</html>
