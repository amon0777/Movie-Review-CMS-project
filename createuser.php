<?php
include 'db_connection.php';

// Check if the "Add User" form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_user"])) {
    $new_username = $_POST['new_username'] ?? '';
    $new_email = $_POST['new_email'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    
    // Hash the password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Perform validation here (e.g., check for empty fields)

    // Insert new user into the database
    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $new_username, $new_email, $hashed_password);
    $stmt->execute();

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
	<title>Add a New User</title>
	<link rel="stylesheet" type="text/css" href="main.css">
</head>
<body>
	<div class="newuser"><h2>Add New User</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <label for="new_username">Username:</label>
    <input type ="text" id="new_username" name="new_username" required><br>
    <label for="new_email">Email:</label>
    <input type="email" id="new_email" name="new_email" required><br>
    <label for="new_password">Password:</label>
    <input type="password" id="new_password" name="new_password" required><br>
    <button type="submit" name="add_user">Add User</button>
</form>
</div>
<script type="text/javascript" src="script.js"></script>
</body>
</html>