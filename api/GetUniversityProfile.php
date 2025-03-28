<?php

	$inData = getRequestInfo();

	$uniID = $inData["Uni_ID"];

	$conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
        // get a university's profile
		$get = $conn->prepare("SELECT LocID, Uni_name, Student_num, Profile_pic FROM University WHERE Uni_ID = ?");
        $get->bind_param("i", $uniID);
        $get->execute();
        $result = $get->get_result();

        if ($row = $result->fetch_assoc()) {
            returnWithInfo($row);
        }
        else
        {
            returnWithError("University not found!");
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