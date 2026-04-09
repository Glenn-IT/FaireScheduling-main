<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../database/connection.php';
header("Content-Type: application/json");

// Allow only POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method Not Allowed"]);
    exit();
}

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

if ($data === null) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON format"]);
    exit();
}

// Validate input fields
if (empty($data["email"]) || empty($data["password"])) {
    echo json_encode(["status" => "error", "message" => "Email and Password are required."]);
    exit();
}

// Sanitize input
$email = filter_var(trim($data["email"]), FILTER_SANITIZE_EMAIL);
$password = trim($data["password"]);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Invalid email format."]);
    exit();
}

// Fetch user from database
$stmt = $conn->prepare("SELECT * FROM tblusers WHERE email = :email LIMIT 1");
$stmt->execute([":email" => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["status" => "error", "message" => "Invalid credentials. User not found."]);
    exit();
}

// Verify password
if (!password_verify($password, $user["password"])) {
    echo json_encode(["status" => "error", "message" => "Invalid credentials. Incorrect Password."]);
    exit();
}

// Store user details in session
$_SESSION['userid'] = $user['id'];
$_SESSION['lastname'] = $user['lastname'];
$_SESSION['firstname'] = $user['firstname'];
$_SESSION['middlename'] = $user['middlename'];;
$_SESSION['userrole'] = $user['user_role'];

// Determine dashboard based on user role
if ($user["user_role"] === "Admin") {
    $redirect_url = "navigation/admin/dashboard.php";
} else {
    $redirect_url = "navigation/user/user_index.php";
}

// Send valid JSON response
echo json_encode([
    "status" => "success",
    "message" => "Login successful.",
    "redirect" => $redirect_url,
    "user" => [
        "userid" => $user["id"],
        "lastname" => $user["lastname"],
        "firstname" => $user["firstname"],
        "middlename" => $user["middlename"],
        "email" => $user["email"],
        "role" => $user["user_role"]
    ]
]);
?>
