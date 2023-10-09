<?php
    // Get the raw request data from php://input
    $get_data = file_get_contents('php://input');
    // Decode the JSON data
    $data = json_decode($get_data, true);
    // Check if the decoding was successful
    if ($data !== null) {
        // Print the entire decoded array
        $decodedData = $data['data'];
        // print_r($decodedData);
        $totalPages = counts($decodedData);
        echo $totalPages;
    } else {
        echo "No pages found";
    }

    function counts($data)
    {
        $pdf = base64_decode($data);
        $number = preg_match_all("/\/Page\W/", $pdf, $dummy);
        return $number;
    }
?>
