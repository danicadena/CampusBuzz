<?php

	$inData = getRequestInfo();
	
	$uid = 0;
	$firstName = "";
	$lastName = "";

	$conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$stmt = $conn->prepare("SELECT UID,First,Last,User_Type,Email FROM Users WHERE Username=? AND Password=?");
		$stmt->bind_param("ss", $inData["Username"], $inData["Password"]);
		$stmt->execute();
		$result = $stmt->get_result();

		if( $row = $result->fetch_assoc()  )
		{
			returnWithInfo( $row['First'], $row['Last'], $row['UID'], $row['User_Type'], $row['Email'] );
		}
		else
		{
			returnWithError("No Records Found");
		}

		$stmt->close();
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
		$retValue = '{"uid":0,"email":"","firstName":"","lastName":"","user_type":"","error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
	function returnWithInfo( $firstName, $lastName, $uid, $type, $email )
	{
		$retValue = '{"id":' . $uid . ',"user_type":"' . $type . '","email":"' . $email . '","firstName":"' . $firstName . '","lastName":"' . $lastName . '","error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
?>