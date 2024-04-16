<?php
include 'db_connection.php';

session_start();

// Check if user is logged in or is an admin
$is_logged_in = isset($_SESSION["loggedin"]);
$is_admin = isset($_SESSION["role"]) && $_SESSION["role"] === "admin";

// Redirect users who are not logged in or are not admins
if (!$is_logged_in && !$is_admin) {
    header("location: login.php");
    exit;
}

function file_is_an_image($temporary_path, $file_name) {
    $allowed_mime_types = ['image/gif', 'image/jpeg', 'image/png'];
    $allowed_file_extensions = ['gif', 'jpg', 'jpeg', 'png'];

    if (empty($temporary_path)) {
    header("Location: home.php"); // Redirect to the home page
    exit; // Ensure that code execution stops after the redirect
}

    // Check if the file is an image
    $image_info = getimagesize($temporary_path);
    if ($image_info === false) {
        return "Error: The File is not a Image Update Failed";
    }

    $actual_file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $actual_mime_type = $image_info['mime'];

    $file_extension_is_valid = in_array($actual_file_extension, $allowed_file_extensions);
    $mime_type_is_valid = in_array($actual_mime_type, $allowed_mime_types);

    if (!$file_extension_is_valid || !$mime_type_is_valid) {
        return "Error: Uploaded file is not a valid image.";
    }

    return true;
}

 $delete_image = isset($_POST['delete_image']) && $_POST['delete_image'] == 1;
if ($delete_image) {
    $movie_id = $_POST['movie_id'];

    // Get the image filename associated with the movie
    $sql_select_image = "SELECT image FROM movie_data WHERE movie_id=?";
    $stmt_select_image = $conn->prepare($sql_select_image);
    $stmt_select_image->bind_param("i", $movie_id);
    $stmt_select_image->execute();
    $stmt_select_image->store_result();

    if ($stmt_select_image->num_rows == 1) {
        $stmt_select_image->bind_result($image_filename);
        $stmt_select_image->fetch();

        // Check if the image filename is not empty
        if (!empty($image_filename)) {
            // Delete the image file from the file system
            if (file_exists('Uploads/' . $image_filename)) {
                unlink('Uploads/' . $image_filename);
            }

            // Update the database record to remove the association with the image
            $sql_remove_image = "UPDATE movie_data SET image=NULL WHERE movie_id=?";
            $stmt_remove_image = $conn->prepare($sql_remove_image);
            $stmt_remove_image->bind_param("i", $movie_id);
            $stmt_remove_image->execute();
        } else {
            echo "Error: Image filename is empty.";
        }
    }
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update']) && isset($_FILES['image'])) {
        // Get other form data
        $title = $_POST['title'];
        $director = $_POST['director'];
        $genre = $_POST['genre'];
        $release_year = $_POST['release_year'];
        $movie_id = $_POST['movie_id'];
        $image_name = $_FILES['image']['name'];
        $image_size = $_FILES['image']['size'];
        $temp_name = $_FILES['image']['tmp_name'];
        $error = $_FILES['image']['error'];

        if (file_is_an_image($temp_name, $image_name) !== true) {
    echo file_is_an_image($temp_name, $image_name);
    exit; // Stop further processing if the file is not an image
}

        if (file_is_an_image($temp_name, $image_name)) {
            if ($error === 0) {
                // Check if image file size is less than 5MB
                if ($image_size < 5000000) {
                    // Move the uploaded image to the uploads folder
                    $upload_dir = 'Uploads/';
                    $upload_path = $upload_dir . $image_name;
        list($width, $height, $type) = getimagesize($temp_name);
$new_width = 250; // Define the new width
$new_height = ($new_width / $width) * $height; // Calculate the proportional height

// Create a new image resource based on the image type
switch ($type) {
    case IMAGETYPE_JPEG:
        $source_image = imagecreatefromjpeg($temp_name);
        break;
    case IMAGETYPE_PNG:
        $source_image = imagecreatefrompng($temp_name);
        break;
    case IMAGETYPE_GIF:
        $source_image = imagecreatefromgif($temp_name);
        break;
    default:
        echo "Unsupported image type!";
        exit;
}

// Create a new true color image for the resized image
$resized_image = imagecreatetruecolor($new_width, $new_height);

// Resize the image
if (!imagecopyresampled($resized_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height)) {
    echo "Error: Failed to resize image.";
    exit;
}

// Define the path for the resized image
$upload_path_resized = $upload_dir . 'resized_' . $image_name;

// Save the resized image based on the image type
switch ($type) {
    case IMAGETYPE_JPEG:
        if (!imagejpeg($resized_image, $upload_path_resized, 100)) {
            echo "Error: Failed to save resized image.";
            exit;
        }
        break;
    case IMAGETYPE_PNG:
        if (!imagepng($resized_image, $upload_path_resized, 0)) {
            echo "Error: Failed to save resized image.";
            exit;
        }
        break;
    case IMAGETYPE_GIF:
        if (!imagegif($resized_image, $upload_path_resized)) {
            echo "Error: Failed to save resized image.";
            exit;
        }
        break;
    default:
        echo "Unsupported image type!";
        exit;
}

// Free up memory
imagedestroy($resized_image);
imagedestroy($source_image);


                    if (move_uploaded_file($temp_name, $upload_path)) {
                        // Update the movie data in the database with the image filename
                        $sql = "UPDATE movie_data SET title=?, director=?, genre=?, release_year=?, image=? WHERE movie_id=?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("sssssi", $title, $director, $genre, $release_year, $image_name, $movie_id);
                        // Execute the query
                        $stmt->execute();
                        echo "Movie data updated successfully!";
                    } else {
                        echo "Error: Failed to move uploaded file.";
                    }
                } else {
                    echo "Error: Uploaded file is too large. Please upload a file less than 5MB.";
                }
            } else {
                echo "Error uploading file: ";
                switch ($error) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        echo "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        echo "The uploaded file was only partially uploaded.";
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        echo "No file was uploaded.";
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        echo "Missing a temporary folder.";
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        echo "Failed to write file to disk.";
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        echo "A PHP extension stopped the file upload.";
                        break;
                    default:
                        echo "Unknown error occurred.";
                        break;
                }
            }
        } else {
            echo "Error: Uploaded file is not a valid image.";
        }
    } else {
        echo "Image file not provided.";
    }
} else {
    // Update the movie data without an image
    $sql = "UPDATE movie_data SET title=?, director=?, genre=?, release_year=? WHERE movie_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $title, $director, $genre, $release_year, $movie_id);
    // Execute the query
    $stmt->execute();
    
}

