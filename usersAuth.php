<?php

include './config.php';
include './common.php';

testIpOrDie();

if(isset($_GET['hash']))
{
    // Вход пользователя
    mysqli_query(app::conf()->getComet(), "INSERT INTO users_auth (id, hash)VALUES (".((int)$_GET['id']).", '".mysqli_real_escape_string(app::conf()->getComet(),$_GET['hash'])."')");  
}
else
{
    // Выход пользователя 
    mysqli_query(app::conf()->getComet(), "DELETE FROM users_auth WHERE id = ".((int)$_GET['id'])); 
}
echo "usersAuth-ok";
 








