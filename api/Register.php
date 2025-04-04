<?php
    $inData = getRequestInfo();

    $email = $inData["Email"];
    $firstName = $inData["First"];
    $lastName = $inData["Last"];
    $userName = $inData["Username"];
    $password = $inData["Password"];
    $userType = $inData["User_Type"];
    $uniName = isset($inData["University_name"]) ? $inData["University_name"] : null;

	// validate fields
	if($userType === 'Student' && empty($uniName))
	{
		returnWithError("Student must belong to a university!");
		return;
	}

	if($userType === 'SuperAdmin')
	{
		$uniName = null;
	}

	// connect to database
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
		$stmt->bind_param("sssssss", $email, $firstName, $lastName, $userName, $password, $userType, $uniName);
		$stmt->execute();
		$uid = $conn->insert_id;
		$stmt->close();

		if ($userType === "SuperAdmin")
		{
			$superStmt = $conn->prepare("INSERT INTO SuperAdmins (UID) VALUES (?)");
			$superStmt->bind_param("i", $uid);
			$superStmt->execute();
			$superStmt->close();
		}

		$getinfo = $conn->prepare("SELECT First,User_Type FROM Users WHERE UID=?");
		$getinfo->bind_param("i", $uid);
		$getinfo->execute();
		$getinfo->bind_result($first, $type);
		$getinfo->fetch();
		$getinfo->close();

		$conn->close();
		returnWithInfo($first, $uid, $type);
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

	function returnWithInfo( $firstName, $uid, $type )
	{
		$retValue = '{"id":' . $uid . ',"user_type":"' . $type . '","firstName":"' . $firstName . '","error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
	function returnWithError( $err, $statusCode = 200)
	{
		http_response_code($statusCode);
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}

?>