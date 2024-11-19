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
    case 'checkIfUser':
        echo checkIfUser($_POST['username'], $_POST['password']);
        break;
    case 'pushUser':
        pushUser($_POST['prompt'], $_POST['category'], $_POST['use_case']);
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
    return "pushed successfully";
}

function checkIfUser($username, $password) {
    global $pdo;
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if ($username == "") {
            echo 'fill in a username';
        } elseif ($password == "") {
            echo "fill in your password";
        } else {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
            $stmt->execute([':username' => $username]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false) {
                var_dump($row);
                if (password_verify($password, $row['password'])) {
                    $_SESSION['userid'] = $row['id'];
                    return "Logged in";
                    exit;
                }
            } else {
                echo "Invalid username or password";
            }
        }
    }
    return "Logged in";
}

function pushUser($username, $password, $verifypassword) {
    global $pdo;
    if ($username !== null && $password !== null && $verifypassword !== null) {
        if ($password == $verifypassword) {
            $password = $password;
            $hashpassword = password_hash($password, PASSWORD_DEFAULT);
            $statement = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $statement->bindParam(':username', $username);
            $statement->bindParam(':password', $hashpassword);
            $statement->execute();
            return "pushed successfully";
            exit();
        } else {
            echo "password doesn't match";
        }
    } else {
        echo "warning: fill all in";
    }
}

echo json_encode($prompts);