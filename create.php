<?php
include 'db_connection.php';

function file_is_an_image($temporary_path, $file_name) {
    $allowed_mime_types = ['image/gif', 'image/jpeg', 'image/png'];
    $allowed_file_extensions = ['gif', 'jpg', 'jpeg', 'png'];

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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the title field is not empty
    if (!empty($_POST['title'])) {
        // Retrieve form data
        $title = $_POST['title'];
        $director = $_POST['director'];
        $genre_name = $_POST['genre'];
        $release_year = $_POST['release_year'];

        $stmt_genre =$conn->prepare("SELECT genre_id FROM genres WHERE genre_name = ?");
        $stmt_genre->bind_param("s", $genre_name);
        $stmt_genre->execute();
        $result_genre = $stmt_genre->get_result();

         if ($result_genre->num_rows > 0) {
            $row = $result_genre->fetch_assoc();
            $genre_id = $row['genre_id'];


        // Check if an image file is uploaded
if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
            // Retrieve image data
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
}

// Free up memory
imagedestroy($resized_image);
imagedestroy($source_image);



                    if (move_uploaded_file($temp_name, $upload_path)) {
                        // Insert the movie data into the database with the image filename
                        $sql = "INSERT INTO movie_data (title, director, genre, release_year, image, genre_id) VALUES (?, ?, ?, ?, ? ,?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("sssssi", $title, $director, $genre_name, $release_year, $image_name, $genre_id); // Changed "i" to "s" for string

                        // Execute the SQL statement
                        if ($stmt->execute()) {
                            // Redirect back to home.php after successful insertion
                            header("Location: home.php");
                            exit;
                        } else {
                            // Display an error message if insertion fails
                            echo "Error: " . $stmt->error;
                        }
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
            // No image uploaded, insert the movie data without an image
            $sql = "INSERT INTO movie_data (title, director, genre, release_year,genre_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $title, $director, $genre_name, $release_year,$genre_id);

            // Execute the SQL statement
            if ($stmt->execute()) {
                // Redirect back to home.php after successful insertion
                header("Location: home.php");
                exit;
            } else {
                // Display an error message if insertion fails
                echo "Error: " . $stmt->error;
            }
        }
    }

        // Close the prepared statement
        $stmt->close();
    } else {
        // Display an error message if title field is empty
        echo "Error: Title cannot be empty.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="main.css">
    <title>Create New Movie</title>
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
    <div class="container">
    <h1>Create New Movie</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        <label for="title">Title:</label><br>
        <input type="text" id="title" name="title" required><br>
        <label for="director">Director:</label><br>
        <input type="text" id="director" name="director" required><br>
     <?php
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
        <label for="release_year">Release Year:</label><br>
        <input type="number" id="release_year" name="release_year" required><br><br>
        <label for="image">Upload Image:</label>
        <input type="file" name="image"><br>
        <label for="content">Content:</label><br>
    <textarea id="content" name="content" rows="10" cols="50"></textarea><br>
        <input type="submit" name="submit" value="Submit">
    </form>
</div>
<script type="text/javascript" src="script.js"></script>
</body>
</html>
