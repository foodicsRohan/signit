<?php


// CONSTANTS:
//include 'NafithVariable.php';

$Authorization = "MnlkZnJjWVBNOVNyWW9lZU5pcWR5ZDNkVDJkdzdmeTVyWFJnMVhXajpPakRiVXFNSEVlcDdQUUVvVnRpTmJhUWpnYnc2MlUxTG5TU2c4cnBkRW1CZlRHQTFGSW9KNTNQY3dKMDVQWTllZW5aMTNHTExXZ2p4emg4eW9TQlo0aVViSTZSenI0WUI5QnR2amltVUF3eDE5M3lNU3RnS3ZFMHlhUjQyOWxldQ==";
$secret_key = "rwGoV7s8dBY1J7zZjcJAjWOazEVs0dIsEw7KUHEkiBAwUcTnmQV8pgXkMCrXdIVNQLguTNTwQPW4aj81rM42TIJcY3UC22iOhuTDq1ZxsYa0IggWpu2qjf1LwhexDqrs";
$NafithTokenURL = 'https://sandbox.nafith.sa/api/oauth/token/';
$CreateSanadURL = 'https://sandbox.nafith.sa/api/sanad-group/';


// CODE BASE
/*
 * function to simplify printing debug message to screen
 */
function debugToScreen(string $message): void
{
        print "DEBUG MESSAGE: $message\r\n";

}

/*
 * function that gets that call the api to obtain access point.
 */
function getAccessToken(string $NafithTokenURL1,string $Authorization1): string
{
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
        CURLOPT_URL => $NafithTokenURL1,
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
            'Authorization:Basic '.$Authorization1,
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
    return $accessToken;
}

/*
  Get The Nafith Body From Apex CLASS CALL 
*/
  function getFileContent(): string
  {
          $rawPayload = file_get_contents('php://input');
          // Decode the input JSON
          $inputData = json_decode($rawPayload, true);
          $inputData2 = $inputData['data'];
     //    debugToScreen("inputData : $inputData2\n");
          return $inputData2;
 }
 getFileContent();
 
 /*
   CREATE A SIGNATOR FOR GET  SANAD
 */
 
   function calculateHmacSignatureGet($data = null,
                                $method = null,
                                $connection_url = "nafith.sa",
                                $targeted_endpoint = null,
                                $targeted_object = "",
                                $unix_timestamp = null,
                                $secret_key = null
): string
 {
    $encoded_data = base64_encode($data);

    $message = "$method\n$connection_url\n$targeted_endpoint\nid=$targeted_object&t=$unix_timestamp&ed=$encoded_data";

    $signature = hash_hmac('sha256', $message, $secret_key, true);
    $calculated_signature = base64_encode($signature);
  //  debugToScreen("HMAC Signature : $calculated_signature\n");
     return $calculated_signature;
}




/*
 Function TO GET A SANAD FROM NAFITH 
*/

function getSanad(string $filedata, string $accessToken , string $sigantor ,string $SanadID): string
{

     $bodyData= "{}";
     $method = "GET";
     $endpoint = "/api/sanad/by-number/";
     $sanad_object =getFileContent();
     $secret_key1 = "rwGoV7s8dBY1J7zZjcJAjWOazEVs0dIsEw7KUHEkiBAwUcTnmQV8pgXkMCrXdIVNQLguTNTwQPW4aj81rM42TIJcY3UC22iOhuTDq1ZxsYa0IggWpu2qjf1LwhexDqrs";
     $unix_timestamp =  time() * 1000;
     $MainContent = calculateHmacSignatureGet($bodyData, $method, "nafith.sa", $endpoint, $sanad_object, $unix_timestamp, $secret_key1);
     $SanadNumber = getFileContent();
   // Generate a timestamp
    $timestamp = time() * 1000; // Convert the current UNIX timestamp to milliseconds
     $filedata2 = "{}";
     // debugToScreen("filedata : $filedata");
    $curl = curl_init();
   // debugToScreen("Get Sanad URL    we got back is:   $SanadID");
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://sandbox.nafith.sa/api/sanad/by-number/".$SanadNumber,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_POSTFIELDS => $filedata2,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'X-Nafith-Timestamp:'.$timestamp,
            'X-Nafith-Tracking-Id: 145',
            'X-Nafith-Signature:' .  $MainContent,
            'Authorization: Bearer ' . $accessToken
        ),
        CURLOPT_SSL_VERIFYPEER => null, // Disable SSL certificate verification
    ));

      $response = curl_exec($curl);
     // debugToScreen("Create Sanad : $response");
      $responseDecoded = json_decode($response);
      $SanadStatus =  $responseDecoded->status;
      debugToScreen("Get Sanad Status   we got back is:   $SanadStatus ");

       return $response; // Return the response
    }
    $accessToken = getAccessToken($NafithTokenURL,$Authorization);
    $MainContent = '';
    $CreateSanadURL = '';
    getSanad("{}",$accessToken,$MainContent ,$CreateSanadURL);
?>
