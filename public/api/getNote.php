<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $json = file_get_contents('php://input');

  $userID = $jwtData["data"]->data->id;

  $conn = Database::getInstance()->getConnection();
  $sql = "SELECT id, content, priority, tag, icon, isDone FROM notes WHERE userID = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $userID);
  $stmt->execute();

  $result = $stmt->get_result();

  $notes = [];

  while ($row = $result->fetch_assoc()) {
    $notes[] = $row;
  }

  echo json_encode([
    'status' => 'success',
    'notes' => $notes,
  ]);
}

