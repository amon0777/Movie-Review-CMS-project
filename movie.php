<?php
require('connect.php');

$servername = "localhost";
$username = "serveruser";
$password = "gorgonzola7!";
$dbname = "serverside";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the movie_id parameter is provided in the URL
if (isset($_GET['id'])) {
    // Retrieve the movie_id from the URL
    $movie_id = $_GET['id'];

    // Prepare a SQL statement to retrieve information about the specific movie
    $sql = "SELECT * FROM movie_data WHERE movie_id = ?";

    // Prepare and bind parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $movie_id);

    // Execute the query
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Check if the query was successful and if the movie exists
    if ($result->num_rows > 0) {
        // Fetch the movie details
        $movie = $result->fetch_assoc();
    } else {
        echo "Movie not found";
    }

    // Close the prepared statement
    $stmt->close();
} else {
    echo "Movie ID not provided";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CAPTCHA
    session_start();
    if ($_POST['captcha'] != $_SESSION['captcha']) {
        echo "CAPTCHA verification failed!";
        exit;
    }

    // Get the name and comment from the form
    $name = $_POST['name'];
    $comment = $_POST['comment'];

    // Insert the comment into the comments table
    $sql = "INSERT INTO comments (commenter_name, commenter_text, movie_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $name, $comment, $movie_id);
    $stmt->execute();
    
    // Check if the comment was inserted successfully
    if ($stmt->affected_rows > 0) {
        echo "Comment added successfully!";
    } else {
        echo "Error adding comment: " . $conn->error;
    }



    // Close the prepared statement
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="main.css">
    <title><?php echo isset($movie['title']) ? $movie['title'] : 'Movie Not Found'; ?></title>
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
            <!-- Add links to other sections/pages here -->
        </ul>
    </nav>
    </div>

    <div class="moviecontent">
    <?php if (isset($movie)): ?>
        <h2><?php echo $movie['title']; ?></h2>
        <p>Director: <?php echo $movie['director']; ?></p>
        <p>Genre: <?php echo $movie['genre']; ?></p>
        <p>Release Year: <?php echo $movie['release_year']; ?></p>
        <!-- Add more details as needed -->
    <?php endif; ?>
</div>

 <div class="formdisplay">
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $movie_id; ?>" method="post">
        <label for="name">Name:</label>
        <input type="text" name="name" required><br>
        <label for="comment">Comment:</label>
        <textarea name="comment" required></textarea><br>
        <label for="captcha">CAPTCHA:</label>
        <img src="generate_captcha.php" alt="CAPTCHA Image"><br>
        <input type="text" name="captcha" required><br>
        <input type="submit" value="Submit">
    </form>
</div>
<div class="comments">
  <?php
  // Query to retrieve comments associated with the movie
$sql_comments = "SELECT * FROM comments WHERE movie_id = ?";
$stmt_comments = $conn->prepare($sql_comments);
if (!$stmt_comments) {
    die("Error preparing statement: " . $conn->error);
}
$stmt_comments->bind_param("i", $movie_id);
$stmt_comments->execute();
$result_comments = $stmt_comments->get_result();
if (!$result_comments) {
    die("Error executing query: " . $stmt_comments->error);
}

// Check if there are any comments
if ($result_comments->num_rows > 0) {
    // Loop through each comment and display it
    while($comment = $result_comments->fetch_assoc()) {
        echo "<p>{$comment['commenter_name']}: {$comment['commenter_text']}</p>";
    }
} else {
    echo "No comments yet.";
}
?>
</div>
  
</body>
</html>

<?php
$conn->close();
?>
