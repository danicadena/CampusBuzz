<?php

	// UIDs: [1,5,6,7,8]
	// Student_promoted: 1
	// Admin_phone: "1234567890"
	// RSO_name: "Women in STEM"

    $inData = getRequestInfo();

	$uidList = $inData["UIDs"];
    $newAdmin = $inData["Student_promoted"];
	$rsoName = $inData["RSO_name"];

	// admins can create RSOs - already in database
	$phone = isset($inData["Admin_phone"]) ? $inData["Admin_phone"] = null;

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error, 404);
	}
	else
	{
		// ensure student promoted is inside UIDs
		foreach($uid as $uidList)
		{
			if($newAdmin == $uid)
			{
				$found = true;
				break;
			}
		}

		if(!$found)
		{
			returnWithError("Admin must be included in UIDs!");
			return;
		}

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

			$domain = substr(strrchr($email, "@"), 1);
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

		// check if student promoted is already an admin
		$check = $conn->prepare("SELECT UID from Admins WHERE UID = ?");
		$check->bind_param("i", $uidList[0]);
		$check->execute();
		$check->store_result();
		$adminFound = $check->num_rows > 0;
		$check->close();
		
		if($adminFound){
			continue;
		}
		else{
			// promote student to Admin
			$addAdmin = $conn->prepare("INSERT into Admins (UID, Phone) VALUES (?, ?)");
			$addAdmin->bind_param("is", $newAdmin, $phone);
			$addAdmin->execute();
			$adminID = $conn->insert_id;
			$addAdmin->close();

			// change student user_type to Admin
			$updateStudent = $conn->prepare("UPDATE Users SET User_Type = 'Admin' WHERE UID = ?");
			$updateStudent->bind_param("i", $newAdmin);
			$updateStudent->execute();
			$updateStudent->close();
		}

		// create the RSO with new admin
		$stmt = $conn->prepare("INSERT into RSOs_Creates (Admins_ID, RSO_name, Email_domain) VALUES(?,?,?)");
		$stmt->bind_param("iss", $adminID, $rsoName, $first);
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