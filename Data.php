<?php

include("connect.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch($_POST['function']) {
        case 'logInCheck':
            echo logInCheck();
            break;
        case 'getPrompts':
            echo getPrompts();
            break;
        case 'pushPrompt':
            echo pushPrompt($_POST['prompt'], $_POST['category'], $_POST['usecase']);
            break;
        case 'checkIfUser':
            echo checkIfUser($_POST['username'], $_POST['password']);
            break;
        case 'pushUser':
            echo pushUser($_POST['username'], $_POST['password'], $_POST['verifypassword']);
            break;
        default:
            echo "Function not found";
            break;
    }
}

function logInCheck() {
    if ($_SESSION['userid'] == null) {
        return "Log in first";
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
    if($prompt !== null && $category !== null && $use_case !== null && $prompt !== "" && $category !== "" && $use_case !== "") {
        $statement = $pdo->prepare("INSERT INTO promptbook (prompt, userid, category, use_case) VALUES (:prompt, :userid, :category, :use_case)");
        $statement->bindParam(':prompt', $prompt);
        $statement->bindParam(':userid', $_SESSION['userid']);
        $statement->bindParam(':category', $category);
        $statement->bindParam(':use_case', $use_case);
        $statement->execute();
        return "pushed successfully";
    } else {
        return "Something not filled in";
    }
}

function checkIfUser($username, $password) {
    global $pdo;
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if ($username == "") {
            return 'fill in a username';
        } elseif ($password == "") {
            return "fill in your password";
        } else {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
            $stmt->execute([':username' => $username]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false) {
                if (password_verify($password, $row['password'])) {
                    $_SESSION['userid'] = $row['id'];
                    return "Logged in";
                } else {
                    return "Invalid password";
                }
            } else {
                return "Invalid username";
            }
        }
    }
}

function pushUser($username, $password, $verifypassword) {
    global $pdo;
    if ($username !== null && $password !== null && $verifypassword !== null) {
        if ($password == $verifypassword) {
            $hashpassword = password_hash($password, PASSWORD_DEFAULT);
            $statement = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $statement->bindParam(':username', $username);
            $statement->bindParam(':password', $hashpassword);
            $statement->execute();
            return "pushed successfully";
            exit();
        } else {
            return "password doesn't match";
        }
    } else {
        return "warning: fill all in";
    }
}

