<?php
// Start session
session_start();

// Generate a random CAPTCHA value (4-digit number)
$captcha = rand(1000, 9999);

// Store the CAPTCHA value in session
$_SESSION['captcha'] = $captcha;

// Create a blank image with dimensions for the CAPTCHA text
$image = imagecreatetruecolor(100, 30);

// Set background color (white)
$bg_color = imagecolorallocate($image, 255, 255, 255);
imagefill($image, 0, 0, $bg_color);

// Set text color (black)
$text_color = imagecolorallocate($image, 0, 0, 0);

// Add the CAPTCHA text to the image
imagestring($image, 5, 10, 5, $captcha, $text_color);

// Set the content type header to display the image
header('Content-type: image/png');

// Output the image as PNG
imagepng($image);

// Free up memory
imagedestroy($image);
?>
