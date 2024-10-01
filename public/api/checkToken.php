<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// check jwt token
function authenticate($key): array
{
  // Get the Authorization header
  $header = getallheaders();
  list($jwt) = sscanf($header['Authorization'] ?? '', 'Bearer %s');

  // Check if a token was provided
  if ($jwt) {
    try {
      // Decode the JWT token using the provided key and HS256 algorithm
      $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

      // Return the decoded data and the token itself in the response
      return [
        "status" => "success",
        "message" => "Token is valid.",
        "data" => $decoded,
        "token" => $jwt
      ];
    } catch (Exception $e) {
      // If the token is invalid, respond with a 401 error and detailed message
      http_response_code(401);
      die(json_encode([
        "status" => "error",
        "message" => "Access denied due to invalid token.",
        "error" => [
          "type" => get_class($e), // Type of the exception (JWT-specific errors)
          "description" => $e->getMessage(),
          "timestamp" => date("Y-m-d H:i:s")
        ]
      ]));
    }
  }

  // If no token was provided, respond with a 401 error and relevant message
  http_response_code(401);
  die(json_encode([
    "status" => "error",
    "message" => "Access denied due to missing token.",
    "error" => [
      "type" => "MissingTokenException",
      "description" => "No token provided in the Authorization header.",
      "timestamp" => date("Y-m-d H:i:s")
    ]
  ]));
}
