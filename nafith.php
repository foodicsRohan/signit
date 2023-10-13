<?php


// CONSTANTS:
include 'NafithVariable.php';

// CODE BASE
/*
 * function to simplify printing debug message to screen
 */
function debugToScreen(string $message): void
{
    if (CODE_DEBUG_FLAG) {
        print "DEBUG MESSAGE: $message\r\n";
    }

}

/*
 * function that gets that call the api to obtain access point.
 */
function getAccessToken(): string
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
        CURLOPT_URL => $NafithTokenURL,
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
            'Authorization:Basic '. $Authorization,
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
   CREATE A SIGNATOR FOR CREATE SANAD
 */
   function calculateHmacSignature($data = null,
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
    debugToScreen("HMAC Signature : $calculated_signature\n");
     return $calculated_signature;
}


/*
  Get The Nafith Body From Apex CLASS CALL 
*/
  function getFileContent(): string
  {
     $rawPayload = file_get_contents('php://input');

        // Decode the input JSON
        $inputData = json_decode($rawPayload, true);

        // Get the nested JSON from the "data" field and decode it
        $nestedJson = json_decode($inputData['data'], true);

        // Merge the nested JSON back into the main structure
        $inputData['data'] = $nestedJson;

        // Re-encode the entire structure (if necessary)
        $outputJson = json_encode($inputData['data']);
        // Print the corrected JSON
        
       //  debugToScreen("FileData  is: $outputJson");

        
          return $outputJson;
 }

/*
 Function TO CREATE A SANAD IN NAFITH 
*/
function createSanad(string $filedata, string $accessToken , string $sigantor): string
{
    // Generate a timestamp
    $timestamp = time() * 1000; // Convert the current UNIX timestamp to milliseconds
     // debugToScreen("filedata : $filedata");
  $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $CreateSanadURL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $filedata,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'X-Nafith-Timestamp:'.$timestamp,
            'X-Nafith-Tracking-Id: 145',
            'X-Nafith-Signature:' . $sigantor,
            'Authorization: Bearer ' . $accessToken
        ),
        CURLOPT_SSL_VERIFYPEER => null, // Disable SSL certificate verification
    ));

    $response = curl_exec($curl);
   // debugToScreen("Create Sanad : $response");
      $responseDecoded = json_decode($response);
      $SanadID =  $responseDecoded->id;
      debugToScreen("SanadID  we got back is:  $SanadID");
    return $response; // Return the response
}

/*
 * main function, takes nothing and print out the document id returned by the api
 * it first get the access token and then upload a document
 */
function main(): void
{
    $accessToken = getAccessToken();
    $bodyData = getFileContent();

    // PARAMETERS PASSED TO CREATE SIGNATOR
    $method = "POST";
    $endpoint = "/api/sanad-group/";
    $sanad_object = "";
    // $secret_key = $secret_key;
    $unix_timestamp =  time() * 1000;
    $MainContent = calculateHmacSignature($bodyData, $method, "nafith.sa", $endpoint, $sanad_object, $unix_timestamp, $secret_key);
    $documentId = createSanad($bodyData, $accessToken,$MainContent );
   // print "document id obtained is: " . $documentId; // Print the document ID
}

/*
 * call the main function
 */
main();

?>
