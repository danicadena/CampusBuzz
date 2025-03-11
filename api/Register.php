<?php
    $inData = getRequestInfo();

    $email = $inData["Email"];
    $firstName = $inData["First"];
    $lastName = $inData["Last"];
    $userName = $inData["Username"];
    $password = $inData["Password"];
    $userType = $inData["User_Type"];
    $uniName = $inData["University_name"];

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error, 404);
	}
	else
	{
		$checkStmt = $conn->prepare("SELECT Username FROM Users WHERE Username = ?");
		$checkStmt->bind_param("s", $userName);
		$checkStmt->execute();
		$checkStmt->store_result();

		if( $checkStmt->num_rows > 0 )
		{
			$checkStmt->close();
			$conn->close();
			returnWithError("Username already taken", 409);
			return;
		}

		$stmt = $conn->prepare("INSERT into Users (Email, First, Last, Username, Password, User_Type, University_name) VALUES(?,?,?,?,?,?,?)");
		$stmt->bind_param("ssss", $email, $firstName, $lastName, $userName, $password, $userType, $uniName);
		$stmt->execute();
		$stmt->close();
		$conn->close();
		returnWithError("");
	}

    function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}
	
	function returnWithError( $err, $statusCode = 200)
	{
		http_response_code($statusCode);
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}

?>