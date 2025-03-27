<?php
    $inData = getRequestInfo();

    $uid = $inData["UID"];
    $eventID = $inData["Events_ID"];
    $rating = $inData["Rating"];
    $text = $inData["Text"];
    $timestamp = date("Y-m-d H:i:s");

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error, 404);
	}
	else
	{
		$stmt = $conn->prepare("INSERT into Comments (UID,Events_ID,Rating,Text,Timestamp) VALUES(?,?,?,?,?)");
		$stmt->bind_param("iiiss", $uid, $eventID, $rating, $text, $timestamp);
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