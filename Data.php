<?php

include("connect.php");
session_start();

switch($_POST['function']) {
    case 'logInCheck':
        logInCheck();
        break;
    case 'getPrompts':
        echo getPrompts();
        break;
    case 'pushPrompt':
        pushPrompt($_POST['prompt'], $_POST['category'], $_POST['use_case']);
        break;
}

function logInCheck() {
    if ($_SESSION['userid'] == null) {
        header("Location: login_register.php");
        exit;
    }
}

function getPrompts() {
    global $pdo;
    $statement = $pdo->prepare("SELECT * FROM promptbook");
    $statement->execute();
    $prompts = $statement->fetchAll(PDO::FETCH_ASSOC);
    return json_encode($prompts);
}

function pushPrompt($prompt, $category, $use_case) {
    global $pdo;
    $statement = $pdo->prepare("INSERT INTO promptbook (prompt, user, category, use_case) VALUES (:prompt, :user, :category, :use_case)");
    $statement->bindParam(':prompt', $prompt);
    $statement->bindParam(':user', $_SESSION['userid']);
    $statement->bindParam(':category', $category);
    $statement->bindParam(':use_case', $use_case);
    $statement->execute();
}

echo json_encode($prompts);