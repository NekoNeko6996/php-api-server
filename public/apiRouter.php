<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../database/Connect.php';

use \Dotenv\Dotenv;
header('Content-Type: application/json; charset=utf-8');


// Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../', '.env');
$dotenv->load();

// Get .env variables
$secretKey = $_ENV['JWT_SECRET_KEY'];



// get route request
$requestedRoute = $_GET['route'] ?? 'get';

// route list
$apiNoCheckedAllowedRoutes = ["signup", "login", "get", "getPowerOutageSchedule"];
$apiCheckedAllowedRoutes = ['saveNote', 'getNote'];

if ($requestedRoute == "checkToken") {

  //check token route
  include "./api/checkToken.php";
  $jwtData = authenticate($secretKey);
  $data = $jwtData["data"];
  $token = $jwtData["token"];
  echo json_encode(["userID" => $data->data->id, "fullName" => $data->data->fullName, "status" => "success", "message" => "Token Verified", "token" => $token]);

} else if (in_array($requestedRoute, $apiCheckedAllowedRoutes)) {

  // include check token route
  $filePath = __DIR__ . '/api/' . $requestedRoute . '.php';
  if (file_exists($filePath)) {
    include "./api/checkToken.php";
    $jwtData = authenticate($secretKey);

    // open file 
    include $filePath;
  } else {
    echo "Error: File not found!";
  }
} else if (in_array($requestedRoute, $apiNoCheckedAllowedRoutes)) {

  // no include check token route
  $filePath = __DIR__ . '/api/' . $requestedRoute . '.php';
  if (file_exists($filePath)) {
    include $filePath;
  } else {
    http_response_code(404);
    echo "Error: File not found!";
  }
} else {
  http_response_code(404);
  echo "Error: Invalid route!";
}