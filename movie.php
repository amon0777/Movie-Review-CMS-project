<?php
include 'db_connection.php';
session_start();
$is_logged_in = isset($_SESSION["loggedin"]);
// Check if the form was submitted and process the unhide action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'unhide' && isset($_POST['comment_id'])) {
    // Check if the user is an admin (you may need to adjust this logic based on your authentication system)
    $is_admin = isset($_SESSION["role"]) && $_SESSION["role"] === "admin";
    
    if ($is_admin) {
        // Get the comment ID from the form submission
        $comment_id = $_POST['comment_id'];
        
        // Perform the unhide action (update the database)
        $sql = "UPDATE comments SET is_hidden = 0 WHERE comment_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $comment_id);
        
        if ($stmt->execute()) {
            // Unhide successful
            // You can optionally display a success message here
        } else {
            // Unhide failed
            // You can optionally display an error message here
        }
        
        // Close the prepared statement
        $stmt->close();
    }
}
// Check if the user is an admin
$is_admin = isset($_SESSION["role"]) && $_SESSION["role"] === "admin";
// Check if the form was submitted and process the action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['comment_id'])) {
    // Check if the user is an admin (you may need to adjust this logic based on your authentication system)
    $is_admin = isset($_SESSION["role"]) && $_SESSION["role"] === "admin";
    
    // Get the action from the form submission
    $action = $_POST['action'];
    
    if ($is_admin) {
        // Get the comment ID from the form submission
        $comment_id = $_POST['comment_id'];
        
        // Perform the corresponding action (update the database)
        if ($action === 'unhide') {
            $sql = "UPDATE comments SET is_hidden = 0 WHERE comment_id = ?";
        } elseif ($action === 'hide') {
            $sql = "UPDATE comments SET is_hidden = 1 WHERE comment_id = ?";
        } elseif ($action === 'delete') {
            $sql = "DELETE FROM comments WHERE comment_id = ?";
        }
        
        // Prepare and execute the SQL statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $comment_id);
        
        if ($stmt->execute()) {
            // Action successful
            // You can optionally display a success message here
        } else {
            // Action failed
            // You can optionally display an error message here
        }
        
        // Close the prepared statement
        $stmt->close();
    }
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
// Initialize variables to hold comment and name
$comment_text = "";
$commenter_name = "";
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the comment from the form
    $comment_text = isset($_POST['comment']) ? $_POST['comment'] : "";
        $commenter_name = isset($_POST['name']) ? $_POST['name'] : "";




    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        // If user is not logged in, check CAPTCHA
        if (isset($_POST['captcha']) && $_POST['captcha'] == $_SESSION['captcha']) {
            // CAPTCHA verification passed, proceed to insert comment
            $commenter_name = isset($_POST['name']) ? $_POST['name'] : "";

            // Insert comment with commenter name into comments table
            $stmt_insert_comment = $conn->prepare("INSERT INTO comments (commenter_name, commenter_text, movie_id) VALUES (?, ?, ?)");
            $stmt_insert_comment->bind_param("ssi", $commenter_name, $comment_text, $movie_id);

            // Execute the prepared statement
            $stmt_insert_comment->execute();

            // Check if the comment was inserted successfully
            if ($stmt_insert_comment->affected_rows > 0) {
                echo "Comment added successfully!";
            } else {
                echo "Error adding comment: " . $conn->error;
            }
        } else {
            // CAPTCHA verification failed, display error message
            echo "CAPTCHA verification failed! Please try again.";
        }
    } else {
        // If user is logged in, insert comment with user ID into comments table
        $user_id = $_SESSION['user_id'];

        // Insert comment with user ID into comments table
        $stmt_insert_comment = $conn->prepare("INSERT INTO comments (user_id, commenter_text, movie_id) VALUES (?, ?, ?)");
        $stmt_insert_comment->bind_param("isi", $user_id, $comment_text, $movie_id);

        // Execute the prepared statement
        $stmt_insert_comment->execute();

        // Check if the comment was inserted successfully
        if ($stmt_insert_comment->affected_rows > 0) {
            echo "Comment added successfully!";
        } else {
            echo "Error adding comment: " . $conn->error;
        }
    }
}

// Fetch comments associated with the movie from the database
$stmt_comments = $conn->prepare("SELECT comments.*, users.username FROM comments LEFT JOIN users ON comments.user_id = users.user_id WHERE movie_id = ? ORDER BY comment_id DESC");
$stmt_comments->bind_param("i", $movie_id);
$stmt_comments->execute();
$result_comments = $stmt_comments->get_result();
$stmt_comments->close();



    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update'])) {
        // Get other form data
        $title = $_POST['title'];
        $director = $_POST['director'];
        $genre = $_POST['genre'];
        $release_year = $_POST['release_year'];
        $movie_id = $_POST['movie_id'];

        echo "Received movie data: ";
        echo "Title: " . $title . "<br>";
        echo "Director: " . $director . "<br>";
        echo "Genre: " . $genre . "<br>";
        echo "Release Year: " . $release_year . "<br>";
        echo "Movie ID: " . $movie_id . "<br>";

        // Check if an image was uploaded
        if (isset($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
            echo "Image file uploaded.<br>";
            // Rest of your file upload code here
        } else {
            echo "No image file uploaded.<br>";
        }
    }
}// 

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

