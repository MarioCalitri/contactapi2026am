<?php
    require ('connect.php');
    header('Content-Type: application/json');

    $postdata = file_get_contents("php://input");

    if (isset($postdata) && !empty($postdata)) {
        $request = json_decode($postdata);
        $payload = isset($request->data) ? $request->data : $request;

        if (trim($payload->firstName ?? '') == '' || trim($payload->lastName ?? '') == '' ||
            trim($payload->emailAddress ?? '') == '' || trim($payload->phoneNumber ?? '') == '' ||
            trim($payload->dob ?? '') == '') {

            http_response_code(400);
            echo json_encode(['message' => 'missing required fields']);
            exit;
        }

        $firstName = mysqli_real_escape_string($con, trim($payload->firstName));
        $lastName = mysqli_real_escape_string($con, trim($payload->lastName));
        $emailAddress = mysqli_real_escape_string($con, trim($payload->emailAddress));
        $phoneNumber = mysqli_real_escape_string($con, trim($payload->phoneNumber));
        $dob = mysqli_real_escape_string($con, trim($payload->dob));
        $imageName = mysqli_real_escape_string($con, trim($payload->imageName ?? ''));

        $origimg = str_replace('\\', '/', $imageName);
        $new = basename($origimg);
        if (empty($new)) {
            $new = 'placeholder_100.jpg';
        }

        try {
            // Check duplicate email
            $checkEmailSql = "SELECT 1 FROM contacts WHERE emailAddress = '{$emailAddress}'";
            $checkEmailResult = mysqli_query($con, $checkEmailSql);

            if (mysqli_num_rows($checkEmailResult) > 0) {
                http_response_code(409);
                echo json_encode(['message' => 'Duplicate email address.']);
                exit;
            }

            $typeID = isset($payload->typeID) && $payload->typeID !== '' ? (int)$payload->typeID : 1;

            // Insert into database
            $sql = "INSERT INTO `contacts`(`firstName`, `lastName`, `emailAddress`, `phoneNumber`, `dob`, `imageName`, `typeID`)
                VALUES('{$firstName}', '{$lastName}', '{$emailAddress}', '{$phoneNumber}', '{$dob}', '{$new}', {$typeID})";
            mysqli_query($con, $sql);

            http_response_code(201);
            echo json_encode([
                'data' => [
                    'contactID' => mysqli_insert_id($con),
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'emailAddress' => $emailAddress,
                    'phoneNumber' => $phoneNumber,
                    'dob' => $dob,
                    'imageName' => $new
                ]
            ]);
        } catch (mysqli_sql_exception $e) {
            http_response_code(422);
            echo json_encode(['message' => 'SQL Error: ' . $e->getMessage()]);
        }
    }
?>