<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $json = file_get_contents('php://input');
  $data = json_decode($json, true);

  $province_code = $data["province_code"];

  if (!isset($province_code)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing unit_code"]);
  }

  fetchData($province_code);
} else {
  http_response_code(405);
  echo json_encode(["status" => "error", "message" => "Method not allowed"]);
}

function fetchData($province_code)
{
  $url = "https://www.cskh.evnspc.vn/LienHe/getDienLucList";

  $postData = [
    "pMA_DVICTREN" => $province_code,
  ];

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $response = curl_exec($ch);

  if (curl_errno($ch)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => curl_error($ch)], JSON_UNESCAPED_UNICODE);
    return;
  }

  $data = [];

  preg_match_all('/<option value="(.*?)">(.*?)<\/option>/', $response, $matches, PREG_SET_ORDER);

  foreach ($matches as $match) {
    $data[] = [
      'region_key' => $match[1],
      'region_name' => html_entity_decode($match[2])
    ];
  }

  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

  curl_close($ch);
}