<?php

	$inData = getRequestInfo();
	
	$searchResults = "";
	$searchCount = 0;

	// user that is searching
	$uid = $inData["UID"];

	$conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		$stmt = $conn->prepare("SELECT * FROM Events_At WHERE Event_name like ?");
		$name = "%" . $inData["search"] . "%";
		$stmt->bind_param("s", $name);
		$stmt->execute();
		
		$result = $stmt->get_result();
		
		while($row = $result->fetch_assoc())
		{
			if( $searchCount > 0 )
			{
				$searchResults .= ",";
			}
			$searchCount++;
            $searchResults .= '{"Events_ID":"' . $row["Events_ID"] . '", "Event_name":"' . $row["Event_name"] . '", "Description":"' . $row["Description"] . '", "Date":"' . $row["Date"] . '", "Event_time":"' . $row["Event_time"] . '", "Event_type":"' . $row["Event_type"] . '", "Approval_Status":"' . $row["Approval_Status"] . '"}';
        }
		
		if( $searchCount == 0 )
		{
			returnWithError( "No Records Found" );
		}
		else
		{
			returnWithInfo( $searchResults );
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
		$retValue = '{"results":[],"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
	function returnWithInfo( $firstName )
	{
		$retValue = '{"results":[' . $searchResults . '],"error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
?>