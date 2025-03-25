<?php
    $inData = getRequestInfo();

    $uniName = $inData["Uni_name"];
    $studentCount = $inData["Student_num"];
    $profilePic = isset($inData["Profile_pic"]) ? $inData["Profile_pic"] : null;
    $superAdminID = $inData["SuperAdmins_ID"];

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error, 404);
	}
	else
	{
        // check if LocID exists in Locations
        $locStmt = $conn->prepare("SELECT LocID FROM Locations WHERE Lname = ?");
        $locStmt->bindparam("s", $uniName);
        $locStmt->execute();
        $locStmt->store_result();

        if($locStmt->num_rows == 0)
        {
            $locStmt->close();
            $conn->close();
            returnWithError("Location not found!", 404);
            return;
        }

        // if match found, get the LocID
        $locStmt->bind_result($locID);
        $locStmt->fetch();
        $locStmt->close();

		$stmt = $conn->prepare("INSERT into University (Uni_name, Student_num, Profile_pic, SuperAdmins_ID, LocID) VALUES(?,?,?,?,?)");
		$stmt->bind_param("sisii", $uniName, $studentCount, $profilePic, $superAdminID, $locID);
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