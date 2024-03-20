<?php
require('connect.php'); // Include database connection script

$servername = "localhost";
$username = "serveruser";
$password = "gorgonzola7!";
$dbname = "serverside";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start(); // Start the session

// Check if the user is already logged in, redirect to dashboard if logged in
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: dashboard.php");
    exit;
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Database connection details
    $servername = "localhost";
    $dbname = "serverside";
    $username_db = "serveruser";
    $password_db = "gorgonzola7!";

    // Connect to the database
    $conn = new mysqli($servername, $username_db, $password_db, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }


    // Prepare and execute SQL statement to fetch user data
    $sql = "SELECT user_id, username, password, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $username, $hashed_password, $role); // Fetch the role from the database
        if ($stmt->fetch()) {
            if (password_verify($password, $hashed_password)) {
                // Password is correct, start a new session
                session_start();

                // Store data in session variables
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $id;
                $_SESSION["username"] = $username;
                $_SESSION["role"] = $role; // Set the user's role in the session

                // Redirect user to home page
                header("location: home.php");
                exit; // Ensure that no further code execution occurs after redirection
            } else {
                // Display an error message if password is not valid
                $login_err = "Invalid username or password.";
            }
        }
    } else {
        // Display an error message if username doesn't exist
        $login_err = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="main.css">
    <title>User Login</title>
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
                        while ($row = $resultGenres->fetch_assoc()) {
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
            <!-- Add links to other sections/pages here -->
        </ul>
    </nav>
</div>
<div class="loginbox">
    <h2>User Login</h2>
    <form action="login.php" method="post">
        <div class="username">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="password">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="submit">
            <button type="submit">Login</button>
        </div>
    </form>
    <?php if (isset($login_err) && !empty($login_err)) { ?>
        <div style="color: red;"><?php echo $login_err; ?></div>
    <?php } ?>
</div>

</body>
</html>
