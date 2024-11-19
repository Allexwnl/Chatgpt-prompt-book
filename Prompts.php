<?php

include("connect.php");

$statement = $pdo->prepare("SELECT * FROM promptbook");
$statement->execute();
$prompts = $statement->fetchAll(PDO::FETCH_ASSOC);

function getPrompt() {
    global $prompts;
    return json_encode($prompts);
}

function pushPrompt($prompt, $user, $category, $use_case) {
    global $pdo;
    $statement = $pdo->prepare("INSERT INTO promptbook (prompt, user, category, use_case) VALUES (:prompt, :user, :category, :use_case)");
    $statement->bindParam(':prompt', $prompt);
    $statement->bindParam(':user', $user);
    $statement->bindParam(':category', $category);
    $statement->bindParam(':use_case', $use_case);
    $statement->execute();
}

echo json_encode($prompts);