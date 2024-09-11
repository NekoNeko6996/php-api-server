<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// check jwt token
function authenticate($key): array
{
  $headers = getallheaders();
  if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if ($jwt) {
      try {
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        return ["data" => $decoded, "token" => $jwt];
      } catch (Exception $e) {
        http_response_code(401);
        echo json_encode([
          "status" => "error",
          "message" => "Access denied.",
          "error" => [
            "description" => $e->getMessage(),
            "timestamp" => date("Y-m-d H:i:s")
          ]
        ]);
        exit();
      }
    }
  }
  // Trường hợp không có token
  http_response_code(401);
  echo json_encode([
    "status" => "error",
    "message" => "Access denied.",
    "error" => [
      "type" => "MissingTokenException",
      "description" => "No token provided in the Authorization header.",
      "timestamp" => date("Y-m-d H:i:s")
    ]
  ]);
  exit();
}