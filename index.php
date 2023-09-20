<?php

// $path = $_POST['file'];


$get_data = file_get_contents('php://input');
file_put_contents("pdf_data.txt",$get_data);

$data = file_get_contents("pdf_data.txt");
// echo "<pre>";print_r($data);


$totalPages = counts($data);
echo $totalPages;

function counts($data)
{
  $pdf = base64_decode($data);
  // echo $pdf;
  $number = preg_match_all("/\/Page\W/", $pdf, $dummy);
  return $number;
}

?>
