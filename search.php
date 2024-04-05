<?php
 include "db_connection.php";// Retrieve search query
if (isset($_GET['query'])) {
    $search_query = $_GET['query'];
} else {
    $search_query = '';
}

session_start();

$is_logged_in = isset($_SESSION["loggedin"]);
$is_admin = isset($_SESSION["role"]) && $_SESSION["role"] === "admin";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Search</title>
    <link rel="stylesheet" type="text/css" href="main.css">
</head>
<body>
    
    <div class="mainnav">
       <h1 class="head" onclick="alert('Welcome to The Movie Realm!')">The Movie Realm</h1>
        
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
        <button type="submit">Search</button>
    </form>
</li>

            <!-- Add links to other sections/pages here -->
        </ul>
    </nav>
    </div>

    <?php
// SQL query to search for movie_data where the genre or title matches the search query
$sql = "SELECT * FROM movie_data WHERE genre LIKE '%" . mysqli_real_escape_string($conn, $search_query) . "%' OR title LIKE '%" . mysqli_real_escape_string($conn, $search_query) . "%'";

// Execute query
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    echo "<h2>Search Results:</h2>";
    while ($row = mysqli_fetch_assoc($result)) {
     $movie_id = $row['movie_id'];
                $title = $row['title'];
                $image = $row['image']; // Assuming image column holds image filenames

                // Display movie image if available
                if (!empty($image)) {
                    echo "<div class='movie'>";
                    echo "<img src='Uploads/resized_$image' alt='$title' />";
                    echo "<div class='overlay'><p><a href='movie.php?id=$movie_id'>$title</a></p></div>";
                    echo "</div>";
                } else {
                    // If image is not available
                    echo "<div class='movie'>";
                    echo "<div class='overlay'><p><a href='movie.php?id=$movie_id'>$title</a></p></div>";
                    echo "</div>";
                }
            }
        } else {
            echo "No results found";
        }

mysqli_close($conn);
    ?>
<script type="text/javascript" src="script.js"></script>
</body>
</html>