<?php
require '../config.php';
$data = ["apis" => [], "parse_url" => ""];
$res = $conn->query("SELECT * FROM video_apis ORDER BY id DESC");
while ($row = $res->fetch_assoc()) {
  $data['apis'][] = ["id" => $row['id'], "name" => $row['name'], "api_url" => $row['api_url']];
}
$parse = $conn->query("SELECT value FROM config WHERE key_name='player_parse_url'");
if ($row = $parse->fetch_assoc()) $data['parse_url'] = $row['value'];
echo json_encode($data);
?>
