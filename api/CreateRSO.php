<?php

	// UID: []
	// Admin_UID: 1

    $inData = getRequestInfo();

    $adminID = isset($inData["Admins_ID"]) ? $inData["Admins_ID"]: null;

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error, 404);
	}
	else
	{
		$stmt = $conn->prepare("INSERT into RSOs_Creates (Admins_ID) VALUES(?)");
		$stmt->bind_param("i", $adminID);
		$stmt->execute();
		$rsoID = $conn->insert_id;
		$stmt->close();

		// NEED - add the creator as first member - if student created it, they should be promoted as an admin

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