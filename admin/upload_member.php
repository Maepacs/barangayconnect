<?php
$data_file = '../data/org_members.json';
$upload_dir = '../uploads/orgchart/';

if (!is_dir($upload_dir)) {
  mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
  $name = trim($_POST['name'] ?? '');
  $position = trim($_POST['position'] ?? '');
  $file_name = basename($_FILES['photo']['name']);

  if (empty($name) || empty($position) || empty($file_name)) {
    echo "<script>alert('Please complete all fields.'); window.history.back();</script>";
    exit;
  }

  $ext = pathinfo($file_name, PATHINFO_EXTENSION);
  $safe_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file_name, PATHINFO_FILENAME));
  $new_file_name = $safe_name . '_' . time() . '.' . $ext;
  $target = $upload_dir . $new_file_name;

  if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
    $members = file_exists($data_file) ? json_decode(file_get_contents($data_file), true) : [];

    $members[] = [
      'name' => $name,
      'position' => $position,
      'photo' => $new_file_name
    ];

    file_put_contents($data_file, json_encode($members, JSON_PRETTY_PRINT));
    echo "<script>alert('Barangay official added successfully!'); window.history.back();</script>";
  } else {
    echo "<script>alert('Failed to upload photo. Please try again.'); window.history.back();</script>";
  }
}
?>
