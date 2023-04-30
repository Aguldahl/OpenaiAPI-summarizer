<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="index.css">
    <title>ApiTester</title>
</head>
<body>
<div class="center">
    <h1>Oppgave til internship:</h1>
    <h2>Undervisnings artikkel:</h2>
    <div class="artikkel-text">
        <p> <?php $promptFile = fopen('prompt.txt', 'r');
            $prompt = fread($promptFile, filesize('prompt.txt'));
            echo $prompt;?>
        </p>
    </div>
    <h2>Sammendrag av undervisningsartikkelen: </h2>
    <div class="open-ai-result-container">
        <div class="open-ai-result-text">
            <p>
                <?php
                    include 'sammendrag.php';
                ?>
            </p>
        </div>
    </div>

</div>
<h1>Oppgave til artikkelen: </h1>
<div class="open-ai-result-container">
    <div class="artikkel-multiple-choice">
        <p> <?php include 'multiple_choice.php'?> </p>
    </div>
</div>

</body>
</html>