<?php if (isset($movie)): ?>
    <div class="moviecontent">
        <h2><?php echo $movie['title']; ?></h2>
        <p>Director: <?php echo $movie['director']; ?></p>
        <p>Genre: <?php echo $movie['genre']; ?></p>
        <p>Release Year: <?php echo $movie['release_year']; ?></p>
     <?php 
$sql = "SELECT image FROM movie_data WHERE movie_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$res = $stmt->get_result();

while ($images = $res->fetch_assoc()) {
?>
   <img src="Uploads/resized_<?php echo $movie['image']; ?>" alt="Resized Image"><br>
<?php 
}
$stmt->close();
?>
<?php endif; ?>

 <div class="formdisplay">
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $movie_id; ?>" method="post">
        <?php if (!$is_logged_in) : ?>
            <!-- Show name input if not logged in -->
            <label for="name">Name:</label>
            <input type="text" name="name" value="<?php echo isset($commenter_name) ? htmlspecialchars($commenter_name) : ''; ?>" ><br>
        <?php endif; ?>
        <!-- Show comment textarea -->
        <label for="comment">Comment:</label>
          <textarea name="comment" required><?php echo isset($comment_text) ? htmlspecialchars($comment_text) : ''; ?></textarea><br>
        <!-- Omit CAPTCHA if user is logged in -->
        <?php if (!$is_logged_in) : ?>
            <label for="captcha">CAPTCHA:</label>
            <img src="generate_captcha.php" alt="CAPTCHA Image"><br>
            <input type="text" name="captcha" required><br>
        <?php endif; ?>
        <input type="submit" value="Submit">
    </form>
</div>
<div class="comments">
  <!-- Hidden section for the popup -->
<div id="hiddenCommentsPopup" class="popup">
        <div class="comments">
       <?php
// Check if the user is an admin
if ($is_admin) {
    // Fetch and display hidden comments
    $stmt_hidden_comments = $conn->prepare("SELECT comments.*, users.username FROM comments LEFT JOIN users ON comments.user_id = users.user_id WHERE movie_id = ? AND is_hidden = 1 ORDER BY comment_id DESC");
    $stmt_hidden_comments->bind_param("i", $movie_id);
    $stmt_hidden_comments->execute();
    $result_hidden_comments = $stmt_hidden_comments->get_result();

    // Check if there are any hidden comments
    if ($result_hidden_comments->num_rows > 0 && $is_admin) {
        // Display the heading for hidden comments
        echo "<h2>Hidden Comments</h2>";

        // Loop through each hidden comment and display it along with the username and unhide option
        while ($hidden_comment = $result_hidden_comments->fetch_assoc()) {
            echo "<div class='comment'>";
            echo "<div class='comment-info'>";
            echo "<span class='commenter-name'>" . $hidden_comment['username'] . "</span>";
            echo "</div>";
            echo "<div class='comment-text'>{$hidden_comment['commenter_text']}</div>";
            // Create a form for each hidden comment with an Unhide button
            echo "<form action='movie.php?id=$movie_id' method='post'>";
            echo "<input type='hidden' name='action' value='unhide'>";
            echo "<input type='hidden' name='comment_id' value='{$hidden_comment['comment_id']}'>";
            echo "<input type='submit' value='Unhide'>";
            echo "</form>";
            echo "</div>";
        }
    }
}
?>

        </div>
    </div>
</div>

<!-- Button to open the popup -->
<?php if ($is_admin) : ?>
    <button onclick="showHiddenComments()">View Hidden Comments</button>
<?php endif; ?>


    <?php
  // Fetch comments associated with the movie from the database
$stmt_comments = $conn->prepare("SELECT comments.*, users.username, comments.is_hidden FROM comments LEFT JOIN users ON comments.user_id = users.user_id WHERE movie_id = ? AND is_hidden = 0 ORDER BY comment_id DESC");
$stmt_comments->bind_param("i", $movie_id);
$stmt_comments->execute();
$result_comments = $stmt_comments->get_result();
$stmt_comments->close();

// Check if there are any comments
if ($result_comments->num_rows > 0) {
    // Loop through each comment and display it along with the username
    while ($comment = $result_comments->fetch_assoc()) {
        // Check if the comment is empty or deleted
        if (!empty($comment['commenter_text'])) {
            echo "<div class='comment'>";
            echo "<div class='comment-info'>";
            
            // Determine the commenter name
            $commenter_name = isset($comment['username']) ? $comment['username'] : $comment['commenter_name'];
            echo "<span class='commenter-name'>" . $commenter_name . "</span>";
    
    // Display moderation options for admin users
    if ($is_admin) {
         echo "<span class='moderation-options'>";
    echo "<form action='movie.php?id=$movie_id' method='post'>";
    echo "<input type='hidden' name='action' value='delete'>";
    echo "<input type='hidden' name='comment_id' value='{$comment['comment_id']}'>";
    echo "<button type='submit'>Delete</button>";
    echo "</form>";
        if (!$comment['is_hidden']) {
            echo "<form action='{$_SERVER["PHP_SELF"]}?id=$movie_id' method='post'>";
        echo "<input type='hidden' name='action' value='hide'>";
        echo "<input type='hidden' name='comment_id' value='{$comment['comment_id']}'>";
        echo "<button type='submit'>Hide</button>";
        echo "</form>";
        } 
        // You can add more moderation options such as disemvoweling
        echo "</span>";
    }

    echo "</div>";
    echo "<div class='comment-text'>{$comment['commenter_text']}</div>";
    echo "</div>";
}
}

}
    ?>
</div>


</body>
</html>

<?php
$conn->close();
?>
