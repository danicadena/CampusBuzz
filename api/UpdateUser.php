<?php
    $inData = getRequestInfo();

    $firstName = $inData["First"];
    $lastName = $inData["Last"];
    $password = $inData["Password"];
    $uid = $inData["UID"];

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
        $ret = $conn->prepare("SELECT First FROM Users WHERE UID=?");
        $ret->bind_param("i", $uid);
        $ret->execute();
        $ret->store_result();

        if( $ret->num_rows > 0){
            $stmt = $conn->prepare("UPDATE Users SET First=?, Last=?, Password=? WHERE UID=?");
    		$stmt->bind_param("sssi", $firstName, $lastName, $password, $uid);
            $stmt->execute();
            $stmt->close();
            $conn->close();
            returnWithError("");
        }
        else{
            $ret->close();
			$conn->close();
			returnWithError("No User found with this UID");
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

    function returnWithInfo( $firstName, $lastName, $password, $universityName, $uid )
	{
		$retValue = '{"uid":' . $uid . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","password":"' . $password . '","university":"' . $universityName . '","error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
	function returnWithError( $err )
	{
        $retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}


?>