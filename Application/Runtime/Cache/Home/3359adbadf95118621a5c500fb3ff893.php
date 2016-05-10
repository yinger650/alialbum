<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>

    <link rel="stylesheet" type="text/css" href="/aliphoto/Public/assets/css/bootstrap.min.css" media="all">

    <script type="text/javascript" src="/aliphoto/Public/assets/js/jquery-2.2.3.min.js"></script>
    <script type="text/javascript" src="/aliphoto/Public/assets/js/bootstrap.min.js"></script>

</head>
<body>
<div id="login">
    <h2>User Register</h2>
    <form action="" method="post">
        <label>UserName  :</label>
        <input type="text" name="username" id="name" placeholder="username"><br><br>
        <label>Password  :</label>
        <input type="password" name="password" id="password" placeholder="**********"><br><br>
        <label>Repeat Password  :</label>
        <input type="password" name="repassword" id="repassword" placeholder="**********"><br><br>
        <label>Mail  :</label>
        <input type="text" name="mail" id="mail" placeholder="mail"><br><br>

        <input type="submit" value=" Login " name="submit"><br>
        <span></span>
    </form>
</div>
</body>
</html>