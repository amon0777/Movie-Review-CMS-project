<?php

include 'db_connection.php';


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
$sql = "SELECT user_id, username, email FROM users";
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
    $update_password = $_POST['update_password'] ?? '';

    // Perform validation here (e.g., check for empty fields)

    // Check if the password field is not empty and hash the password
    if (!empty($update_password)) {
        $hashed_password = password_hash($update_password, PASSWORD_DEFAULT);

        // Update user's email and password in the database
        $sql = "UPDATE users SET email = ?, password = ? WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $update_email, $hashed_password, $update_username);
    } else {
        // Update user's email without changing the password
        $sql = "UPDATE users SET email = ? WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $update_email, $update_username);
    }

    // Execute the SQL statement
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
include 'db_connection.php';



$is_logged_in = isset($_SESSION["loggedin"]);

// Check if the user is an admin
$is_admin = isset($_SESSION["role"]) && $_SESSION["role"] === "admin";
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
    <!-- Navigation links -->
</div>
<div class="admindashboardcontainer">
<h2>Welcome to the Admin Dashboard, <?php echo htmlspecialchars($_SESSION["username"]); ?></h2>
<div class="usertable">
<h3>User Management</h3>
<table>
    <thead>
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td>
                           <form action="updateuser.php" method="get" style="display: inline;">
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
        <button type="submit" name="edit_user">Edit</button>
    </form>

                        <!-- Delete User Form -->
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display: inline;">
                            <input type="hidden" name="delete_username" value="<?php echo htmlspecialchars($user['username']); ?>">
                            <button type="submit" name="delete_user">Delete</button>
                        </form>
                    </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
 <button onclick="window.location.href = 'createuser.php'">Add a new User</button>
</div>
<div class="Movietable">
    <h2>Manage Movies</h2>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Director</th>
                <th>Year</th>
                <th>Genre</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Query to fetch movie data
            $sqlMovies = "SELECT * FROM movie_data";
            $resultMovies = $conn->query($sqlMovies);

            // Check if movie data exists and loop through the result set
            if ($resultMovies && $resultMovies->num_rows > 0) {
                while ($row = $resultMovies->fetch_assoc()) {
                    $movie_id = $row['movie_id']; // Corrected variable assignment
                    $title = $row['title'];
                    $director = $row['director'];
                    $year = $row['release_year'];
                    $genre = $row['genre'];

                    echo "<tr>";
                    // Make movie title clickable and link to movie.php with movie_id parameter
                    echo "<td><a href='movie.php?id=$movie_id'>$title</a></td>"; // Updated link
                    echo "<td>$director</td>";
                    echo "<td>$year</td>";
                    echo "<td>$genre</td>";
                    // Link the edit button to edit.php with movie_id parameter
                    echo "<td><a href='edit.php?id=$movie_id'>Edit</a></td>"; // Updated link
                    echo "<td><form method='post'><input type='hidden' name='movie_id' value='$movie_id'><button type='submit' name='delete_movie'>Delete</button></form></td>";

                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No movies found.</td></tr>";
            }
            // Handle movie deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_movie"])) {
    $delete_movie_id = $_POST['movie_id'] ?? '';

    // Perform validation here (e.g., check if $delete_movie_id is not empty)

    // Delete related records from the comments table
    $sqlDeleteComments = "DELETE FROM comments WHERE movie_id = ?";
    $stmt = $conn->prepare($sqlDeleteComments);
    $stmt->bind_param("i", $delete_movie_id);
    $stmt->execute();

    // Delete movie from the database
    $sqlDelete = "DELETE FROM movie_data WHERE movie_id = ?";
    $stmt = $conn->prepare($sqlDelete);
    $stmt->bind_param("i", $delete_movie_id);
    $stmt->execute();

    // Check if any rows were affected
    if ($stmt->affected_rows > 0) {
        echo "Movie deleted successfully.";
    } else {
        echo "Failed to delete movie.";
    }
    echo "<script>window.location.reload();</script>";
}
                
            
            ?>
        </tbody>
    </table>
</div>

<div class="createbutton">
<?php if ($is_logged_in || $is_admin): ?>
    <button onclick="window.location.href = 'create.php'">Create a New Page</button>
<?php endif; ?>
</div>

<li>
    <form action="" method="post">
        <button type="submit" name="logout">Logout</button>
    </form>
</li>

<!-- Forms for adding, updating, and deleting users -->
<script type="text/javascript" src="script.js"></script>
</body>
</html>

