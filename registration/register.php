<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "../database/connection.php";

header('Content-Type: application/json');
 // This should create a PDO instance named $conn

try {
    // Decode JSON input
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        echo json_encode(["status" => "error", "message" => "Invalid input."]);
        exit;
    }

    // Assign values
    $lastname = $input['lastname'] ?? '';
    $firstname = $input['firstname'] ?? '';
    $middlename = $input['middlename'] ?? '';
    $birthday = $input['birthday'] ?? '';
    $age = $input['age'] ?? '';
    $mobile = $input['mobile'] ?? '';
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format."]);
        exit;
    }

    // Password strength check
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        echo json_encode([
            "status" => "error",
            "message" => "Password must be at least 8 characters long and include uppercase, lowercase, number, and special character."
        ]);
        exit;
    }

    // Required fields check
    if (
        empty($lastname) || empty($firstname) || empty($middlename) ||  empty($birthday) ||
        empty($age) || empty($mobile) || empty($email) || empty($password)
    ) {
        echo json_encode(["status" => "error", "message" => "Please fill in all required fields."]);
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT email FROM tblusers WHERE email = :email");
    $stmt->execute(['email' => $email]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["status" => "error", "message" => "Email already registered."]);
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into tblusers
    $insert = $conn->prepare("
        INSERT INTO tblusers (
            lastname, firstname, middlename, birthday, age,
            mobilenumber, email, password, datecreated, user_role, user_active
        ) VALUES (
            :lastname, :firstname, :middlename, :birthday, :age,
            :mobile, :email, :password, CURDATE(), 'User', 1
        )
    ");

    $insert->execute([
        'lastname'        => $lastname,
        'firstname'       => $firstname,
        'middlename'      => $middlename,
        'birthday'        => $birthday,
        'age'             => $age,
        'mobile'          => $mobile,
        'email'           => $email,
        'password'        => $hashed_password
    ]);

    echo json_encode(["status" => "success"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
