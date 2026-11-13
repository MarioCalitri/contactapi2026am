<?php
    require('connect.php');

    header('Content-type: application/json');

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Get posted data
    $postdata = file_get_contents("php://input");
    
    if (isset($postdata) && !empty($postdata)) {
        $request = json_decode($postdata);

        // Support both nested { data: { ... } } and flat JSON payloads
        $payload = isset($request->data) ? $request->data : $request;

        $firstName = isset($payload->firstName) ? trim($payload->firstName) : '';
        $lastName = isset($payload->lastName) ? trim($payload->lastName) : '';
        $emailAddress = isset($payload->emailAddress) ? trim($payload->emailAddress) : '';
        $phoneNumber = isset($payload->phoneNumber) ? trim($payload->phoneNumber) : '';
        $dob = isset($payload->dob) ? trim($payload->dob) : '';

        // Validate required fields
        if ($firstName === '' || $lastName === '' || $emailAddress === '' || $phoneNumber === '' || $dob === '') {
            http_response_code(400);
            echo json_encode(['message' => 'Missing required fields.']);
            exit;
        }

        // Sanitize input data
        $firstName = mysqli_real_escape_string($con, $firstName);
        $lastName = mysqli_real_escape_string($con, $lastName);
        $emailAddress = mysqli_real_escape_string($con, $emailAddress);
        $phoneNumber = mysqli_real_escape_string($con, $phoneNumber);
        $dob = mysqli_real_escape_string($con, $dob);

        // Check for duplicate email address
        $checkEmailsql = "SELECT 1 FROM contacts WHERE emailAddress = '{$emailAddress}'";
        $checkEmailResult = mysqli_query($con, $checkEmailsql);

        if (mysqli_num_rows($checkEmailResult) > 0) {
            http_response_code(409);
            echo json_encode(['message' => 'Duplicate email address.']);
            exit;
        }

        // Insert to database
        $sql = "INSERT INTO `contacts` (`firstName`, `lastName`, `emailAddress`, `phoneNumber`, `dob`) 
                VALUES ('{$firstName}', '{$lastName}', '{$emailAddress}', '{$phoneNumber}', '{$dob}')";

        if (mysqli_query($con, $sql)) {
            http_response_code(201);
            echo json_encode([
                'data' => [
                    'contactID' => mysqli_insert_id($con),
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'emailAddress' => $emailAddress,
                    'phoneNumber' => $phoneNumber,
                    'dob' => $dob
                ]
            ]);
        } else {
            http_response_code(422);
            echo json_encode(['message' => 'db insert failed.']);
        }
    }
?>