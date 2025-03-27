<?php
    $inData = getRequestInfo();

    // event being updated
    $eventID = $inData["Events_ID"];
	// admin doing the update
	$adminID = $inData["Admins_ID"];

    $time = $inData["Event_time"];
    $date = $inData["Date"];
    $name = $inData["Event_name"];
    $description = $inData["Description"];

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error, 404);
	}
	else
	{
		// check that the correct admin is updating the event
		$type = $conn->prepare("SELECT Event_type FROM Events_At WHERE Events_ID = ?");
        $type->bind_param("i", $eventID);
        $type->execute();
        $type->bind_result($eventType);
        $type->fetch();
        $type->close();

		if ($eventType === null) {
			$conn->close();
			returnWithError("Event_type is null!");
			return;
		}		

        if ($eventType === "Public") {
            $auth = $conn->prepare("SELECT Admins_ID FROM Public_Events_Creates WHERE Events_ID = ?");
            $auth->bind_param("i", $eventID);
        } elseif ($eventType === "Private") {
            $auth = $conn->prepare("SELECT Admins_ID FROM Private_Events_Creates WHERE Events_ID = ?");
            $auth->bind_param("i", $eventID);
        } elseif ($eventType === "RSO") {
            // get RSOs_ID to then find Admin in charge
            $getRSO = $conn->prepare("SELECT RSOs_ID FROM RSOs_Events_Owns WHERE Events_ID = ?");
            $getRSO->bind_param("i", $eventID);
            $getRSO->execute();
            $getRSO->bind_result($foundRSO);
            $getRSO->fetch();
            $getRSO->close();

            $auth = $conn->prepare("SELECT Admins_ID FROM RSOs_Creates WHERE RSOs_ID = ?");
            $auth->bind_param("i", $foundRSO);
        } else {
            $conn->close();
            returnWithError("Invalid event type.");
            return;
        }

        $auth->execute();
        $auth->bind_result($foundAdmin);
        $auth->fetch();
        $auth->close();

        if ($adminID !== $foundAdmin)
        {
            $conn->close();
            returnWithError("Not authorized to update this event!");
            return;
        }

        // if authorized, update event
		$stmt = $conn->prepare("UPDATE Events_At SET Event_time=?,Date=?,Event_name=?,Description=? WHERE Events_ID = ?");
		$stmt->bind_param("ssssi", $time, $date, $name, $description, $eventID);
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