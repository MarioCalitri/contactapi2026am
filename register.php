<?php
    require ('connect.php');
    header ('Content-Type: application/json');

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    $postdata = file_get_contaents("php://input");




    if (!isset($postdata->userName, $postdata->password, $postdata->emailAddress))
        {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        $userName = trim,($postdata->userName);
        $password = trim($postdata->password);
        $emailAddress = trim($postdata->emailAddress);

        $check = $con->prepare("SELECT * FROM registrations WHERE userName = ? OR emailAddress = ?");
        $check->bind_param("ss", $userName, $emailAddress);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'username or email exists']);
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        


?>