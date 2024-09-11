<?php
function generateUniqueID()
{
  return uniqid('id_', true);
}


function checkEmail($email, $conn)
{
  try {
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      return false;
    }

    return $email;
  } catch (Exception $e) {
    error_log($e->getMessage());
    return false;
  }
}

function saveData($fullName, $hash, $email, $gender, $birthDate, $address)
{
  $conn = Database::getInstance()->getConnection();
  $id = generateUniqueID();
  $email = checkEmail($email, $conn);

  if (!$email) {
    echo json_encode([
      'status' => 'error',
      'message' => 'Email already exists'
    ]);
    return;
  }

  try {
    $sql_user = "INSERT INTO users (id, email, hash) VALUES (?, ?, ?)";
    $stmt_user = $conn->prepare($sql_user);
    if (!$stmt_user) {
      throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $stmt_user->bind_param("sss", $id, $email, $hash);
    if (!$stmt_user->execute()) {
      throw new Exception("Execute statement failed: " . $stmt_user->error);
    }

    $sql_userInfo = "INSERT INTO user_information (id, fullName, gender, birthDate, address) VALUES (?, ?, ?, ?, ?)";
    $stmt_userInfo = $conn->prepare($sql_userInfo);
    if (!$stmt_userInfo) {
      throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $stmt_userInfo->bind_param("ssiss", $id, $fullName, $gender, $birthDate, $address);
    if (!$stmt_userInfo->execute()) {
      throw new Exception("Execute statement failed: " . $stmt_userInfo->error);
    }

  } catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
    ]);
    return;
  }

  echo json_encode([
    'status' => 'success',
    'message' => 'Data saved successfully',
  ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $json = file_get_contents('php://input');
  $data = json_decode($json, true);

  if (json_last_error() === JSON_ERROR_NONE) {
    $fullName = $data['fullName'] ?? '';
    $hash = $data['hash'] ?? '';
    $email = $data['email'] ?? '';
    $gender = $data['gender'] ?? true;
    $birthDate = $data['birthDate'] ?? '';
    $address = $data['address'] ?? '';

    saveData($fullName, $hash, $email, $gender, $birthDate, $address);
  } else {
    echo json_encode([
      'status' => 'error',
      'message' => 'Invalid JSON',
    ]);
  }
}
