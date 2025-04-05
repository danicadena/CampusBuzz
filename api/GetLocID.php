<?php

	$inData = getRequestInfo();

	$name = $inData["Lname"];

	$conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
        // get a locations id
		$get = $conn->prepare("SELECT LocID FROM Locations WHERE Lname = ?");
        $get->bind_param("s", $name);
        $get->execute();
        $result = $get->get_result();

        if ($row = $result->fetch_assoc()) {
            returnWithInfo($row['LocID']);
        }
        else
        {
            returnWithError("Location not found!");
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
	
	function returnWithInfo( $id )
	{
		$retValue = '{"LocID":' . $id . ',"error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
?>