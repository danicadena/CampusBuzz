<?php

	$inData = getRequestInfo();

    $locID = $inData["LocID"];
    $time = $inData["Event_time"];
    $date = $inData["Date"];
    $name = $inData["Event_name"];
    $description = $inData["Description"];
    $type = $inData["Event_type"];
	$approval = 'pending';
    $adminID = (int)$inData["Admins_ID"];

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

        // if found, create an event
		$stmt = $conn->prepare("INSERT into Events_At (LocID, Event_time, Date, Event_name, Description, Event_type, Approval_Status) VALUES(?,?,?,?,?,?,?)");
		$stmt->bind_param("issssss", $locID, $time, $date, $name, $description, $type, $approval);
		$stmt->execute();
		$eventID = $conn->insert_id;
		$stmt->close();

        // create public event
		if ($type === 'Public')
		{
			$publicStmt = $conn->prepare("INSERT INTO Public_Events_Creates (Events_ID, Admins_ID) VALUES (?,?)");
			$publicStmt->bind_param("ii", $eventID, $adminID);
			$publicStmt->execute();
			$publicStmt->close();
		}
        // create private event
		elseif ($type === "Private")
		{
			$privateStmt = $conn->prepare("INSERT INTO Private_Events_Creates (Events_ID, Admins_ID) VALUES (?,?)");
			$privateStmt->bind_param("ii", $eventID, $adminID);
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

			// check if admin adding RSO event is the owner of RSO 
			$ownerStmt = $conn->prepare("SELECT Admins_ID FROM RSOs_Creates WHERE RSOs_ID = ?");
			$ownerStmt->bind_param("i", $rsoID);
			$ownerStmt->execute();
			$ownerStmt->bind_result($ownerAdminID);
			$ownerStmt->fetch();
			$ownerStmt->close();

			if ($ownerAdminID !== $adminID) {
				$conn->close();
				returnWithError("Not authorized to create events for this RSO!");
				return;
			}

            $rsoStmt = $conn->prepare("INSERT INTO RSO_Events_Owns (Events_ID, RSOs_ID) VALUES (?,?)");
			$rsoStmt->bind_param("ii", $eventID, $rsoID);
			$rsoStmt->execute();
			$rsoStmt->close();

			// change approval status to approved because super admin does not need to approve
			$approve = $conn->prepare("UPDATE Events_At SET Approval_Status = 'approved' WHERE Events_ID = ?");
			$approve->bind_param("i", $eventID);
			$approve->execute();
			$approve->close();
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