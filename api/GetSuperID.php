<?php

	$inData = getRequestInfo();

	$uid = $inData["UID"];

	$conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
        // get a locations id
		$get = $conn->prepare("SELECT SuperAdmins_ID FROM SuperAdmins WHERE UID = ?");
        $get->bind_param("i", $uid);
        $get->execute();
        $result = $get->get_result();

        if ($row = $result->fetch_assoc()) {
            returnWithInfo($row['SuperAdmins_ID']);
        }
        else
        {
            returnWithError("Super Admin not found!");
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
		$retValue = '{"SuperAdmins_ID":' . $id . ',"error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
?>