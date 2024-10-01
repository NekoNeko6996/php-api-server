<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $json = file_get_contents('php://input');
  $data = json_decode($json, true);

  $unitCode = $data["unit_code"];

  if (!isset($unitCode)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing unit_code"]);
  }

  fetchData($unitCode);
} else {
  http_response_code(405);
  echo json_encode(["status" => "error", "message" => "Method not allowed"]);
}

function fetchData($unitCode)
{
  $url = "https://www.cskh.evnspc.vn/TraCuu/GetThongTinLichNgungGiamMaKhachHang";

  $postData = [
    'madvi' => $unitCode,
    'tuNgay' => '28-09-2024',
    'denNgay' => '06-10-2024',
    'ChucNang' => 'MaDonVi'
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

  preg_match_all('/<tr>(.*?)<\/tr>/s', $response, $rows);

  foreach ($rows[1] as $row) {
    preg_match_all('/<td>(.*?)<\/td>/s', $row, $cells);

    if (count($cells[1]) === 4) {
      $data[] = [
        'start_date' => trim(strip_tags($cells[1][0])),
        'end_date' => trim(strip_tags($cells[1][1])),
        'address' => html_entity_decode(trim(strip_tags($cells[1][2]))),
        'reason' => html_entity_decode(trim(strip_tags($cells[1][3]))),
      ];
    }
  }

  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

  curl_close($ch);
}