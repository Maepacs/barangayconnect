<?php
$announcement_dir = '../uploads/announcements/';

if (is_dir($announcement_dir)) {
    $announcements = glob($announcement_dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    if (!empty($announcements)) {
        foreach ($announcements as $img) {
            $img_url = htmlspecialchars($img, ENT_QUOTES, 'UTF-8');
            echo '<div class="carousel-slide"><img src="'. $img_url .'" alt="Announcement"></div>';
        }
    } else {
        echo '<div class="carousel-slide"><p>No announcements uploaded yet.</p></div>';
    }
} else {
    echo '<div class="carousel-slide"><p>Announcement folder not found.</p></div>';
}
?>
