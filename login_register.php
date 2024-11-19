<!-- login -->
<?php

if ($_GET["login_register"] == 'login') {
        try {
            if ($_SERVER['REQUEST_METHOD'] == "POST") {
                if ($_POST['gebruikersnaam'] == "") {
                    echo 'fill in a username';
                } elseif ($_POST['password'] == "") {
                    echo "fill in your password";
                } else {
                    $password = $_POST['password'];
                    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
                    $stmt->execute([':username' => $_POST['gebruikersnaam']]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($row !== false) {
                        var_dump($row);
                        if (password_verify($password, $row['password'])) {
                            $_SESSION['userid'] = $row['id'];
                            header("Location: index.php");
                            exit;
                        }
                    } else {
                        echo "Invalid username or password";
                    }
                }
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="loginregister.css">
            <title>login</title>
        </head>

        <body>
        <div class="container">
            <div class="login-forum">
                <form method="POST">
                    <h1>username</h1>
                    <input type="text" name="gebruikersnaam" id="gebruikersnaam">
                    <h1>password</h1>
                    <input type="password" name="password" id="password">
                    <div class="button-container">
                        <input type="submit" class="button" value="login" id="login">
                    </div>
                    <a href="register.php" class="knop"> don't have an account click here!</a>
                </form>
            </div>
        </div>
        </body>

        </html>
        <?php
            } else {
            ?>
        <!-- register -->

        <?php

        include_once("connect.php");

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            try {
                if ($_POST['gebruikersnaam'] !== null && $_POST['password'] !== null && $_POST['verifypassword'] !== null) {
                    if ($_POST['password'] == $_POST['verifypassword']) {
                        $password = $_POST['password'];
                        $hashpassword = password_hash($password, PASSWORD_DEFAULT);
                        $sql = "INSERT INTO users (username, password)
                        VALUES (:username, :password)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([
                            ":username" => $_POST['gebruikersnaam'],
                            ":password" => $hashpassword,
                        ]);
                        header('Location: login_register.php?login_register=login');
                        exit();
                    } else {
                        echo "password doesn't match";
                    }
                } else {
                    echo "warning: fill all in";
                }
            } catch (\Throwable $th) {
                if ($th instanceof \PDOException && $th->errorInfo[1] == 1062) {
                    echo "gebruikersnaam bestaat al";
                } else {
                    var_dump($th);
                }
            }
        }
 
        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Document</title>
            <link rel="stylesheet" href="loginregister.css">
        </head>

        <body>
            <div class="container">
            <div class="register-forum">
                <form method="post">
                    <h1>username</h1>
                    <input type="text" name="gebruikersnaam" id="gebruikersnaam">
                    <h1>password</h1>
                    <input type="password" name="password" id="password">
                    <h1>verify password</h1>
                    <input type="password" name="verifypassword" id="verifypassword">
                    <div>
                        <input type="submit" class="button" value="register user" name="submit" id="submit">
                    </div>
                    <a href="login_register.php?login_register=login">already have an account click here!</a>
                </form>
            </div>
            </div>
        </body>

</html>
<?php
    }
?>