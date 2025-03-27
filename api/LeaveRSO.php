<?php
    $inData = getRequestInfo();

    $uid = $inData["UID"];
    $rsoID = $inData["RSOs_ID"];

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$checkStmt = $conn->prepare("SELECT UID,RSOs_ID FROM Joins WHERE UID = ? AND RSOs_ID = ?");
		$checkStmt->bind_param("ii", $uid, $rsoID);
		$checkStmt->execute();
		$checkStmt->store_result();

		if( $checkStmt->num_rows == 0 )
		{
			$checkStmt->close();
			$conn->close();
			returnWithError("No user found in RSO!");
			return;
		}
		$stmt = $conn->prepare("DELETE from Joins where UID = ? AND RSOs_ID = ?");
		$stmt->bind_param("ii", $uid, $rsoID);
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