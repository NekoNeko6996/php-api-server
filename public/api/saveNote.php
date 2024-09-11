<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $json = file_get_contents('php://input');
  $data = json_decode($json, true);

  if (json_last_error() === JSON_ERROR_NONE) {

    if ($data) {
      $content = $data["content"];
      $tag = $data["tag"];
      $type = $data["type"];
      $icon = $data["icon"];
      $isDone = $data["isDone"];

      $userID = $jwtData["data"]->data->id;
      // check fields //


      //////////////////
      saveNote($content, json_encode($tag), $type, $icon, $isDone, $userID);
      echo json_encode([
        'status' => 'success',
        'message' => $data["tag"],
      ]);
    } else {
      echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON',
      ]);
    }
  }
}

function saveNote($content, $tag, $type, $icon, $isDone, $userID)
{
  $conn = Database::getInstance()->getConnection();
  $sql = "INSERT INTO notes (userID, content, type, tag, icon, isDone) VALUES (?, ?, ?, ?, ?, ?)";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssssi", $userID, $content, $type, $tag, $icon, $isDone);
  $stmt->execute();
}