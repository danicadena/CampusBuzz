<?php
    $inData = getRequestInfo();

    $eventID = $inData["Events_ID"];

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$checkStmt = $conn->prepare("SELECT Events_ID FROM Events_At WHERE Events_ID = ? ");
		$checkStmt->bind_param("i", $eventID);
		$checkStmt->execute();
		$checkStmt->store_result();

		if( $checkStmt->num_rows == 0 )
		{
			$checkStmt->close();
			$conn->close();
			returnWithError("No event found!");
			return;
		}
		
		$stmt = $conn->prepare("DELETE from Events_At where Events_ID = ?");
		$stmt->bind_param("i", $eventID);
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
	
	function returnWithError( $err )
	{
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}


?>