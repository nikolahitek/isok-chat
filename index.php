<?php
    session_start();
    $username = '';
    $email = '';
    $isLoggedIn = False;

    if (isset($_COOKIE['sid'])) {
        if ($_SESSION[$_COOKIE['sid']]['last_login'] > (time() - 7200)) {
            $isLoggedIn = True;
        } else {
            $isLoggedIn = False;
            $username = $_SESSION[$_COOKIE['sid']]['username'];
            $email = $_SESSION[$_COOKIE['sid']]['email'];
        }
    } else if($_POST) {
        if (isset($_POST['username']) and isset($_POST['email'])) {
            $sid = uniqid();
            setcookie('sid', $sid);
            $_SESSION[$sid] = [
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'last_login' => time()
            ];
            $isLoggedIn = True;
        }
    }
?>
<html>
<head>
    <title>Index</title>
</head>
<body>
    <?php if (!$isLoggedIn): ?>
    <form action="index.php" method="post">
        <label>
            Username:
            <input type="text" name="username" value="<?= $username ?>"/>
        </label>
        <label>
            Email:
            <input type="email" name="email" value="<?= $email ?>"/>
        </label>
        <button type="submit">Submit</button>
    </form>
    <?php else: require 'channels.php';?>
    <?php endif; ?>
</body>
</html>
