<?php

require_once 'config.php';

$promptFile = fopen('prompt.txt', 'r');
$prompt = fread($promptFile, filesize('prompt.txt'));

class Choice {
    public $text;
    public $correct;

    public function __construct($text, $correct)
    {
        $this->text = $text;
        $this->correct = $correct;
    }


}

function generate_question($prompt) {
    $data = array(
        'prompt' => $prompt . ' Lag et spørsmål til denne teksten, som skal brukes i en multiple choice test. Inkluder ingen ekstra symboler, valg eller lignende.',
        'model' => 'text-davinci-003',
        'temperature' => 0.7,
        'max_tokens' => 1500,
        'top_p' => 1.0,
        'n' => 1,
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

    if ($response && isset($response['choices'][0]['text'])) {
        $noNewlineText = $response['choices'][0]['text'];
    } else {
        echo 'Error: genererte ikke text';
    }

    $htmlParagraph = nl2br($noNewlineText);
    return $response['choices'][0]['text'];
}

function generate_choices($prompt) {
    $question = generate_question($prompt);
    $options = [];
    $loopNumber = 1;

    for ($i = 0; $i < $loopNumber; $i++) {
        $data = array(
            'prompt' => 'Her er teksten: ' . $prompt . "Her er spørsmålet: " . $question . "Lag tre feile valg og ett riktig valg til spørsmålet basert på teksten. På slutten vil jeg at du lager et svar hvor du forteller svaret utifra de 4 valgene med en veldig kort forklaring på hvorfor. Bruk <br > som newline mellom hver linje. Alternativene skal være A) B) C) og D) ett av de skal være svaret. Svaret skriver du slik: svar: riktig bokstav, og så forklaringen.",
            'model' => 'text-davinci-003',
            'temperature' => 0.7,
            'max_tokens' => 1500,
            'top_p' => 1.0,
            'n' => 1,
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
        $fullText = $response['choices'][0]['text'];

        $textSplit = explode("<br>", $fullText);
        if (sizeof($textSplit) < 3) { // Hvis API-en ikke har levert en tekst delt inn etter <br> så requestes en ny tekst. Helt til de leverer det som er spurt etter.
            $loopNumber++;
        }
    }
    if ($response && isset($response['choices'][0]['text'])) {
        echo $question . "<br>";
        echo $response['choices'][0]['text'];
    } else {
        echo 'Error while generating choices';
    }



    $choiceA = new Choice(trim($textSplit[0]), false);
    $choiceB = new Choice(trim($textSplit[1]), false);
    $choiceC = new Choice(trim($textSplit[2]), false);
    $choiceD = new Choice(trim($textSplit[3]), false);
    array_push($options, $choiceA, $choiceB, $choiceC, $choiceD);

    if (str_contains($response['choices'][0]['text'], 'Svar: A')) {
        $choiceA->correct = true;
    } elseif (str_contains($response['choices'][0]['text'], 'Svar: B')) {
        $choiceB->correct = true;
    } elseif (str_contains($response['choices'][0]['text'], 'Svar: C')) {
        $choiceC->correct = true;
    } elseif (str_contains($response['choices'][0]['text'], 'Svar: D')) {
        $choiceD->correct = true;
    }

    $trimmedQuestion = trim($question);
    $jsonString = json_encode(['question' => $trimmedQuestion, 'options' => $options],JSON_PRETTY_PRINT);
    $jsonFile = fopen("generatedMultipleChoice", 'w');
    fwrite($jsonFile, $jsonString);
    fclose($jsonFile);

}

generate_choices($prompt);
