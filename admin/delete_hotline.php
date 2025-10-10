<?php
$hotlines_file = '../data/hotlines.json';
$delete_name = trim($_POST['delete_name'] ?? '');

if ($delete_name && file_exists($hotlines_file)) {
  $hotlines = json_decode(file_get_contents($hotlines_file), true);
  $filtered = array_filter($hotlines, fn($h) => $h['name'] !== $delete_name);
  file_put_contents($hotlines_file, json_encode(array_values($filtered), JSON_PRETTY_PRINT));
  echo "<script>alert('Contact deleted successfully!'); window.location.href=document.referrer;</script>";
} else {
  echo "<script>alert('No contact selected.'); window.location.href=document.referrer;</script>";
}
?>
