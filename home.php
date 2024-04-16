<?php
include 'db_connection.php';

// Initialize variables to avoid undefined variable warnings
$resultMovies = null;
$resultGenres = null;

// Query to fetch all movies
$sql = "SELECT * FROM movie_data";
$result = $conn->query($sql);

// Check if the query was successful
if ($result) {
    // Fetch distinct genres
    $sqlGenres = "SELECT DISTINCT genre_name FROM genres";
    $resultGenres = $conn->query($sqlGenres);

    // Check if the genre parameter is set in the URL
    if (isset($_GET['genre'])) {
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
    } else {
        // If genre parameter is not set, initialize $resultMovies with the complete list of movies
        $resultMovies = $result;
    }
}

session_start();

$is_logged_in = isset($_SESSION["loggedin"]);
$is_admin = isset($_SESSION["role"]) && $_SESSION["role"] === "admin";
?>


<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="main.css">
    <title>The Movie Realm</title>
     <style>
        .movie {
            display: inline-block;
            margin: 10px;
            text-align: center;
        }

        .movie img {
            width: 200px; /* Set width of the image */
            height: auto; /* Maintain aspect ratio */
        }
    </style>
    <style>
@import url('https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700;900&display=swap')
</style>
</head>
<body>
           <div class="video-container">
        <video autoplay muted loop  id="video-background">
           <source src="intros/mix.mp4" type="video/mp4">    
        </video>
        <!-- Overlay with welcome message and explore button -->
        <div class="welcomeoverlay">
            <h1>Welcome to The Movie Realm</h1>
            <p>Explore our vast collection of movies</p>
            <a href="#movies" class="explore-btn" onclick="smoothScroll(event)">Explore Now</a>
        </div>
    </div>
  
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

            <!-- Add links to other sections/pages here -->
        </ul>
    </nav>
    </div>
 
     <div class="allmoviescontent" id = "movies">
        <!-- Display genres -->
        <div class="content">
            <ul>
                <?php
                // Display genres in navigation menu
                if ($resultGenres->num_rows > 0) {
                    while($row = $resultGenres->fetch_assoc()) {
                        $genre = $row['genre_name'];
                        // Instead of linking to another page, include query parameter for genre
                        echo "<li><a href='home.php?genre=$genre'>$genre</a></li>";
                    }
                }
                ?>
            </ul>
        </div>

        <!-- Display movies -->
        <?php 
        if ($resultMovies->num_rows > 0) {
            // Display each movie
            while($row = $resultMovies->fetch_assoc()) {
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
                     echo "<img src='path/to/your/placeholder/image.jpg' alt='No image available' /><p><a href='movie.php?id=$movie_id'>$title</a></p>";
                    echo "</div>";
                }
            }
        } else {
            echo "No results found";
        }
        ?>
    </div>
</div>
<script type="text/javascript" src="script.js"></script>
</body>
</html>

<?php
$conn->close();
?>