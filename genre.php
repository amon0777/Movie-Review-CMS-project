<?php
require('connect.php');

// Connect to the database
$servername = "localhost";
$username = "serveruser";
$password = "gorgonzola7!";
$dbname = "serverside";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch distinct genres
$sqlGenres = "SELECT DISTINCT genre FROM movie_data";
$resultGenres = $conn->query($sqlGenres);

if(isset($_GET['genre'])) {
    // Retrieve the genre from the URL
    $genre = $_GET['genre'];

    // Query to fetch movies associated with the specified genre
    $sqlMovies = "SELECT * FROM movie_data WHERE genre = ?";
    $stmt = $conn->prepare($sqlMovies);
    $stmt->bind_param("s", $genre);
    $stmt->execute();
    $resultMovies = $stmt->get_result();

    // Close the prepared statement
    $stmt->close();
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
        // Display the movies associated with the genre
        if (isset($resultMovies) && $resultMovies->num_rows > 0) {
            echo "<h2>Movies in the genre: $genre</h2>";
            echo "<ul>";
            while($row = $resultMovies->fetch_assoc()) {
                echo "<li><a href='movie.php?id={$row['movie_id']}'>{$row['title']}</a></li>";
            }
            echo "</ul>";
        }
        ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
