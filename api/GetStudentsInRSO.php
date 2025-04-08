<?php

	$inData = getRequestInfo();

	$rsoID = $inData["RSOs_ID"];

	$conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
        $students = [];

        // get an rso's list of students
		$get = $conn->prepare("
            SELECT U.First,U.Last,J.Approval_Status
            FROM Joins J
            JOIN Users U ON J.UID = U.UID
            WHERE J.RSOs_ID = ?
        ");
        $get->bind_param("i", $rsoID);
        $get->execute();
        $result = $get->get_result();


        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }

        if (count($students) === 0)
        {
            returnWithError("Students not found!");
        }
        else{
            returnWithInfo($students);
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