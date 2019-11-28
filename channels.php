<?php
    session_start();

    if (!isset($_COOKIE['sid']) or !$_SESSION[$_COOKIE['sid']]['last_login'] > (time() - 7200)) {
        header('location: index.php');
        exit();
    }

    require_once 'pdo_setup.php';

    $channels = [];
    $messages = [];
    $username = '';
    $info = '';

    $id = null;

    $sql = 'select channels.*, count(case messages.is_read when 0 then 1 else null end) as num_unread 
            from channels left join messages on channels.id = messages.channel group by channels.id order by num_unread desc';

    if ($s = $pdo->prepare($sql)) {
        if ($s->execute() and $s->rowCount() > 0) {
            $channels = $s->fetchAll();
        }
    }

    if($_GET) {
        if (isset($_GET['channel-id'])) {

            if (isset($_COOKIE['username'])) {
                $username = $_COOKIE['username'];
            }

            $id = $_GET['channel-id'];
            $sql = 'select * from messages where channel = ? order by time';
            if ($s = $pdo->prepare($sql)) {
                if ($s->execute([$id]) and $s->rowCount() > 0) {
                    $messages = $s->fetchAll();
                    foreach ($messages as $message) {
                        if ($message['email'] != $_SESSION[$_COOKIE['sid']]['email']) {
                            if ($s = $pdo->prepare('update messages set is_read = 1 where id = ?')) {
                                $s->execute([$message['id']]);
                            }
                        }
                    }
                }
            }
        }
    }

    if($_POST) {
        if (isset($_POST['username']) and isset($_POST['text'])) {
            $name = $_POST['username'];
            $text = $_POST['text'];
            $email = $_SESSION[$_COOKIE['sid']]['email'];
            setcookie('username', $name);

            if ($s = $pdo->prepare('insert into messages values (?, ?, ?, ?, ?, ?, ?);')) {
                if ($s->execute([52, $text, $username, $email, $id, '2019-11-01 00:00:00', 0])) {
                    header("Refresh:0");
                } else {
                    $info = 'Not really ok';
                }
            } else {
                $info = 'Not not ok';
            }
        }
    }

    unset($pdo);
 ?>
<html>
<head>
    <title>Channels</title>
</head>
<body>
    <?php if ($id == null):?>
    <div>
        <h1>Channels:</h1>
        <?php foreach ($channels as $c): ?>
            <p>
                <span><?= $c['id']?></span>
                <a href="channels.php?channel-id=<?= $c['id'] ?>"><?= $c['name']?></a>
                <span>: <?= $c['description']?></span>
                <span>- Unread messages(<?= $c['num_unread']?>)</span>
            </p>
        <?php endforeach; ?>
    </div>
    <?php else:?>
    <div>
        <h1>Messages</h1>
        <?php foreach ($messages as $m): ?>
            <p>
                <span><b><?= $m['username']?></b>:</span>
                <span><?= $m['text']?></span>
                <span><i><?= $m['time']?></i></span>
            </p>
        <?php endforeach; ?>

        <form action="" method="post">
            <label>
                Username:
                <input name="username" value="<?= $username?>">
            </label>
            <label>
                Message:
                <textarea name="text"></textarea>
            </label>
            <button type="submit">Submit</button>
            <p><?= $info?></p>
        </form>
    </div>
    <?php endif;?>
</body>
</html>

