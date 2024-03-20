<?php
require('connect.php');
require('authenticate.php');

// Connect to the database and fetch movie data
$servername = "localhost";
$username = "serveruser";
$password = "gorgonzola7!";
$dbname = "serverside";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start(); // Start the session

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check if the user is an admin
if ($_SESSION["role"] !== "admin") {
    // Redirect to a different page or show an error message
    echo "You don't have permission to access this page.";
    exit;
}

// Logout logic
if (isset($_POST["logout"])) {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to login page
    header("location: login.php");
    exit;
}

// Fetch all users from the database
$sql = "SELECT username, email FROM users";
$result = $conn->query($sql);

// Check if users were fetched successfully
if ($result) {
    // Fetch users as associative array
    $users = $result->fetch_all(MYSQLI_ASSOC);
} else {
    // Handle the case where there are no users or an error occurred
    $users = array(); // Initialize an empty array
}

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

// Check if the "Update User" form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_user"])) {
    $update_username = $_POST['update_username'] ?? '';
    $update_email = $_POST['update_email'] ?? '';

    // Perform validation here (e.g., check for empty fields)

    // Update user's email in the database
    $sql = "UPDATE users SET email = ? WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $update_email, $update_username);
    $stmt->execute();

    // Refresh the page to display the updated user list
    header("location: admin_dashboard.php");
    exit;
}

// Check if the "Delete User" form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_user"])) {
    $delete_username = $_POST['delete_username'] ?? '';

    // Perform validation here (e.g., check for empty fields)

    // Delete user from the database
    $sql = "DELETE FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $delete_username);
    $stmt->execute();

    // Refresh the page to display the updated user list
    header("location: admin_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="main.css">
    <title>Admin Dashboard</title>
</head>
<body>
<div class="mainnav">
    <h1 class="head">The Movie Realm</h1>
     <nav>
        <ul>
            <li><a href="home.php">All Movies</a></li>
            <li>
                <label for="genre">Genre</label>
                <select id="genre" onchange="window.location.href=this.value;">
                    <option value="">Select Genre</option> <!-- Empty value for default selection -->
                    <?php
                    require('connect.php');

                    // Query to fetch distinct genres
                    $sqlGenres = "SELECT DISTINCT genre FROM movie_data";
                    $resultGenres = $conn->query($sqlGenres);

                    if ($resultGenres->num_rows > 0) {
                        while($row = $resultGenres->fetch_assoc()) {
                            $genre = $row['genre'];
                            echo "<option value='genre.php?genre=$genre'>$genre</option>";
                        }
                    } else {
                        echo "<option value=''>No genres found</option>"; 
                    }
                    ?>
                </select>
            </li>
            <li><a href="register.php"> Sign Up</a></li>
        <li><a href="login.php">Log In</a></li>

            <li><a href="dashboard.php">User Profile</a></li>
         <li><a href="admin_dashboard.php">Admin Dashboard</a></li>

            <!-- Add links to other sections/pages here -->
        </ul>
    </nav>
    <!-- Navigation links -->
</div>
<h2>Welcome to the Admin Dashboard, <?php echo htmlspecialchars($_SESSION["username"]); ?></h2>

<h3>User Management</h3>
<table>
    <thead>
        <tr>
            <th>Username</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2>Add New User</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <label for="new_username">Username:</label>
    <input type ="text" id="new_username" name="new_username" required><br>
    <label for="new_email">Email:</label>
    <input type="email" id="new_email" name="new_email" required><br>
    <label for="new_password">Password:</label>
    <input type="password" id="new_password" name="new_password" required><br>
    <button type="submit" name="add_user">Add User</button>
</form>

<h2>Update User</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <label for="update_username">Username:</label>
    <input type="text" id="update_username" name="update_username" required><br>
    <label for="update_email">New Email:</label>
    <input type="email" id="update_email" name="update_email" required><br>
    <button type="submit" name="update_user">Update Email</button>
</form>

<h2>Delete User</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <label for="delete_username">Username:</label>
    <input type="text" id="delete_username" name="delete_username" required><br>
    <button type="submit" name="delete_user">Delete User</button>
</form>

<li>
    <form action="" method="post">
        <button type="submit" name="logout">Logout</button>
    </form>
</li>

<!-- Forms for adding, updating, and deleting users -->
</body>
</html>

