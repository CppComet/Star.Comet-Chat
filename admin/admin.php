<?php
/**
 * Apache License 2.0
 * @author Trapenok Victor (Трапенок Виктор Викторович), Levhav@ya.ru, 89244269357
 * Буду рад новым заказам на разработку чего ни будь.
 *
 * Levhav@ya.ru
 * Skype:Levhav
 * 89244269357
 * 
 * https://github.com/Levhav/Star.Comet-Chat
 */

include '../config.php';
include '../common.php';


$admin_id = getAdminIdOrDie();
$admin_key = getUserKeyOrDie();
$url = getConfArray("home_dir");
$userLink = getConfArray("user_url_tpl");

$avatarUrl = getConfArray("user_avatar_url_tpl");



if(isset($_POST['query']) && $_POST['query'] == "removeMessage")
{
    removeMessage();
}

function removeMessage()
{ 
    $msg_id = (int)$_POST['messageId'];
    mysqli_query(StarCometChat::conf()->getDB(),"DELETE FROM `messages` WHERE `id` = ".$msg_id);
    echo json_encode(array("success" => true));
    exit;
}

function getByAbuse()
{
    global $url, $userLink, $avatarUrl;
    $startDate = (int)$_GET['startDate'];
    $endDate = (int)$_GET['endDate'];

    //  id user_id_from user_id_to time
    $result = mysqli_query(StarCometChat::conf()->getDB(), "SELECT abuse.id, abuse.user_id_from, abuse.user_id_to, abuse.time,
                                                         (select login from users where id = abuse.user_id_from ) as from_user_login,
                                                         (select login from users where id = abuse.user_id_to ) as to_user_login,
                                                         (select avatar_url from users where id = abuse.user_id_from ) as from_user_avatar,
                                                         (select avatar_url from users where id = abuse.user_id_to ) as to_user_avatar
                                                  FROM `abuse`
                                                  where time > ".$startDate." and time < ".$endDate." ORDER BY `abuse`.`id` DESC limit 300"); 
    if(!mysqli_num_rows($result))
    {
        echo "За указанный период жалоб не поступало.";
    }
    
    while($row = mysqli_fetch_assoc($result))
    {
        if(!$row['from_user_login'])
        {
            $NewContactInfo = getUsersInfo(array($row['from_user_id']));
            $NewContactInfo[0]['login'] = preg_replace("/^.*?([^\/]*)$/usi", "$1", $NewContactInfo[0]['login']);
            mysqli_query(StarCometChat::conf()->getDB(),
                    "INSERT INTO `users` (`id`, `login`, `avatar_url`) VALUES ('".$NewContactInfo[0]['user_id']."', '".
                                                mysqli_real_escape_string(StarCometChat::conf()->getDB(),$NewContactInfo[0]['login'])."', '".
                                                mysqli_real_escape_string(StarCometChat::conf()->getDB(),$NewContactInfo[0]['avatar_url'])."');");
        }
        
        echo "<div class='line' >"
                . "<div class='message-date' >".date("d-m-Y H:i", $row['time'])."</div>"
                . "<div class='dialogUsers'>"
                    ."<a href='".$userLink."".$row['from_user_login']."' target='_blank'><img src='".$avatarUrl.$row['from_user_avatar']."' title='".$row['from_user_login']."' /></a>"
                    ."<a href='".$userLink."".$row['to_user_login']."'  target='_blank' ><img src='".$avatarUrl.$row['to_user_avatar']."' title='".$row['to_user_login']."' /></a>"
                ."</div>"
                . "<div class='dialogLink'><a href='?query=getDialogByUsersId&user_id1=".$row['user_id_from']."&user_id2=".$row['user_id_to']."' target='_blank' >Диалог</a></div>"
            . "</div>";
    }

}


function getByDate()
{
    global $url, $userLink, $avatarUrl;
    $startDate = (int)$_GET['startDate'];
    $endDate = (int)$_GET['endDate'];
    $hasAttachments = (int)$_GET['hasAttachments'];
    if($hasAttachments)
    {
        $hasAttachments = " and  message like \"[[%\" ";
    }
    else
    {
        $hasAttachments = "";
    }

    $result = mysqli_query(StarCometChat::conf()->getDB(), "SELECT msg.id, msg.from_user_id, msg.to_user_id, msg.time, msg.message,
                                                         (select login from users where id = msg.from_user_id ) as from_user_login,
                                                         (select login from users where id = msg.to_user_id ) as to_user_login,
                                                         (select avatar_url from users where id = msg.from_user_id ) as from_user_avatar,
                                                         (select avatar_url from users where id = msg.to_user_id ) as to_user_avatar
                                                  FROM `messages` as msg
                                                  where time > ".$startDate." and time < ".$endDate." ".$hasAttachments." ORDER BY `msg`.`id` DESC limit 300");

    while($row = mysqli_fetch_assoc($result))
    {
        if(!$row['from_user_login'])
        {
            $NewContactInfo = getUsersInfo(array($row['from_user_id']));
            $NewContactInfo[0]['login'] = preg_replace("/^.*?([^\/]*)$/usi", "$1", $NewContactInfo[0]['login']);
            mysqli_query(StarCometChat::conf()->getDB(),
                    "INSERT INTO `users` (`id`, `login`, `avatar_url`) VALUES ('".$NewContactInfo[0]['user_id']."', '".
                                                mysqli_real_escape_string(StarCometChat::conf()->getDB(),$NewContactInfo[0]['login'])."', '".
                                                mysqli_real_escape_string(StarCometChat::conf()->getDB(),$NewContactInfo[0]['avatar_url'])."');");
        }
        
        $row['message'] = preg_replace("/\[\[img=([A-z0-9\._]+)\]\]/usi", "<img src='".getConfArray("host_name")."/".getConfArray('file_dir')."/$1'>", preg_replace("/\n/usi", "<br>", $row['message']));
        echo "<div class='line' id='msgId-".$row['id']."' >"
                . "<div class='message-date' >".date("d-m-Y  H:i", $row['time'])."</div>"
                . "<div class='dialogUsers'>"
                    ."<a href='".$userLink."".$row['from_user_login']."' target='_blank'><img src='".$avatarUrl.$row['from_user_avatar']."' title='".$row['from_user_login']."' /></a>"
                    ."<a href='".$userLink."".$row['to_user_login']."'  target='_blank' ><img src='".$avatarUrl.$row['to_user_avatar']."' title='".$row['to_user_login']."' /></a>"
                ."</div>"
                . "<div class='dialogLink'><a href='?query=getDialogByUsersId&user_id1=".$row['from_user_id']."&user_id2=".$row['to_user_id']."' target='_blank' >Диалог</a></div>"
                . "<div class='removeLink'><a href='#' onclick='removeMessage(".$row['id'].")' >Удалить</a></div>"
                . "<div class='message'>".$row['message']."</div>"
            . "</div>";
    }

}

function getByDateToday()
{
    global $url, $userLink, $avatarUrl;
    $result = mysqli_query(StarCometChat::conf()->getDB(), "SELECT msg.id, msg.from_user_id, msg.to_user_id, msg.time, msg.message,
                                                         (select login from users where id = msg.from_user_id ) as from_user_login,
                                                         (select login from users where id = msg.to_user_id ) as to_user_login,
                                                         (select avatar_url from users where id = msg.from_user_id ) as from_user_avatar,
                                                         (select avatar_url from users where id = msg.to_user_id ) as to_user_avatar
                                                  FROM `messages` as msg
                                                  where time > ".(date("U")-3600*24)." ORDER BY `msg`.`id` DESC limit 300");
 
    while($row = mysqli_fetch_assoc($result))
    {
        if(!$row['from_user_login'])
        {
            $NewContactInfo = getUsersInfo(array($row['from_user_id']));
            
            if(isset($NewContactInfo[0]))
            { 
                $NewContactInfo[0]['login'] = preg_replace("/^.*?([^\/]*)$/usi", "$1", $NewContactInfo[0]['login']);
                mysqli_query(StarCometChat::conf()->getDB(),
                    "INSERT INTO `users` (`id`, `login`, `avatar_url`) VALUES ('".$NewContactInfo[0]['user_id']."', '".
                                                mysqli_real_escape_string(StarCometChat::conf()->getDB(),$NewContactInfo[0]['login'])."', '".
                                                mysqli_real_escape_string(StarCometChat::conf()->getDB(),$NewContactInfo[0]['avatar_url'])."');");
            }
            else
            {
                mysqli_query(StarCometChat::conf()->getDB(), 
                    "INSERT INTO `users` (`id`, `login`, `avatar_url`) VALUES ('".$row['from_user_id']."', 'Удалён', '".$url."/img/avata.png');");
            }
        }
        
        $row['message'] = preg_replace("/\[\[img=([A-z0-9\._]+)\]\]/usi", "<img src='".getConfArray("host_name")."/".getConfArray('file_dir')."/$1'>", preg_replace("/\n/usi", "<br>", $row['message']));
        echo "<div class='line' id='msgId-".$row['id']."' >"
                . "<div class='message-date' >".date("d-m-Y H:i", $row['time'])."</div>"
                . "<div class='dialogUsers'>"
                    ."<a href='".$userLink."".$row['from_user_login']."' target='_blank'><img src='".$avatarUrl.$row['from_user_avatar']."' title='".$row['from_user_login']."' /></a>"
                    ."<a href='".$userLink."".$row['to_user_login']."'  target='_blank' ><img src='".$avatarUrl.$row['to_user_avatar']."' title='".$row['to_user_login']."' /></a>"
                ."</div>"
                . "<div class='dialogLink'><a href='?query=getDialogByUsersId&user_id1=".$row['from_user_id']."&user_id2=".$row['to_user_id']."' target='_blank' >Диалог</a></div>"
                . "<div class='removeLink'><a href='#' onclick='removeMessage(".$row['id'].")' >Удалить</a></div>"
                . "<div class='message'>".$row['message']."</div>"
            . "</div>";
    }
}

function getByLogin()
{
    global $url, $userLink, $avatarUrl;
    $startDate = (int)$_GET['startDate'];
    $endDate = (int)$_GET['endDate'];
    $andTime = "";
    if($startDate !=0 && $endDate !=0)
    {
        $andTime = " and time > ".$startDate." and time < ".$endDate." ";
    }
    $login = mysqli_real_escape_string(StarCometChat::conf()->getDB(),trim($_GET['login']));
    
    
    $result = mysqli_query(StarCometChat::conf()->getDB(), "select avatar_url from users where login = '".$login."'");
    $row = mysqli_fetch_assoc($result);
    $avatar_url = $row['avatar_url'];

    $result = mysqli_query(StarCometChat::conf()->getDB(), "SELECT msg.id, msg.from_user_id, msg.to_user_id, msg.time, msg.message,
                                                         (select login from users where id = msg.to_user_id ) as to_user_login,
                                                         (select avatar_url from users where id = msg.to_user_id ) as to_user_avatar
                                                  FROM `messages` as msg
                                                  where from_user_id in( (select id from users where login = '".$login."' ) )  ".$andTime."
                                                  ORDER BY `msg`.`id` DESC limit 300");

    while($row = mysqli_fetch_assoc($result))
    {
        $row['message'] = preg_replace("/\[\[img=([A-z0-9\._]+)\]\]/usi", "<img src='".getConfArray("host_name")."/".getConfArray('file_dir')."/$1'>", preg_replace("/\n/usi", "<br>", $row['message']));
        echo "<div class='line' id='msgId-".$row['id']."' >"
                . "<div class='message-date' >".date("d-m-Y H:i", $row['time'])."</div>"
                . "<div class='dialogUsers'>"
                    ."<a href='".$userLink."".$login."' target='_blank'><img src='".$avatarUrl.$avatar_url."' title='Отправитель ".$login."' /></a>" 
                    ."<a href='".$userLink."".$row['to_user_login']."'  target='_blank' ><img src='".$avatarUrl.$row['to_user_avatar']."' title='Получатель ".$row['to_user_login']."' /></a>"
                ."</div>"
                . "<div class='dialogLink'><a href='?query=getDialogByUsersId&user_id1=".$row['from_user_id']."&user_id2=".$row['to_user_id']."' target='_blank' >Диалог</a></div>"
                . "<div class='removeLink'><a href='#' onclick='removeMessage(".$row['id'].")' >Удалить</a></div>"
                . "<div class='message'>".$row['message']."</div>"
            . "</div>";
    }
}

function countMsgByTime($time)
{  
// select sum(countMsg.one) from (SELECT 1 as one FROM `messages` where time > 1444824799 - 3600*24  group by from_user_id, to_user_id) as countMsg
// SELECT count(*) as countMsg FROM `messages` where time >  1444824799 - 3600*24
    $result = mysqli_query(StarCometChat::conf()->getDB(), "select sum(countMsg.one) as countDlg from (SELECT 1 as one FROM `messages` where time > ".((int)$time."  group by from_user_id, to_user_id) as countMsg"));
    $row = mysqli_fetch_assoc($result);
    return $row["countDlg"];
}




function getDialogByUsersId()
{
    global $url, $userLink, $avatarUrl;
    if(!isset($_GET['startDate']) )
    {
        $_GET['startDate'] = 0;
    }

    if(!isset($_GET['endDate']) )
    {
        $_GET['endDate'] = 0;
    }

    $user_id1 = (int)$_GET['user_id1'];
    $user_id2 = (int)$_GET['user_id2'];

    $startDate = (int)$_GET['startDate'];
    $endDate = (int)$_GET['endDate'];
    $andTime = "";

    if($startDate !=0 && $endDate !=0)
    {
        $andTime = " and time > ".$startDate." and time < ".$endDate." ";
    }

    $user_login1 = $user_id1;
    $user_login2 = $user_id2;
    $user_avatar1 = "";
    $user_avatar2 = "";

    $result = mysqli_query(StarCometChat::conf()->getDB(),"SELECT id, login, avatar_url FROM `users` where id in(".$user_id1.",".$user_id2.")");
    $row = mysqli_fetch_assoc($result);
    if($row['id'] == $user_id1)
    {
        $user_login1 = $row['login'];
        $user_avatar1 = $row['avatar_url'];
    }
    else
    {
        $user_login2 = $row['login'];
        $user_avatar2 = $row['avatar_url'];
    }

    $row = mysqli_fetch_assoc($result);
    if($row['id'] == $user_id2)
    {
        $user_login2 = $row['login'];
        $user_avatar2 = $row['avatar_url'];
    }
    else
    {
        $user_login1 = $row['login'];
        $user_avatar1 = $row['avatar_url'];
    }


    $result = mysqli_query(StarCometChat::conf()->getDB(),
            "SELECT id, from_user_id, to_user_id, read_time, time, message FROM `messages` "
            . " where (from_user_id = ".$user_id1." and to_user_id = ".$user_id2.") or (to_user_id = ".$user_id1." and from_user_id = ".$user_id2.") ".$andTime
            . " order by time desc limit 300");

    while($row = mysqli_fetch_assoc($result))
    {
        if($row['from_user_id'] == $user_id1)
        {
            $from_user = $user_login1;
            $to_user = $user_login2;
            
            $from_user_avatar = $user_avatar1;
            $to_user_avatar = $user_avatar2;
        }
        else
        {
            $from_user = $user_login2;
            $to_user = $user_login1;
            
            $from_user_avatar = $user_avatar2;
            $to_user_avatar = $user_avatar1;
        }

        $row['message'] = preg_replace("/\[\[img=([A-z0-9\._]+)\]\]/usi", "<img src='".getConfArray("host_name")."/".getConfArray('file_dir')."/$1'>", preg_replace("/\n/usi", "<br>", $row['message']));
        echo "<div class='line' id='msgId-".$row['id']."' >"
                . "<div class='message-date' >".date("d-m-Y H:i", $row['time'])."</div>"
                . "<div class='dialogUsers'>"
                    ."<a href='".$userLink."".$from_user."' target='_blank'><img src='".$avatarUrl.$from_user_avatar."' title='".$from_user."' /></a>"
                    ."<a href='".$userLink."".$to_user."' target='_blank'><img src='".$avatarUrl.$to_user_avatar."' title='".$to_user."' /></a>"
                ."</div>"
                . "<div class='dialogLink'><a href='?query=getDialogByUsersId&user_id1=".$row['from_user_id']."&user_id2=".$row['to_user_id']."' target='_blank'>Диалог</a></div>"
                . "<div class='removeLink'><a href='#' onclick='removeMessage(".$row['id'].")' >Удалить</a></div>"
                . "<div class='message'>".$row['message']."</div>"
            . "</div>";
    }

}

if(isset($_GET['query']) && $_GET['query'] == "getByDate")
{
    if(!isset($_GET['startDate']) || !isset($_GET['endDate']))
    {
        die("Не переданы startDate и endDate");
    }
}
else if(isset($_GET['query']) && $_GET['query'] == "getDialogByUsersId")
{
    if(!isset($_GET['user_id1']) || !isset($_GET['user_id2']))
    {
        die("Не переданы user_id1 и user_id2");
    }
}
else if(isset($_GET['query']) && $_GET['query'] == "getByLogin")
{
    if(!isset($_GET['startDate']) || !isset($_GET['endDate']))
    {
        die("Не переданы startDate и endDate");
    }

    if(!isset($_GET['login']) || strlen($_GET['login']) < 1 )
    {
        die("Не передан login");
    }
}
else if(isset($_GET['query']) && $_GET['query'] == "getByAbuse")
{
    if(!isset($_GET['startDate']) || !isset($_GET['endDate']))
    {
        die("Не переданы startDate и endDate");
    }
} 
?>
<!DOCTYPE HTML>
<html>
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="<?php echo $url; ?>/admin/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $url; ?>/admin/bootstrap/css/bootstrap-datetimepicker.css">

    <!-- Подключаем библиотеки -->
    <script src="https://comet-server.com/CometServerApi.js" type="text/javascript"></script>
    <script src="<?php echo $url; ?>/js/jquery.min.js"      type="text/javascript"></script>
    <script src="<?php echo $url; ?>/js/jquery.cookie.js"   type="text/javascript" ></script>
    <script src="<?php echo $url; ?>/js/moment.min.js"      type="text/javascript"></script>

    <script src="<?php echo $url; ?>/admin/bootstrap/js/bootstrap.min.js"></script>
    <script src="<?php echo $url; ?>/admin/bootstrap/js/moment-with-locales.js"></script>
    <script src="<?php echo $url; ?>/admin/bootstrap/js/bootstrap-datetimepicker.js"></script>

</head>
<body>
<style>

body {
  padding-top: 50px;
}
.starter-template {
  padding: 40px 15px;
  text-align: justify;
}

.login{
    display: inline-block;
    width: 300px;
    background-color: #ff8;
}

.message{
    word-break: break-word;
    display: table-cell;
}
.message img{
    max-width: 100%;
    max-height: 300px;
    display: block;
}

.line{
    border: 1px solid #ddd;
    margin: 10px 0px;
    padding: 10px;
    border-radius: 10px;
    display: table-row;
}

.line div{
    padding: 8px;
    border:1px solid #ccc;
    vertical-align: top;
}

.message-date{ 
    width: 140px;
    font-weight: bold;
    display: table-cell;
}

.dialogUsers{
    display: table-cell;
    width: 120px;
}
.dialogUsers a{
    margin-right: 5px;
}

.dialogUsers a img{
    width: 45px;
    height: 45px;
}

.dialogLink{
    display: table-cell;
    width: 50px;
}

.removeLink{
    display: table-cell;
    width: 50px;
}

.message-abuse{
    display: table-cell;
    width: 130px;
    font-weight: bold;
    color: #f77;
}

.lineTable{
    display: table;
    width: 100%; 
}
</style>

<script type="text/javascript">

var baseUrl = "<?php echo $url; ?>"
var userLink = "<?php echo $userLink; ?>";
</script>

<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Star.comet chat</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="<?php echo $url; ?>admin/admin.php">Управление чатом</a></li>
                <li title="Диалоги за 1 час" ><a>1 час - <?php echo countMsgByTime(date("U")-3600); ?></a></li>
                <li title="Диалоги за 24 часа" ><a>24 часа - <?php echo countMsgByTime(date("U")-3600*24); ?></a></li>
                <li title="Диалоги за 7 дней" ><a>7 дней - <?php echo countMsgByTime(date("U")-3600*24*7); ?></a></li>
                <li title="Диалоги за месяц" ><a>30 дней - <?php echo countMsgByTime(date("U")-3600*24*30); ?></a></li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>

<div >
    <div class="container">
        <div class="starter-template">

            <div>
                <div style="width: 200px; display: inline-block;float: left;    margin-right: 10px;">
                    <div class="form-group">
                        <div class='input-group date' id='datetimepicker3'>
                            <input type='text' class="form-control" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div style="width: 200px; display: inline-block;float: left;    margin-right: 10px;">
                    <div class="form-group">
                        <div class='input-group date' id='datetimepicker4'>
                            <input type='text' class="form-control" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div style="width: 200px; display: inline-block;float: left;    margin-right: 10px;">
                    <div class="form-group">
                        <button class="btn btn-default" onclick="getByAbuse()">Выбрать жалобы по дате</button>
                    </div>
                </div>
            </div>
            <div style="clear: both;"></div>
            <div>
                <div style="width: 200px; display: inline-block;float: left;    margin-right: 10px;">
                    <div class="form-group">
                        <div class='input-group date' id='datetimepicker6'>
                            <input type='text' class="form-control" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div style="width: 200px; display: inline-block;float: left;    margin-right: 10px;">
                    <div class="form-group">
                        <div class='input-group date' id='datetimepicker7'>
                            <input type='text' class="form-control" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div style="width: 200px; display: inline-block;float: left;    margin-right: 10px;">
                    <div class="form-group input-group">
                        <span class="input-group-btn"><button class="btn btn-default" onclick="getByDate(0)">По дате</button></span>
                        <span class="input-group-btn"><button class="btn btn-default" onclick="getByDate(1)">С вложениями</button></span>
                    </div>
                </div>
            </div>
            <div style="clear: both;"></div>
            <div>
                <div style="width: 200px; display: inline-block;float: left;    margin-right: 10px;">
                    <div class="form-group">
                        <div class='input-group date' id='datetimepicker1'>
                            <input type='text' class="form-control" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div style="width: 200px; display: inline-block;float: left;    margin-right: 10px;">
                    <div class="form-group">
                        <div class='input-group date' id='datetimepicker2'>
                            <input type='text' class="form-control" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Выбрать сообщения по логину" id='getByLogin'>
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" onclick="getByLogin()">Выбрать сообщения по логину!</button>
                    </span>
                </div><!-- /input-group -->
            </div>
            <hr>
            <?php
                if(isset($_GET['query']) && $_GET['query'] == "getByDate")
                {
                    echo "<h1>Список сообщений за определённый период</h1>";
                    echo "<div class='lineTable'>";
                    getByDate();
                    echo "</div>";
                }
                else if(isset($_GET['query']) && $_GET['query'] == "getByLogin")
                {
                    echo "<h1>Все сообщения пользователя</h1>";
                    echo "<div class='lineTable'>";
                    getByLogin();
                    echo "</div>";
                }
                else if(isset($_GET['query']) && $_GET['query'] == "getDialogByUsersId")
                {
                    echo "<h1>Диалог пользователей</h1>";
                    echo "<div class='lineTable'>";
                    getDialogByUsersId();
                    echo "</div>";
                }
                else if(isset($_GET['query']) && $_GET['query'] == "getByAbuse")
                {
                    echo "<h1>Список жалоб</h1>";
                    echo "<div class='lineTable'>";
                    getByAbuse();
                    echo "</div>";
                }
                else
                {
                    echo "<h1>Real time мониторинг диалогов</h1>";
                    echo "<div id='msgTable'  class='lineTable' ></div>";
                    echo "<div class='lineTable' >";
                    getByDateToday();
                    echo "</div>";
                }
            ?>

            <div style='clear: both;'></div>
        </div>
    </div><!-- /.container -->
</div>

<script type="text/javascript">
$(function ()
{
    $('#datetimepicker6').datetimepicker();
    $('#datetimepicker7').datetimepicker({
        useCurrent: false
    });
    $("#datetimepicker6").on("dp.change", function (e) {
        $('#datetimepicker7').data("DateTimePicker").minDate(e.date);
    });
    $("#datetimepicker7").on("dp.change", function (e) {
        $('#datetimepicker6').data("DateTimePicker").maxDate(e.date);
    });

    $('#datetimepicker1').datetimepicker();
    $('#datetimepicker2').datetimepicker({
        useCurrent: false
    });
    $("#datetimepicker1").on("dp.change", function (e) {
        $('#datetimepicker2').data("DateTimePicker").minDate(e.date);
    });
    $("#datetimepicker2").on("dp.change", function (e) {
        $('#datetimepicker1').data("DateTimePicker").maxDate(e.date);
    });

    $('#datetimepicker3').datetimepicker();
    $('#datetimepicker4').datetimepicker({
        useCurrent: false
    });
    $("#datetimepicker3").on("dp.change", function (e) {
        $('#datetimepicker4').data("DateTimePicker").minDate(e.date);
    });
    $("#datetimepicker4").on("dp.change", function (e) {
        $('#datetimepicker3').data("DateTimePicker").maxDate(e.date);
    });

    CometServer().start({dev_id:1, user_key:'<?php echo $admin_key; ?>', user_id: <?php echo $admin_id; ?>})

    if($("#msgTable").length)
    {
        CometServer().subscription("msg.newMessageForUser", function(event)
        {
            msgt = event
            event.data.message = CometServer().Base64.decode(event.data.message)
            event.data.message = decodeURIComponent(event.data.message)
            event.data.message = event.data.message.replace(/\[\[img=([A-z0-9\._]+)\]\]/mg, "<img src='<?php echo getConfArray("host_name")."/".getConfArray('file_dir') ?>/$1'>").replace(/\n/mg, "<br>");
              
            var html = "";
                html += "<div class='message-date' >"+moment().format("DD-MM-YYYY HH:mm")+"</div>"
                html += "<div class='dialogUsers'><a href='"+userLink+event.data.loginFrom+"' target='_blank'><img src='"+event.data.avatarFrom+"' title='"+userLink+event.data.loginFrom+"' /></a>"
                        +"<a href='"+userLink+event.data.loginTo+"' ><img src='"+event.data.avatarTo+"' title='"+userLink+event.data.loginTo+"' /></a></div>"
                html += "<div class='dialogLink'><a href='?query=getDialogByUsersId&user_id1="+event.data.loginFrom+"&user_id2="+event.data.loginTo+"'>Диалог</a></div>"
                html += "<div class='message'>"+event.data.message+"</div>"

            $("#msgTable").html("<div class='line'>"+html+"</div>" + $("#msgTable").html() );
        })

        CometServer().subscription("msg.newAbuseForUser", function(event)
        {
            var html = "<div class='message-abuse'>Жалоба</div>";
                html += "<div class='message-date' >"+moment().format("DD-MM-YYYY HH:mm")+"</div>"
                html += "<div class='dialogUsers'><a href='"+userLink+event.data.loginFrom+"' target='_blank'><img src='"+event.data.avatarFrom+"' title='"+userLink+event.data.loginFrom+"' /></a>"
                        +"<a href='"+userLink+event.data.loginTo+"' ><img src='"+event.data.avatarTo+"' title='"+userLink+event.data.loginTo+"' /></a></div>"
                html += "<div class='dialogLink'><a href='?query=getDialogByUsersId&user_id1="+event.data.loginFrom+"&user_id2="+event.data.loginTo+"'>Диалог</a></div>"


            $("#msgTable").html("<div class='line'>"+html+"</div>" + $("#msgTable").html() );
        })
    }
});

/**
 *  
 * @param {type} hasAttachments Получить только сообщения с вложениями
 * @returns {undefined} */
function getByDate(hasAttachments)
{
    var startDate = 0
    
    if($('#datetimepicker6').data("DateTimePicker").date())
    {
        startDate = $('#datetimepicker6').data("DateTimePicker").date().unix();
    }
    
    var endDate = new Date()
    endDate = endDate.getTime();

    if($('#datetimepicker7').data("DateTimePicker").date())
    {
        endDate = $('#datetimepicker7').data("DateTimePicker").date().unix();
    }
     
    window.location.replace(baseUrl+"admin/admin.php?startDate="+startDate+"&endDate="+endDate+"&query=getByDate&hasAttachments="+hasAttachments/1);
}

function getByAbuse()
{
    var startDate = 0
    
    if($('#datetimepicker3').data("DateTimePicker").date())
    {
        startDate = $('#datetimepicker3').data("DateTimePicker").date().unix();
    }
    
    var endDate = new Date()
    endDate = endDate.getTime();

    if($('#datetimepicker4').data("DateTimePicker").date())
    {
        endDate = $('#datetimepicker4').data("DateTimePicker").date().unix();
    }
     
    window.location.replace(baseUrl+"admin/admin.php?startDate="+startDate+"&endDate="+endDate+"&query=getByAbuse");
}

function getByLogin()
{
    var login = $('#getByLogin').val();
    var startDate = 0
    
    if($('#datetimepicker1').data("DateTimePicker").date())
    {
        startDate = $('#datetimepicker1').data("DateTimePicker").date().unix();
    }
    
    var endDate = new Date()
    endDate = endDate.getTime();

    if($('#datetimepicker2').data("DateTimePicker").date())
    {
        endDate = $('#datetimepicker2').data("DateTimePicker").date().unix();
    }
     
    window.location.replace(baseUrl+"admin/admin.php?login="+login+"&query=getByLogin&startDate="+startDate+"&endDate="+endDate);
}


function removeMessage(messageId)
{
    if(!confirm("Удалить сообщение?"))
    {
        return;
    }
    
    $("#msgId-"+messageId).animate({"opacity":0});

    $.ajax({
        url: baseUrl+"admin/admin.php",
        type: 'POST',
        dataType:'json',
        data:"messageId="+encodeURIComponent(messageId)+"&query=removeMessage",
        success: function (response)
        {
            $("#msgId-"+messageId).animate({"opacity":0}).hide();
            
        },
        error:function()
        {
            alert("ajax error");
        }
    });
}
</script>

</body>
</html>
