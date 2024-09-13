<?php
// Kiểm tra kết nối
$conn = Database::getInstance()->getConnection();
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function loadUserData($conn)
{
    $sql = "SELECT * FROM user_information INNER JOIN users ON user_information.id = users.id";
    $stmt = $conn->prepare($sql);
    // $stmt->bind_param("s", $jwtData->data->id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid User',
        ]);
        return;
    }

    return $result->fetch_all();
}

echo json_encode(loadUserData($conn));