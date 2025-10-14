<?php
$org_members_file = '../data/org_members.json';
$org_dir = '../uploads/orgchart/';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_name'])) {
    $delete_name = trim($_POST['delete_name']);
    $status = 'success';

    if (file_exists($org_members_file)) {
        $org_members = json_decode(file_get_contents($org_members_file), true);
        $updated_members = [];

        foreach ($org_members as $member) {
            if ($member['name'] === $delete_name) {
                // 🗑️ Delete the member’s photo file
                $photo_path = $org_dir . $member['photo'];
                if (file_exists($photo_path)) {
                    unlink($photo_path);
                }
            } else {
                $updated_members[] = $member;
            }
        }

        // Save updated list
        file_put_contents($org_members_file, json_encode($updated_members, JSON_PRETTY_PRINT));

        // 🧹 If no members left, clear all uploads + JSON file
        if (empty($updated_members)) {
            $files = glob($org_dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
            foreach ($files as $file) {
                unlink($file);
            }
            unlink($org_members_file);
            $status = 'cleared';
        }
    }

    // ✅ Redirect back with success status
    header('Location: ' . $_SERVER['HTTP_REFERER'] . '?status=' . $status);
    exit;
}
?>
