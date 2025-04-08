<?php
    $inData = getRequestInfo();

    $uid = $inData["UID"];
    $rsoID = $inData["RSOs_ID"];
    $approval = 'pending';

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error, 404);
	}
	else
	{
        // get student email from UID
        $domainCheck = $conn->prepare("SELECT Email FROM Users WHERE UID = ?");
        $domainCheck->bind_param("i", $uid);
        $domainCheck->execute();
        $domainCheck->bind_result($email);
        $domainCheck->fetch();
        $domainCheck->close();

        $domain = substr(strrchr($email, "@"), 1);

        // get email domain from RSOs_ID
        $getDomain = $conn->prepare("SELECT Email_domain FROM RSOs_Creates WHERE RSOs_ID = ?");
        $getDomain->bind_param("i", $rsoID);
        $getDomain->execute();
        $getDomain->bind_result($found);
        $getDomain->fetch();
        $getDomain->close();

        // compare user domain to RSO domain
        if ( $domain !== $found )
        {
            $conn->close();
            error_log("User domain: " . $domain);
            error_log("RSO domain: " . $found);
            returnWithError("Must be part of the same university to join!");
            return;
        }

        // check if user has already joined
        $check = $conn->prepare("SELECT * FROM Joins WHERE UID = ? AND RSOs_ID = ?");
        $check->bind_param("ii", $uid, $rsoID);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $check->close();
            $conn->close();
            returnWithError("You have already requested to join this RSO!");
            return;
        }
        $check->close();

        // insert into Joins
        $stmt = $conn->prepare("INSERT into Joins (UID, RSOs_ID, Approval_Status) VALUES (?,?,?)");
        $stmt->bind_param("iis", $uid, $rsoID, $approval);
        $stmt->execute();
        $stmt->close();

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