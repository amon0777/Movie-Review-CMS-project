<?php
include 'db_connection.php';


session_start(); // Start the session


$is_logged_in = isset($_SESSION["loggedin"]);
$is_admin = isset($_SESSION["role"]) && $_SESSION["role"] === "admin";

// Logout logic
if(isset($_POST["logout"])) {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to login page
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="main.css">
    <title>Dashboard</title>
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
                    $sqlGenres = "SELECT genre_name FROM genres";
                    $resultGenres = $conn->query($sqlGenres);

                    if ($resultGenres->num_rows > 0) {
                        while($row = $resultGenres->fetch_assoc()) {
                            $genre = $row['genre_name'];
                            echo "<option value='home.php?genre=$genre'>$genre</option>";
                        }
                    } else {
                        echo "<option value=''>No genres found</option>"; 
                    }
                    ?>
                </select>
            </li>
             <?php
        // If user is not logged in, show login link
        if (!$is_logged_in) {
            echo '<li><a href="login.php">Login</a></li>';
        }

                    if ($is_logged_in) {
            // Show logout link
            echo '<li><a href="dashboard.php">User Profile</a></li>';
        }
            
            // If user is admin, show admin link
            if ($is_admin) {
                echo '<li><a href="admin_dashboard.php">Admin Panel</a></li>';
        }
        ?>
           <li>
    <form action="search.php" method="GET">
        <input type="text" id ="searchInput" name="query" placeholder="Search...">
        <select id="genre" onchange="window.location.href=this.value;">
                    <option value="">Genres</option> <!-- Empty value for default selection -->
                    <?php
                    require('connect.php');

                    // Query to fetch distinct genres
                    $sqlGenres = "SELECT genre_name FROM genres";
                    $resultGenres = $conn->query($sqlGenres);

                    if ($resultGenres->num_rows > 0) {
                        while($row = $resultGenres->fetch_assoc()) {
                            $genre = $row['genre_name'];
                            echo "<option value='home.php?genre=$genre'>$genre</option>";
                        }
                    } else {
                        echo "<option value=''>No genres found</option>"; 
                    }
                    ?>
                </select>
        <button type="submit">Search</button>
    </form>
</li>

                
        </ul>
    </nav>
</div>
<h2>Welcome to the Dashboard, <?php echo htmlspecialchars($_SESSION["username"]); ?></h2>

                <li>
                    <form action="" method="post">
                        <button type="submit" name="logout">Logout</button>
                    </form>
                </li>
                <script type="text/javascript" src="script.js"></script>
</body>
</html>
