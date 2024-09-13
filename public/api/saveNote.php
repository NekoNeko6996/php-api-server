<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $json = file_get_contents('php://input');
  $data = json_decode($json, true);

  if (json_last_error() === JSON_ERROR_NONE) {

    if ($data) {
      $content = $data["content"];
      $tag = $data["tag"];
      $type = $data["priority"];
      $icon = $data["icon"];
      $day = $data["day"];
      $isDone = $data["isDone"];


      $userID = $jwtData["data"]->data->id;
      // check fields //


      //////////////////
      saveNote($content, json_encode($tag), $type, $icon, $isDone, $userID, $day);
      echo json_encode([
        'status' => 'success',
        'message' => "",
      ]);
    } else {
      echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON',
      ]);
    }
  }
}

function saveNote($content, $tag, $priority, $icon, $isDone, $userID, $day)
{
  $conn = Database::getInstance()->getConnection();
  $sql = "INSERT INTO notes (userID, content, priority, tag, icon, day, isDone) VALUES (?, ?, ?, ?, ?, ?, ?)";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssssssi", $userID, $content, $priority, $tag, $icon, $day, $isDone);
  $stmt->execute();
}