<?php

    $inData = getRequestInfo();

    $superID = $inData["SuperAdmins_ID"];
    $eventID = $inData["Events_ID"];
    $approved = 'approved';

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error, 404);
	}
    else
    {
        $eventType = $conn->prepare("SELECT Event_type FROM Events_At WHERE Events_ID = ?");
        $eventType->bind_param("i", $eventID);
        $eventType->execute();
        $eventType->store_result();

        if ($eventType->num_rows == 0) {
            $eventType->close();
            $conn->close();
            returnWithError("Event not found!");
            return;
        }

        $eventType->bind_result($type);
        $eventType->fetch();
        $eventType->close();

        if (!$type) {
            $conn->close();
            returnWithError("Event type fetch failed or null.");
            return;
        }

        if($type === 'Public')
        {
            // approve event
            $approve = $conn->prepare("UPDATE Events_At SET Approval_Status = ? WHERE Events_ID = ?");
            $approve->bind_param("si", $approved, $eventID);
            $approve->execute();
            $approve->close();

            // add super admin
            $addSuper = $conn->prepare("UPDATE Public_Events_Creates SET SuperAdmins_ID = ? WHERE Events_ID = ?");
            $addSuper->bind_param("si", $superID, $eventID);
            $addSuper->execute();
            $addSuper->close();
        }
        else if($type === 'Private')
        {
            // approve event
            $approve = $conn->prepare("UPDATE Events_At SET Approval_Status = ? WHERE Events_ID = ?");
            $approve->bind_param("si", $approved, $eventID);
            $approve->execute();
            $approve->close();

            // add super admin
            $addSuper = $conn->prepare("UPDATE Private_Events_Creates SET SuperAdmins_ID = ? WHERE Events_ID = ?");
            $addSuper->bind_param("si", $superID, $eventID);
            $addSuper->execute();
            $addSuper->close();
        }
        else
        {
            $conn->close();
            returnWithError("Super Admins do not approve RSO Events!");
            return;
        }

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