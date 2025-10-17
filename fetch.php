<?php
error_reporting(0);
header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$month = $_POST['month'] ?? '07';

if(!$id) {
    echo json_encode(['rows'=>[]]);
    exit;
}

$url = "https://scm.up.gov.in/Food/EposAutomation/EPOSRC_Search.aspx/BindTransactionSearchDetails";
$data = [
    "ID" => $id,
    "Flag" => "CD",
    "District" => 164,
    "Month" => $month,
    "Year" => "2025-26",
    "Cycle" => "1",
    "Area" => "U",
    "UserId" => "792334"
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json; charset=UTF-8",
        "Accept: application/json, text/javascript, */*; q=0.01"
    ],
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

$gehu_rows = [];

if(!$error){
    $json = json_decode($response,true);
    $inner = json_decode($json['d'] ?? '[]',true);
    $gehu_rows = array_filter($inner, function($row){
        return isset($row['Commodity']) && trim($row['Commodity']) === 'गेहूँ';
    });
}

if(empty($gehu_rows)){
    $gehu_rows[] = [
        'RC_ID' => $id,
        'MnthName' => '',
        'yr' => '',
        'member_name_ll' => '',
        'cardtype' => '',
        'quantity_lifted' => '',
        'PortabiliytFPS' => '',
        'Mode' => '',
        'DT' => ''
    ];
}

echo json_encode(['rows'=>array_values($gehu_rows)]);
