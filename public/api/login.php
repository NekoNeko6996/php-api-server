<?php
use \Firebase\JWT\JWT;

function TokenEncode($data, $key)
{
  $secretKey = $key;
  $issuedAt = time();
  $expirationTime = $issuedAt + 3600 * 24 * 30;
  $payload = array(
    "iat" => $issuedAt,
    "exp" => $expirationTime,
    "iss" => "localhost",
    "data" => $data
  );
  $jwt = JWT::encode($payload, $secretKey, 'HS256');

  return $jwt;
}

function checkUser($email, $password)
{
  $conn = Database::getInstance()->getConnection();
  $sql = "SELECT hash, users.id, fullName FROM users INNER JOIN user_information ON users.id = user_information.id WHERE email = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows == 0) {
    echo json_encode([
      'status' => 'error',
      'message' => 'Invalid Email',
    ]);
    return;
  }

  //
  $row = $result->fetch_assoc();

  $hash = $row['hash'];
  $id = $row['id'];
  $fullName = $row['fullName'];

  //
  if (password_verify($password, $hash)) {
    echo json_encode([
      "status" => "success",
      "message" => "Login Successful",
      "token" => TokenEncode(["id" => $id, "fullName" => $fullName, 'created_at' => date('Y-m-d H:i:s')], $_ENV['JWT_SECRET_KEY']),
      "userID" => $id,
      "userName" => $fullName,
    ]);
  } else {
    echo json_encode([
      'status' => 'error',
      'message' => 'Invalid User',
    ]);
  }
}

// start here
// get login form here
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $json = file_get_contents('php://input');
  $data = json_decode($json, true);

  if (json_last_error() === JSON_ERROR_NONE) {
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    checkUser($email, $password);
  } else {
    echo json_encode([
      'status' => 'error',
      'message' => 'Invalid User',
    ]);
  }
}
