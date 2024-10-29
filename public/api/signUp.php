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

function saveData($fullName, $hash, $email, $gender, $birthDate, $address, $conn)
{
  $id = generateUniqueID();

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

function checkVerificationCode($email, $reqCode, $conn)
{
  $reqCode = strval($reqCode);

  $sql = "SELECT * FROM `session.verification` WHERE email = ? AND code = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $email, $reqCode);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $expired = $row['expired'];
    $session_id = $row['session_id'];
    $isUsed = $row['isUsed'];
    $code = $row['code'];

    if ($expired < time()) {
      echo json_encode([
        'status' => 'error',
        'message' => 'Verification code has expired',
      ]);
      return false;
    }

    if ($code != $reqCode) {
      echo json_encode([
        'status' => 'error',
        'message' => 'Verification code is incorrect',
      ]);
      return false;
    }

    $sql_delete = "DELETE FROM `session.verification` WHERE session_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("s", $session_id);
    $stmt_delete->execute();
    $stmt_delete->close();

    return $session_id;
  } else {
    echo json_encode([
      'status' => 'error',
      'message' => 'Verification code is incorrect',
    ]);
    return false;
  }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $json = file_get_contents('php://input');
  $data = json_decode($json, true);
  $conn = Database::getInstance()->getConnection();

  if (json_last_error() === JSON_ERROR_NONE) {
    $fullName = $data['fullName'] ?? '';
    $hash = $data['hash'] ?? '';
    $email = $data['email'] ?? '';
    $verifyCode = $data['code'] ?? '';

    // check email is exist
    $isExist = checkEmail($email, $conn);
    if (!$isExist) {
      echo json_encode([
        'status' => 'error',
        'message' => 'Email already exists'
      ]);
      return;
    }

    // check verify code
    $session_id = checkVerificationCode($email, $verifyCode, $conn);

    if (!$session_id) {
      return;
    }



    // save data
    // saveData($fullName, $hash, $email);
  } else {
    echo json_encode([
      'status' => 'error',
      'message' => 'Invalid JSON',
    ]);
  }
}
