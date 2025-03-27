<?php
    $inData = getRequestInfo();

    // event being updated
    $eventID = $inData["Events_ID"];

    $time = $inData["Event_time"];
    $date = $inData["Date"];
    $name = $inData["Event_name"];
    $description = $inData["Description"];
    $adminID = $inData["Admins_ID"];

	// SuperAdmin_ID added once event is approved
    $superID = isset($inData["SuperAdmins_ID"]) ? $inData["SuperAdmins_ID"] : null;
    $rsoID = isset($inData["RSOs_ID"]) ? $inData["RSOs_ID"] : null;

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error, 404);
	}
	else
	{
        // get LocID from Locations
		$getLoc = $conn->prepare("SELECT LocID FROM Events_At WHERE Events_ID = ?");
		$getLoc->bind_param("i", $eventID);
		$getLoc->execute();
		$getLoc->store_result();

		if( $getLoc->num_rows == 0 )
		{
			$getLoc->close();
			$conn->close();
			returnWithError("Location not found in database!");
			return;
		}
        $getLoc->bind_result($locID);
        $getLoc->fetch();
        $getLoc->close();

		// check if there is a duplicate event in the same location at the same time
		$duplicateCheck = $conn->prepare("SELECT Events_ID FROM Events_At WHERE LocID = ? AND Event_time = ? AND Date = ?");
		$duplicateCheck->bind_param("iss", $locID, $time, $date);
		$duplicateCheck->execute();
		$duplicateCheck->store_result();

		if( $duplicateCheck->num_rows > 0 )
		{
			$duplicateCheck->close();
			$conn->close();
			returnWithError("An event already exists at this location and time!");
			return;
		}
		$duplicateCheck->close();

        // if found, update event
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