// Handle form submission for deleting movie data
if (isset($_POST['delete'])) {
    $movie_id = $_POST['movie_id'];

    $sql = "DELETE FROM movie_data WHERE movie_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Movie deleted successfully!";
        // Redirect to a page after successful deletion
        header("location: movie.php");
        exit;
    } else {
        echo "Error deleting movie: " . $conn->error;
    }
}

// Fetch movie details based on movie_id
if (isset($_GET['id'])) {
    $movie_id = $_GET['id'];
    $sql = "SELECT * FROM movie_data WHERE movie_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $movie = $result->fetch_assoc();
        // Fetch image data
        $image_data = $movie['image'];
    } else {
        echo "Movie not found!";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Movie</title>
    <link rel="stylesheet" type="text/css" href="main.css">
     <script src="tinymce/js/tinymce/tinymce.min.js"></script>
    <link rel="stylesheet" href="tinymce/js/tinymce/skins/ui/oxide/content.min.css">
    <script>
    tinymce.init({
        selector: 'textarea#content',  // Use 'textarea' selector to target all textarea elements
        plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak',
        toolbar_mode: 'floating',
        height: 300
    });
</script>
</head>
<body>

<?php if (isset($movie)): ?>
    <h2>Edit Movie: <?php echo $movie['title']; ?></h2>
    <form action="edit.php?id=<?php echo $movie['movie_id']; ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="movie_id" value="<?php echo $movie['movie_id']; ?>">
        <label for="title">Title:</label>
        <input type="text" name="title" value="<?php echo $movie['title']; ?>"><br>
        <label for="director">Director:</label>
        <input type="text" name="director" value="<?php echo $movie['director']; ?>"><br>
        Genre: <?php
     $sqlGenres = "SELECT genre_name FROM genres";
$resultGenres = $conn->query($sqlGenres);

// Check if genres were fetched successfully
if ($resultGenres->num_rows > 0) {
    // Start select element
    echo "<select id='genre' name='genre' required>";
    echo "<option value=''>Select Genre</option>"; // Default option

    // Loop through genres and create an option for each
    while ($row = $resultGenres->fetch_assoc()) {
        $genre = $row['genre_name'];
        echo "<option value='$genre'>$genre</option>";
    }

    // End select element
    echo "</select>";
} else {
    echo "No genres found.";
}
     ?><br>
        <label for="release_year">Release Year:</label>
        <input type="text" name="release_year" value="<?php echo $movie['release_year']; ?>"><br>
          <?php if (!empty($movie['image'])): ?>
            <label for="delete_image">Delete Image:</label>
            <input type="checkbox" name="delete_image" value="1"><br>
        <?php endif; ?>
        <label for="image">Upload Image:</label>
        <input type="file" name="image"><br>
         <label for="content">Content:</label><br>
    <textarea id="content" name="content" rows="10" cols="50"></textarea><br>
          <button type="submit" name="update">Update</button>
        <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this movie?')">Delete</button>
    </form>

<?php else: ?>
    <p>Movie not found!</p>
<?php endif; ?>



<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update'])) {
        // Update movie data
        // Redirect to movie.php after update
        header("Location: movie.php?id=" . $movie_id);
        exit;
    }

    if (isset($_POST['delete'])) {
        // Delete movie data
        // Redirect to movie.php after delete
        header("Location: movie.php");
        exit;
    }
}
?>

<script type="text/javascript" src="script.js"></script>
</body>
</html>

