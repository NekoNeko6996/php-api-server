<?php
header('Content-Type: text/html; charset=utf-8');

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>

  <style>
    th {
      text-align: left;
      padding: 10px;
    }

    td {
      padding: 10px;
    }

    tr:nth-child(even) {
      background-color: #DDD;
    }

    tr:nth-child(1) {
      border-bottom: 1px solid black;

    }

    table {
      border-collapse: collapse;
    }
  </style>
</head>

<body>
  <h1>Admin</h1>
  <table>
    <tr>
      <th>
        <p>ID</p>
      </th>
      <th>
        <p>Email</p>
      </th>
      <th>
        <p>Hash</p>
      </th>
      <th>
        <p>Full Name</p>
      </th>
      <th>
        <p>Birth Date</p>
      </th>
      <th>
        <p>Gender</p>
      </th>
      <th>
        <p>Address</p>
      </th>
      <th>
        <p>Create At</p>
      </th>
      <th>
        <p>Update At</p>
      </th>
    </tr>
    <?php foreach (loadUserData($conn) as $row) { ?>
      <tr>
        <td><?= $row[0] ?></td>
        <td><?= $row[8] ?></td>
        <td><?= $row[9] ?></td>
        <td><?= $row[1] ?></td>
        <td><?= $row[2] ?></td>
        <td><?= $row[3] == 1 ? "Male" : "Female" ?></td>
        <td><?= $row[4] ?></td>
        <td><?= $row[10] ?></td>
        <td><?= $row[11] ?? "null" ?></td>
      </tr>
    <?php } ?>
  </table>
</body>

</html>