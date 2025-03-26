<?php

	// UIDs: [1,5,6,7,8]
	// Student_promoted: 1
	// Admin_phone: "1234567890"
	// RSO_name: "Women in STEM"

    $inData = getRequestInfo();

	$uidList = $inData["UIDs"];
    $newAdmin = $inData["Student_promoted"];
	$phone = $inData["Admin_phone"];
	$rsoName = $inData["RSO_name"];

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error, 404);
	}
	else
	{
		$domains = [];

		// ensure all email domains are the same
		foreach($uidList as $uid)
		{
			$uidEmail = $conn->prepare("SELECT Email FROM Users WHERE UID = ?");
			$uidEmail->bind_param("i", $uid);
			$uidEmail->execute();
			$uidEmail->bind_result($email);
			$uidEmail->fetch();
			$uidEmail->close();

			$domain = strrchr($email, "@");
			$domains[] = $domain;
		}

		$first = $domains[0];
		$same = true;

		foreach($domains as $d)
		{
			if ($d != $first)
			{
				$same = false;
				break;
			}
		}

		if ($same == false)
		{
			returnWithError("All email domains must be the same!");
			return;
		}

		// promote student to Admin
		$addAdmin = $conn->prepare("INSERT into Admins (UID, Phone) VALUES (?, ?)");
		$addAdmin->bind_param("is", $newAdmin, $phone);
		$addAdmin->execute();
		$adminID = $conn->insert_id;
		$addAdmin->close();

		// create the RSO with new admin
		$stmt = $conn->prepare("INSERT into RSOs_Creates (Admins_ID, RSO_name) VALUES(?,?)");
		$stmt->bind_param("is", $adminID, $rsoName);
		$stmt->execute();
		$rsoID = $conn->insert_id;
		$stmt->close();

		// insert each person into Joins
		foreach($uidList as $uid)
		{
			$join = $conn->prepare("INSERT into Joins (UID, RSOs_ID) VALUES (?,?)");
			$join->bind_param("ii", $uid, $rsoID);
			$join->execute();
			$join->close();
		}

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