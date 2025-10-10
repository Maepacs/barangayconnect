<?php
if(isset($_POST['delete_img'])){
    $file = "../uploads/announcements/" . $_POST['delete_img'];
    if(file_exists($file)){
        unlink($file);
        header("Location: landing_page.php");
    } else {
        echo "File does not exist.";
    }
}
?>