<?php
    $inData = getRequestInfo();

    $uid = $inData["UID"];
    $eventID = $inData["Events_ID"];

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$checkStmt = $conn->prepare("SELECT UID,Events_ID FROM Comments WHERE UID = ? AND Events_ID = ?");
		$checkStmt->bind_param("ii", $uid, $eventID);
		$checkStmt->execute();
		$checkStmt->store_result();

		if( $checkStmt->num_rows == 0 )
		{
			$checkStmt->close();
			$conn->close();
			returnWithError("No comment found!");
			return;
		}
		$stmt = $conn->prepare("DELETE from Comments where UID = ? AND Events_ID = ? LIMIT 1");
		$stmt->bind_param("ii", $uid, $eventID);
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