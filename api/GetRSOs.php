<?php

	$inData = getRequestInfo();

	$uid = $inData["UID"];
	$domain = $inData["Domain"];

	$conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
        $rsos = [];

        // get a universities's rso names
		$get = $conn->prepare("
			SELECT R.RSO_name, R.RSOs_ID, J.Approval_Status 
			FROM RSOs_Creates R
			LEFT JOIN Joins J ON R.RSOs_ID = J.RSOs_ID AND J.UID = ?
			WHERE R.Email_domain = ?
		");
        $get->bind_param("is", $uid, $domain);
        $get->execute();
        $result = $get->get_result();

        while ($row = $result->fetch_assoc()) {
            $rsos[] = [
				"name" => $row['RSO_name'],
				"id" => $row['RSOs_ID'],
				"status" => $row['Approval_Status'] ?? "none"
			];
        }

        $get->close();
		$conn->close();

        if (count($rsos) === 0)
        {
            returnWithError("No RSOs in university!");
        }
        else{
            returnWithInfo($rsos);
        }
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