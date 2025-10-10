<?php
// Check if a file was uploaded
if(isset($_FILES['announcement_img']) && $_FILES['announcement_img']['error'] === UPLOAD_ERR_OK){

    $targetDir = "../uploads/announcements/";

    // Make sure directory exists
    if(!is_dir($targetDir)){
        mkdir($targetDir, 0755, true); // create if not exists
    }

    $fileName = basename($_FILES["announcement_img"]["name"]);
    $targetFile = $targetDir . $fileName;

    // Optional: check file type (jpg, png, jpeg, gif)
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg','jpeg','png','gif'];

    if(!in_array($fileType, $allowedTypes)){
        die("Only JPG, JPEG, PNG, GIF files are allowed.");
    }

    // Move the uploaded file
    if(move_uploaded_file($_FILES["announcement_img"]["tmp_name"], $targetFile)){
        header("Location: landing_page.php"); // redirect back to homepage
        exit;
    } else {
        echo "Error uploading file. Check folder permissions.";
    }

} else {
    echo "No file uploaded or upload error.";
}
?>
