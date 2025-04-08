<?php

	$inData = getRequestInfo();

	$adminID = $inData["Admins_ID"];

	$conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
        $rsos = [];

        // get a user's rso names
		$get = $conn->prepare("
            SELECT RSO_name,RSOs_ID,Status
            FROM RSOs_Creates
            WHERE Admins_ID = ?
        ");
        $get->bind_param("i", $adminID);
        $get->execute();
        $result = $get->get_result();


        while ($row = $result->fetch_assoc()) {
            $rsos[] = $row;
        }

        if (count($rsos) === 0)
        {
            returnWithError("RSOs not found!");
        }
        else{
            returnWithInfo($rsos);
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