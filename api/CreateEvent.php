<?php

	$inData = getRequestInfo();

    $locID = $inData["LocID"];
    $time = $inData["Event_time"];
    $date = $inData["Date"];
    $name = $inData["Event_name"];
    $description = $inData["Description"];
    $type = $inData["Event_type"];
	$approval = 'pending';
    $adminID = $inData["Admins_ID"];

    $rsoID = isset($inData["RSOs_ID"]) ? $inData["RSOs_ID"] : null;

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error, 404);
	}
	else
	{
		$conn->begin_transaction(); 

		try {
			// Step 1: Check if location exists
			$checkStmt = $conn->prepare("SELECT LocID FROM Locations WHERE LocID = ?");
			$checkStmt->bind_param("i", $locID);
			$checkStmt->execute();
			$checkStmt->store_result();

			if ($checkStmt->num_rows == 0) {
				throw new Exception("Location not found in database!");
			}
			$checkStmt->close();

			// Step 2: Check for duplicate event
			$duplicateCheck = $conn->prepare("SELECT Events_ID FROM Events_At WHERE LocID = ? AND Event_time = ? AND Date = ?");
			$duplicateCheck->bind_param("iss", $locID, $time, $date);
			$duplicateCheck->execute();
			$duplicateCheck->store_result();

			if ($duplicateCheck->num_rows > 0) {
				throw new Exception("An event already exists at this location and time!");
			}
			$duplicateCheck->close();

			$validTypes = ["Public", "Private", "RSO"];
			if (!in_array($type, $validTypes)) {
				returnWithError("Invalid event type: $type", 400);
				exit;
			}

			// Step 3: Insert event
			$stmt = $conn->prepare("INSERT INTO Events_At (LocID, Event_time, Date, Event_name, Description, Event_type, Approval_Status) VALUES (?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param("issssss", $locID, $time, $date, $name, $description, $type, $approval);
			$stmt->execute();
			$eventID = $conn->insert_id;
			$stmt->close();

			// Step 4: Insert into proper event type table
			if ($type === 'Public') {
				$stmt = $conn->prepare("INSERT INTO Public_Events_Creates (Events_ID, Admins_ID) VALUES (?, ?)");
				$stmt->bind_param("ii", $eventID, $adminID);
				$stmt->execute();
				$stmt->close();
			} elseif ($type === "Private") {
				$stmt = $conn->prepare("INSERT INTO Private_Events_Creates (Events_ID, Admins_ID) VALUES (?, ?)");
				$stmt->bind_param("ii", $eventID, $adminID);
				$stmt->execute();
				$stmt->close();
			} elseif ($type === "RSO") {
				// Validate RSO
				$checkRSO = $conn->prepare("SELECT RSOs_ID FROM RSOs_Creates WHERE RSOs_ID = ?");
				$checkRSO->bind_param("i", $rsoID);
				$checkRSO->execute();
				$checkRSO->store_result();

				if ($checkRSO->num_rows == 0) {
					throw new Exception("RSO not found!");
				}
				$checkRSO->close();

				// Verify admin owns the RSO
				$ownerStmt = $conn->prepare("SELECT Admins_ID FROM RSOs_Creates WHERE RSOs_ID = ?");
				$ownerStmt->bind_param("i", $rsoID);
				$ownerStmt->execute();
				$ownerStmt->bind_result($ownerAdminID);
				$ownerStmt->fetch();
				$ownerStmt->close();

				if ((int)$ownerAdminID !== (int)$adminID) {
					throw new Exception("Not authorized to create events for this RSO!");
				}

				// Insert RSO Event
				$rsoStmt = $conn->prepare("INSERT INTO RSO_Events_Owns (Events_ID, RSOs_ID) VALUES (?, ?)");
				$rsoStmt->bind_param("ii", $eventID, $rsoID);
				$rsoStmt->execute();
				$rsoStmt->close();

				// Auto-approve RSO event
				$approve = $conn->prepare("UPDATE Events_At SET Approval_Status = 'approved' WHERE Events_ID = ?");
				$approve->bind_param("i", $eventID);
				$approve->execute();
				$approve->close();
			} else {
				throw new Exception("Invalid event type: $type");
			}

			// If we made it this far, commit the transaction
			$conn->commit();
			$conn->close();
			sendResultInfoAsJson(json_encode(["error" => "", "eventID" => $eventID]));

		} catch (Exception $e) {
			// Rollback everything if anything failed
			$conn->rollback();
			$conn->close();
			returnWithError("Event creation failed: " . $e->getMessage(), 500);
		}
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