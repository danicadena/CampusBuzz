<?php

	$inData = getRequestInfo();

	// user that is searching
	$eventsID = $inData["Events_ID"];

	$conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
        // get an event's information
		$get = $conn->prepare("SELECT LocID, Event_time, Date, Event_name, Description, Event_type FROM Events_At WHERE Events_ID = ?");
        $get->bind_param("i", $eventsID);
        $get->execute();
        $result = $get->get_result();

        while ($row = $result->fetch_assoc()) {
            returnWithInfo($row);
        }
        else
        {
            returnWithError("Event not found!");
        }

        $get->close();
		$conn->close();
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

    function returnWithError( $err )
	{
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
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