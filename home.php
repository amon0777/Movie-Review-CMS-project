
<?php
require('connect.php');

// Connect to the database and fetch movie data
$servername = "localhost";
$username = "serveruser";
$password = "gorgonzola7!";
$dbname = "serverside";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM movie_data";
$result = $conn->query($sql);


?>


<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="main.css">
    <title>The Movie Realm</title>
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
    </div>
    
<div class="allmoviescontent">
    <ul>
    <?php
    // Display links to all available movies
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
             echo "<div class='movie'>";
            echo "<li><a href='movie.php?id=" . $row['movie_id'] . "'>" . $row['title'] . "</a></li>";
             echo "</div>";
        }
    } else {
        echo "0 results";
    }
    ?>

    </ul>
</div>
</body>
</html>

<?php
$conn->close();
?>
