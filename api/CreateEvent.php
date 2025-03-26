<?php
    $inData = getRequestInfo();

    $locID = $inData["Loc_ID"];
    $time = $inData["Event_time"];
    $date = $inData["Date"];
    $name = $inData["Event_name"];
    $description = $inData["Description"];
    $type = $inData["Event_type"];

    $adminID = isset($inData["Admins_ID"]) ? $inData["Admins_ID"] : null;
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
		$checkStmt = $conn->prepare("SELECT LocID FROM Locations WHERE LocID = ?");
		$checkStmt->bind_param("i", $locID);
		$checkStmt->execute();
		$checkStmt->store_result();

		if( $checkStmt->num_rows == 0 )
		{
			$checkStmt->close();
			$conn->close();
			returnWithError("Location not found in database!");
			return;
		}

		// check if there is a duplicate event in the same location at the same time
		$duplicateCheck = $conn->prepare("SELECT Events_ID FROM Events_At WHERE LocID = ? AND Event_time = ?");
		$duplicateCheck->bind_param("is", $locID, $time);
		$duplicateCheck->execute();
		$duplicateCheck->store_result();

		if( $duplicateCheck->num_rows > 0 )
		{
			$duplicateCheck->close();
			$conn->close();
			returnWithError("An event already exists at this location and time!");
			return;
		}

        // if found, create an event
		$stmt = $conn->prepare("INSERT into Events_At (LocID, Event_time, Date, Event_name, Description, Event_type) VALUES(?,?,?,?,?,?)");
		$stmt->bind_param("isssss", $locID, $time, $date, $name, $description, $type);
		$stmt->execute();
		$eventID = $conn->insert_id;
		$stmt->close();

        // create public event
		if ($type === 'Public')
		{
			$publicStmt = $conn->prepare("INSERT INTO Public_Events_Creates (Events_ID, Admins_ID, SuperAdmins_ID) VALUES (?,?,?)");
			$publicStmt->bind_param("iii", $eventID, $adminID, $superID);
			$publicStmt->execute();
			$publicStmt->close();
		}
        // create private event
		elseif ($type === "Private")
		{
			$privateStmt = $conn->prepare("INSERT INTO Private_Events_Creates (Events_ID, Admins_ID, SuperAdmins_ID) VALUES (?,?,?)");
			$privateStmt->bind_param("iii", $eventID, $adminID, $superID);
			$privateStmt->execute();
			$privateStmt->close();
		}
        // create RSO event
        elseif ($type === "RSO")
        {
            // check if RSOs_ID exists
            $checkRSO = $conn->prepare("SELECT RSOs_ID FROM RSOs_Creates WHERE RSOs_ID = ?");
            $checkRSO->bind_param("i", $rsoID);
            $checkRSO->execute();
            $checkRSO->store_result();

            if ($checkRSO->num_rows == 0) {
                $checkRSO->close();
                $conn->close();
                returnWithError("RSO not found!");
                return;
            }
            $checkRSO->close();

            $rsoStmt = $conn->prepare("INSERT INTO RSOs_Events_Owns (Events_ID, RSOs_ID) VALUES (?,?)");
			$rsoStmt->bind_param("ii", $eventID, $rsoID);
			$rsoStmt->execute();
			$rsoStmt->close();
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