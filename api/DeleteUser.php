<?php
    $inData = getRequestInfo();

    $uid = $inData["UID"];

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$checkStmt = $conn->prepare("SELECT UID FROM Users WHERE UID = ? ");
		$checkStmt->bind_param("i", $uid);
		$checkStmt->execute();
		$checkStmt->store_result();

		if( $checkStmt->num_rows == 0 )
		{
			$checkStmt->close();
			$conn->close();
			returnWithError("No user found");
			return;
		}
		$stmt = $conn->prepare("DELETE from Users where UID = ?");
		$stmt->bind_param("i", $uid);
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

    function returnWithSuccess()
    {
        $retValue = '{"success":"User deleted successfully"}';
        sendResultInfoAsJson( $retValue );
    }


?>