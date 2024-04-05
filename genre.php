<?php
include 'db_connection.php';


// Query to fetch distinct genres
$sqlGenres = "SELECT DISTINCT genre_name FROM genres";
$resultGenres = $conn->query($sqlGenres);

if(isset($_GET['genre'])) {
    // Retrieve the genre from the URL
    $genre = $_GET['genre'];

    // Query to fetch movies associated with the specified genre
    $sqlMovies = "SELECT md.* FROM movie_data md
                  INNER JOIN genres g ON md.genre_id = g.genre_id
                  WHERE g.genre_name = ?";
    $stmt = $conn->prepare($sqlMovies);
    $stmt->bind_param("s", $genre);
    $stmt->execute();
    $resultMovies = $stmt->get_result();

    // Close the prepared statement
    $stmt->close();
    session_start();

$is_logged_in = isset($_SESSION["loggedin"]);
$is_admin = isset($_SESSION["role"]) && $_SESSION["role"] === "admin";
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="main.css">
    <title>Genres</title>
</head>
<body>
    <div class="mainnav">
        <h1 class="head">The Movie Realm</h1>
        
       <nav>
        <ul>
            <li>
    <form action="search.php" method="GET">
        <input type="text" name="query" placeholder="Search...">
        <button type="submit">Search</button>
    </form>
</li>
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
                            echo "<option value='genre.php?genre=$genre'>$genre</option>";
                        }
                    } else {
                        echo "<option value=''>No genres found</option>"; 
                    }
                    ?>
                </select>

            </li>
                <li> <!-- Separate list item for the search form -->
                <form action="search.php" method="GET">
                    <input type="text" name="query" placeholder="Search...">
                    <button type="submit">Search</button>
                </form>
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
      <!-- Add links to other sections/pages here -->
        </ul>
    </nav>
    </div>
        
    <div class="content">
        <ul>
            <?php
            // Display genres in navigation menu
            if ($resultGenres->num_rows > 0) {
                while($row = $resultGenres->fetch_assoc()) {
                    $genre = $row['genre'];
                    echo "<li><a href='genre.php?genre=$genre'>$genre</a></li>";
                }
            }
            ?>
        </ul>

            <?php 
    // Check if there are movies
    if ($resultMovies->num_rows > 0) {
        // Display each movie
        while($row = $resultMovies->fetch_assoc()) {
            $movie_id = $row['movie_id'];
            $title = $row['title'];
            $image = $row['image']; // Assuming image column holds image filenames

            // Display movie image if available
            if (!empty($image)) {
                echo "<div class='movie'>";
                echo "<img src='Uploads/$image' alt='$title' />";
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
    ?>
    </div>
    <script type="text/javascript" src="script.js"></script>
</body>
</html>

<?php
$conn->close();
?>
