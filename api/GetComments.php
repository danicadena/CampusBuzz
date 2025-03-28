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
        $comments = [];

        // get list of comments under a certain event
		$getComment = $conn->prepare("SELECT UID, Rating, Text, Timestamp FROM Comments WHERE Events_ID = ?");
        $getComment->bind_param("i", $eventsID);
        $getComment->execute();
        $result = $getComment->get_result();

        while ($row = $result->fetch_assoc()) {
            $comments[] = $row;
        }
        $getComment->close();
		
		$conn->close();
        returnWithInfo($comments);
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