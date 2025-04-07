<?php

	$conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
        $events = [];

        // get all public events
        $getPublic = $conn->prepare("
            SELECT E.Events_ID, E.Event_name, E.Date, E.Event_time, E.Description, E.Approval_Status, L.Lname
            FROM Events_At E
            JOIN Public_Events_Creates P ON E.Events_ID = P.Events_ID
            JOIN Locations L ON E.LocID = L.LocID
        ");
        $getPublic->execute();
        $allPublic = $getPublic->get_result();

        while ($row = $allPublic->fetch_assoc()) {
            $events[] = $row;
        }

        $getPublic->close();
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