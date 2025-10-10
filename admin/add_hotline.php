<?php
$hotlines_file = '../data/hotlines.json';
$upload_dir = '../uploads/hotlines/';

if (!file_exists($upload_dir)) {
  mkdir($upload_dir, 0777, true);
}

$name = trim($_POST['name'] ?? '');
$number = trim($_POST['number'] ?? '');
$logo_name = '';

if (!empty($_FILES['logo']['name'])) {
  $logo_name = time() . '_' . basename($_FILES['logo']['name']);
  $target = $upload_dir . $logo_name;
  move_uploaded_file($_FILES['logo']['tmp_name'], $target);
}

if ($name && $number) {
  $hotlines = file_exists($hotlines_file) ? json_decode(file_get_contents($hotlines_file), true) : [];
  $hotlines[] = ['name' => $name, 'number' => $number, 'logo' => $logo_name];
  file_put_contents($hotlines_file, json_encode($hotlines, JSON_PRETTY_PRINT));
  echo "<script>alert('Emergency contact added successfully!'); window.location.href=document.referrer;</script>";
} else {
  echo "<script>alert('Please fill out all fields.'); window.location.href=document.referrer;</script>";
}
?>
