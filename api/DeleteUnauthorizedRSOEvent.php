<?php

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$checkStmt = $conn->prepare("
            DELETE FROM Events_At
            WHERE Event_type = 'RSO' AND Approval_Status = 'pending'
        ");

		if(!$stmt)
		{
			returnWithError("failed to delete");
            $conn->close();
            exit;
		}

		$stmt->execute();
        $deletedCount = $stmt->affected_rows;
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