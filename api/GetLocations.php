<?php

	$conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
        $locations = [];

		// get public and private events where event location matches the university a user belongs to
		$getLoc = $conn->prepare("SELECT Lname FROM Locations");
        $getLoc->execute();
        $allLocs = $getLoc->get_result();

        while ($row = $allLocs->fetch_assoc()) {
            $locations[] = $row["Lname"];
        }

        $getLoc->close();
		$conn->close();
        returnWithInfo($locations);
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