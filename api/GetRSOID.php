<?php

	$inData = getRequestInfo();

	$name = $inData["RSO_name"];

	$conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
        // get an rso's id from name
		$get = $conn->prepare("SELECT RSOs_ID FROM RSOs_Creates WHERE RSO_name = ?");
        $get->bind_param("s", $name);
        $get->execute();
        $result = $get->get_result();

        if ($row = $result->fetch_assoc()) {
            returnWithInfo($row['RSOs_ID']);
        }
        else{
            returnWithError("No RSO found!");
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