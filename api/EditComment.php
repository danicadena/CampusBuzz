<?php
    $inData = getRequestInfo();

    $uid = $inData["UID"];
    $eventID = $inData["Events_ID"];

    $rating = $inData["Rating"];
    $text = $inData["Text"];
    $timestamp = date("Y-m-d H:i:s");

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
        $stmt = $conn->prepare("UPDATE Comments SET Rating=?, Text=?, Timestamp=? WHERE UID=? AND Events_ID=?");
        $stmt->bind_param("issiI", $rating, $text, $timestamp, $uid, $eventID);
        $stmt->execute();
        $stmt->close();

        $conn->close();
        returnWithInfo($uid, $eventID, $rating, $text, $timestamp);
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

    function returnWithInfo( $uid, $eventID, $rating, $text, $timestamp )
	{
		$retValue = '{"UID":' . $uid . ',"Events_ID":"' . $eventID . '","Rating":"' . $rating . '","Text":"' . $text . '","Timestamp":"' . $timestamp . '","error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
	function returnWithError( $err )
	{
        $retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}


?>