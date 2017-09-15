<?php
/**
 * Apache License 2.0
 * @author Trapenok Victor, Levhav@ya.ru, 89244269357
 * I will be glad to new orders for the development of anything.
 *
 * Levhav@ya.ru
 * Skype:Levhav
 * 89244269357
 *
 * https://github.com/Levhav/Star.Comet-Chat
 */


/**
 * Warning takes an id from $ _COOKIE and not from $ _SESSION
 * @return int
 */
function getUserIdOrDie()
{
    $user_id = false;
    if(isset($_COOKIE['user_id']))
    {
        $user_id = (int)$_COOKIE['user_id'];
    }
    if(isset($_POST['user_id']))
    {
        $user_id = (int)$_POST['user_id'];
        setcookie("user_id", $user_id, time() + getConfArray('cookie_expire'), '/', "comet-server.com");  
    }

    if( !$user_id )
    {
        die(json_encode(array("success"=>false, "error" => "Authorization failed [1]")));
    }

    $user_key = false;
    if(isset($_COOKIE['user_key']))
    {
        $user_key = $_COOKIE['user_key'];
    }
    if(isset($_POST['user_key']))
    {
        $user_key = $_POST['user_key'];
        setcookie("user_key", $user_key, time() + getConfArray('cookie_expire'), '/', "comet-server.com");  
    }

    if( !$user_key )
    {
        die(json_encode(array("success"=>false, "error" => "Authorization failed[2]")));
    }


    $hashResult = "";
    $result = mysqli_query(StarCometChat::conf()->getComet(), "SELECT hash FROM users_auth WHERE id = ".((int)$user_id));
    if(mysqli_errno(StarCometChat::conf()->getDB()) != 0)
    {
        die ("Error code:".mysqli_errno(StarCometChat::conf()->getDB())." ".mysqli_error(StarCometChat::conf()->getDB())."");
    }
    else if(!mysqli_num_rows($result))
    {
        $hashResult = getUsersHash($user_id);
        mysqli_query(StarCometChat::conf()->getComet(), "INSERT INTO users_auth (id, hash)VALUES (".((int)$user_id).", '".mysqli_real_escape_string(StarCometChat::conf()->getComet(),$hashResult)."')");
        //die(json_encode(array("success"=>false, "error" => "Authorization failed [1]")));
    }
    else
    {
        $row = mysqli_fetch_assoc($result);
        $hashResult = $row['hash'];
    }

    if($hashResult !== $user_key)
    {
        $hashResult = getUsersHash($user_id);
        if($hashResult !== $user_key)
        {
            die(json_encode(array("success"=>false, "error" => "Authorization failed [3]")));
        }
        mysqli_query(StarCometChat::conf()->getComet(), "INSERT INTO users_auth (id, hash)VALUES (".((int)$user_id).", '".mysqli_real_escape_string(StarCometChat::conf()->getComet(),$hashResult)."')");
    }
    
    additionalChecksAccess($user_id);

    return (int)$user_id;
}

function getAdminIdOrDie()
{
    $id = getUserIdOrDie();
    if (!in_array($id, StarCometChat::conf()->getAdminIds()))
    {
        die("Authorization required with administrator rights");
    }

    return $id;
}

function getUserKeyOrDie()
{
    if(isset($_COOKIE['user_key']))
    {
        return $_COOKIE['user_key'];
    }
    if(isset($_POST['user_key']))
    {
        return $_POST['user_key'];
    }

    die("Authorization required");
}


/**
 * Marks an array of messages read
 * @param type $from_user_id User, the recipient is the sender.
 * @param type $to_user_id User, recipient of messages.
 */
function markAsReadMessageArray($from_user_id, $to_user_id)
{
    // Mark that the message is read
    $result = mysqli_query(StarCometChat::conf()->getDB(), "UPDATE `messages` SET `read_time` = '".date("U")."' WHERE to_user_id = ".$to_user_id." and from_user_id = ".$from_user_id." and read_time = 0");
    if( true || mysqli_affected_rows(StarCometChat::conf()->getDB()))
    {
        // If the message exists and was previously unread, we send a notification.

        // Sending a notification to the user who sent the message that the message was read
        $msg = array("to_user_id" => $to_user_id, "time" => date("U"));
        mysqli_query(StarCometChat::conf()->getComet(), "INSERT INTO users_messages (id, event, message)VALUES(".$from_user_id.", 'readMessage', '".mysqli_real_escape_string(StarCometChat::conf()->getComet(),json_encode($msg))."')");
    }
}

/**
 * Send notification to the admin panel to all admins from the config
 * @param array $msg
 */
function sendMsgToAdmin($event, $msg)
{
    $msg = mysqli_real_escape_string(StarCometChat::conf()->getComet(), json_encode($msg));
    // Send notification to the admin panel to all admins from the config
    foreach (StarCometChat::conf()->getAdminIds() as $key => $value)
    {
        mysqli_query(StarCometChat::conf()->getComet(), "INSERT INTO users_messages (id, event, message)VALUES (".$value.", '".$event."', '".$msg."')");
    }
}
