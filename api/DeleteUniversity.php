<?php
    $inData = getRequestInfo();

    $uniID = $inData["Uni_ID"];

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$checkStmt = $conn->prepare("SELECT Uni_ID FROM University WHERE Uni_ID = ? ");
		$checkStmt->bind_param("i", $uniID);
		$checkStmt->execute();
		$checkStmt->store_result();

		if( $checkStmt->num_rows == 0 )
		{
			$checkStmt->close();
			$conn->close();
			returnWithError("No university profile found!");
			return;
		}
		$stmt = $conn->prepare("DELETE from University where Uni_ID = ?");
		$stmt->bind_param("i", $uniID);
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