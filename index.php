<?php
require_once('config.php');
$pdo = connectDatabase(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);

if ($pdo) {
    echo "Pripojene k DB.";
} else {
    echo "error";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

    <br>
    Hello woerld
</body>

</html>