<?php

// API Key: AIzaSyAe-WqswCSTZYV7PWeEEhc_4Kd3tSfJniI

$inData = getRequestInfo();

$lname = $inData["Lname"];
$api = "AIzaSyAe-WqswCSTZYV7PWeEEhc_4Kd3tSfJniI";

$search = urlencode($lname);
$url = "https://maps.googleapis.com/maps/api/geocode/json?address=$search&key=$api";

$response = file_get_contents($url);
$data = json_decode($response, true);

if ($data['status'] === 'OK')
{
    $address = $data['results'][0]['formatted_address'];
    $latitude = $data['results'][0]['geometry']['location']['lat'];
    $longitude = $data['results'][0]['geometry']['location']['lng'];

    $conn = new mysqli("localhost", "campusbuzz", "campus4Buzz", "CampusBuzz");

    if ($conn->connect_error)
    {
        returnWithError( $conn->connect_error );
    }
    else
    {
        $stmt = $conn->prepare("INSERT INTO Locations (Lname, Address, Longitude, Latitude) VALUES (?,?,?,?)");
        $stmt->bind_param("ssdd", $lname, $address, $longitude, $latitude);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        returnWithError("");
    }

}
else
{
    returnWithError("Geocoding API failed!");
}

function getRequestInfo()
{
    return json_decode(file_get_contents('php://input'), true);
}

function sendResultInfoAsJson($obj)
{
    header('Content-type: application/json');
    echo $obj;
}

function returnWithError($err, $code = 200)
{
    http_response_code($code);
    $retValue = '{"error":"' . $err . '"}';
    sendResultInfoAsJson($retValue);
}


?>