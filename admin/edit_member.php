<?php
$data_file = '../data/org_members.json';
$upload_dir = '../uploads/orgchart/';

if (!file_exists($data_file)) {
  echo "<script>alert('No data file found.'); window.history.back();</script>";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $edit_name = trim($_POST['edit_name'] ?? '');
  $new_name = trim($_POST['new_name'] ?? '');
  $new_position = trim($_POST['new_position'] ?? '');
  $new_photo = $_FILES['new_photo'] ?? null;

  if (empty($edit_name)) {
    echo "<script>alert('Please select a member to edit.'); window.history.back();</script>";
    exit;
  }

  // Load existing members
  $members = json_decode(file_get_contents($data_file), true);
  $updated = false;

  foreach ($members as &$member) {
    if (strcasecmp($member['name'], $edit_name) === 0) {
      // Update name if provided
      if (!empty($new_name)) {
        $member['name'] = $new_name;
      }

      // Update position if provided
      if (!empty($new_position)) {
        $member['position'] = $new_position;
      }

      // Handle photo upload
      if (!empty($new_photo['name'])) {
        $photo_name = basename($new_photo['name']);
        $target = $upload_dir . $photo_name;

        // Move uploaded file
        if (move_uploaded_file($new_photo['tmp_name'], $target)) {
          // Delete old photo if exists
          if (!empty($member['photo']) && file_exists($upload_dir . $member['photo'])) {
            unlink($upload_dir . $member['photo']);
          }
          $member['photo'] = $photo_name;
        } else {
          echo "<script>alert('Failed to upload new photo.'); window.history.back();</script>";
          exit;
        }
      }

      $updated = true;
      break;
    }
  }

  if ($updated) {
    file_put_contents($data_file, json_encode($members, JSON_PRETTY_PRINT));
    echo "<script>alert('Member updated successfully!'); window.history.back();</script>";
  } else {
    echo "<script>alert('Member not found.'); window.history.back();</script>";
  }
}
?>
