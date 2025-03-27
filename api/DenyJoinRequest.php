<?php

    $inData = getRequestInfo();

	$adminID = $inData["Admins_ID"];
    $uid = $inData["UID"];
    $rsoID = $inData["RSOs_ID"];
    $deny = 'denied';

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error, 404);
	}
    else
    {
		// check that admin is the admin of this RSO
		$check = $conn->prepare("SELECT Admins_ID FROM RSOs_Creates WHERE RSOs_ID = ?");
		$check->bind_param("i", $rsoID);
		$check->execute();
		$check->bind_result($foundAdmin);
		$check->fetch();
		$check->close();

		if( $adminID !== $foundAdmin )
		{
			$conn->close();
			returnWithError("You are not the admin of this RSO!");
			return;
		}

		// change status
        $update = $conn->prepare("UPDATE Joins SET Approval_Status = ? WHERE UID = ? AND RSOs_ID = ?");
        $update->bind_param("sii", $deny, $uid, $rsoID);
        $update->execute();
        $update->close();

        $conn->close();
		returnWithError("");
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
	
	function returnWithError( $err, $statusCode = 200)
	{
		http_response_code($statusCode);
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}

?>