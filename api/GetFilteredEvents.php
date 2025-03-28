<?php

	$inData = getRequestInfo();

	// user that is searching
	$uid = $inData["UID"];

	$conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
        $events = [];

        // get public events
        $filterPublic = $conn->prepare("
            SELECT E.Events_ID, E.LocID, E.Event_name, E.Date, E.Event_time, E.Description, E.Event_type, E.Approval_Status FROM Events_At E
            WHERE E.Event_type = 'Public'
        ");
        $filterPublic->execite();
        $result1 = $filterPublic->get_result();

        while ($row = $result1->fetch_assoc()) {
            $events[] = $row;
        }
        $filterPublic->close();

		// get private events where event location matches the university a user belongs to
		$filterPrivate = $conn->prepare("
            SELECT E.Events_ID, E.LocID, E.Event_name, E.Date, E.Event_time, E.Description, E.Event_type, E.Approval_Status FROM Events_At E
            JOIN Locations L ON E.LocID = L.LocID
            JOIN Users U ON L.Lname = U.University_name
            WHERE U.UID = ? AND E.Event_type = 'Private'
        ");
        $filterPrivate->bind_param("i", $uid);
        $filterPrivate->execute();
        $result2 = $filterPrivate->get_result();

        while ($row = $result2->fetch_assoc()) {
            $events[] = $row;
        }
        $filterPrivate->close();

        // get rso events where user is a member of
        $filterRSOs = $conn->prepare("
            SELECT E.Events_ID, E.LocID, E.Event_name, E.Date, E.Event_time, E.Description, E.Event_type, E.Approval_Status FROM Events_At E
            JOIN RSO_Events_Owns R ON E.Events_ID = R.Events_ID
            JOIN Joins J ON R.RSOs_ID = J.RSOs_ID
            WHERE J.UID = ? AND J.Approval_Status = 'approved'
        ");
        $filterRSOs->bind_param("i", $uid);
        $filterRSOs->execute();
        $result3 = $filterRSOs->get_result();

		while ($row = $result3->fetch_assoc()) {
            $events[] = $row;
        }
        $filterRSOs->close();
		
		$conn->close();
        returnWithInfo($events);
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
	
	function returnWithInfo($results)
    {
        $retValue = json_encode([
            "results" => $results,
            "error" => ""
        ]);

        sendResultInfoAsJson($retValue);
    }
	
?>