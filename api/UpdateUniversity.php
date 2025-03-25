<?php
    $inData = getRequestInfo();

    $uniID = $inData["Uni_ID"];
    $studentCount = $inData["Student_num"];
    $profilePic = isset($inData["Profile_pic"]) ? $inData["Profile_pic"] : null;

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz"); 	
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
        $ret = $conn->prepare("SELECT Uni_ID FROM University WHERE Uni_ID=?");
        $ret->bind_param("i", $uniID);
        $ret->execute();
        $ret->store_result();

        if( $ret->num_rows > 0){
            $stmt = $conn->prepare("UPDATE University SET Student_num=?, Profile_pic=? WHERE Uni_ID=?");
    		$stmt->bind_param("isi", $studentCount, $profilePic, $uniID);
            $stmt->execute();
            $stmt->close();
            $conn->close();
            returnWithInfo($uniID, $studentCount, $profilePic);
        }
        else{
            $ret->close();
			$conn->close();
			returnWithError("No University found with this id!");
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

    function returnWithInfo( $uniID, $studentCount, $profilePic )
	{
		$retValue = '{"Uni_ID":' . $uniID . ',"Student_num":"' . $studentCount . '","Profile_pic":"' . $profilePic . '","error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
	function returnWithError( $err )
	{
        $retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}


?>