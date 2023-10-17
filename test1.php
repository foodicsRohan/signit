<?php


function getAccessToken(): string
{
    echo "in function";
    $ch = curl_init();
    // Define the request parameters
    $requestParams = array(
        'grant_type' => 'client_credentials',
        'scope' => 'read write'
    );

    // Build the request body
    $requestBody = http_build_query($requestParams);
    $contentLength = strlen($requestBody);

   // Set cURL options
    curl_setopt_array($ch, array(
        CURLOPT_URL => "https://sandbox.nafith.sa/api/oauth/token/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $requestBody,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization:Basic MnlkZnJjWVBNOVNyWW9lZU5pcWR5ZDNkVDJkdzdmeTVyWFJnMVhXajpPakRiVXFNSEVlcDdQUUVvVnRpTmJhUWpnYnc2MlUxTG5TU2c4cnBkRW1CZlRHQTFGSW9KNTNQY3dKMDVQWTllZW5aMTNHTExXZ2p4emg4eW9TQlo0aVViSTZSenI0WUI5QnR2amltVUF3eDE5M3lNU3RnS3ZFMHlhUjQyOWxldQ==',
            'Host: sandbox.nafith.sa',
            'Content-Length: ' . $contentLength,
            'X-Nafith-Signature: T7SQpC+0HVUjkqjQFyHSw4iHqcEtWP3yyDqcIw/PziE=',
        ),
        CURLOPT_SSL_VERIFYPEER => null, // Disable SSL certificate verification
      ));
   // Execute the cURL request and capture the response
    $response = curl_exec($ch);
    //debugToScreen("response we got back is: $response");
    $responseDecoded = json_decode($response);
    $accessToken = $responseDecoded->access_token;
   // debugToScreen("access token returned is: $accessToken");
    echo  $accessToken ;
    return $accessToken;
}
echo "Hello";
getAccessToken();

?>
