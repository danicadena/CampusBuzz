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
		// check that admin exists
		$checkAdmin = $conn->prepare("SELECT Admins_ID FROM Admins WHERE Admins_ID = ?");
		$checkAdmin->bind_param("i", $adminID);
		$checkAdmin->execute();
		$checkAdmin->store_result();

		if ($checkAdmin->num_rows === 0) {
			$checkAdmin->close();
			$conn->close();
			returnWithError("Admin ID not found.");
			return;
		}
		$checkAdmin->close();

        // if admin exists, update event
		$stmt = $conn->prepare("UPDATE Events_At SET Event_time=?, Date=?, Event_name=?, Description=? WHERE Events_ID = ?");
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
