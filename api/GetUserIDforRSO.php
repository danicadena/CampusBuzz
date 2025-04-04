<?php

	$inData = getRequestInfo();

	$emails = $inData["Emails"];

	$conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
        $uids = [];

        // get uids from Users
        foreach($email as $emails)
        {
            $getUID = $conn->prepare("SELECT UID FROM Users WHERE Email=?");
            $getUID->bind_param("s", $email);
            $getUID->execute();
            $getUID->bind_result($foundUID);

            if($getUID->fetch())
            {
                $uids[] = $foundUID;
            }
            else
            {
                returnWithError("Email not found!");
                return;
            }
            $getUID->close();
        }
		
		$conn->close();
        returnWithInfo($uids);
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
	
	function returnWithInfo($results)
    {
        $retValue = json_encode([
            "results" => $results,
            "error" => ""
        ]);

        sendResultInfoAsJson($retValue);
    }

    function returnWithError( $err, $statusCode = 200)
	{
		http_response_code($statusCode);
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
?>