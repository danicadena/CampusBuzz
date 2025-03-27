<?php

    $inData = getRequestInfo();

	$eventID = $inData["Events_ID"];

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error, 404);
	}
    else
    {
        // check the event exists
        $check = $conn->prepare("SELECT Events_ID FROM Events_At WHERE Events_ID = ?");
        $check->bind_param("i", $eventID);
        $check->execute();
        $check->store_result();

        if ($check->num_rows == 0) {
            $check->close();
            $conn->close();
            returnWithError("Event not found!");
            return;
        }
        $check->close();

        $update = $conn->prepare("DELETE from Events_At WHERE Events_ID = ? ");
        $update->bind_param("i", $eventID);
        $update->execute();
        $update->close();

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