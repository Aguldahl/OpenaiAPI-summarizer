<?php
require_once 'config.php';

$promptFile = fopen('prompt.txt', 'r');
$prompt = fread($promptFile, filesize('prompt.txt'));

$data = array(
    'prompt' => $prompt . ' \n\nTl;dr',
    'model' => 'text-davinci-003',
    'temperature' => 0.7,
    'max_tokens' => 1000,
    'top_p' => 1.0,
    'frequency_penalty' => 0.0,
    'presence_penalty' => 1
);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/completions');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . OPENAI_API_KEY
));


$result = curl_exec($ch);
curl_close($ch);
$response = json_decode($result, true);
$responseTrimmed = trim($response['choices'][0]['text'], ': ');


if ($response && isset($response['choices'][0]['text'])) {
    echo $responseTrimmed;
} else {
    echo 'Error: genererte ikke text';
}



