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

        // get a user's university
        $uniName = $conn->prepare("SELECT University_name FROM Users WHERE UID=?");
        $uniName->bind_param("i", $uid);
        $uniName->execute();
        $uniName->bind_result($userUni);
        $uniName->fetch();
        $uniName->close();

		// get public and private events where event location matches the university a user belongs to
		$filterEvent = $conn->prepare("
            SELECT * FROM Events_At E
            JOIN Locations L ON E.LocID = L.LocID
            JOIN Users U ON L.Lname = U.University_name
            WHERE U.UID = ? AND (E.Event_type = 'Public' OR E.Event_type = 'Private')
        ");
        $filterEvent->bind_param("i", $uid);
        $filterEvent->execute();
        $result1 = $filterEvent->get_result();

        while ($row = $result1->fetch_assoc()) {
            $events[] = $row;
        }
        $filterEvent->close();

        // get rso events where 
        $filterRSOs = $conn->prepare("
            SELECT * FROM Events_At E
            JOIN RSOs_Events_Owns R ON E.Events_ID = R.Events_ID
            JOIN Joins J ON R.RSOs_ID = J.RSOs_ID
            WHERE J.UID = ? AND J.Approval_Status = 'approved'
        ");
        $filterRSOs->bind_param("i", $uid);
        $filterRSOs->execute();
        $result2 = $filterRSOs->get_result();

		while ($row = $result2->fetch_assoc()) {
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