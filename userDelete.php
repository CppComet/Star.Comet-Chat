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
  
include './config.php';
include './common.php';
  
testIpOrDie();
$user_id = (int)$_GET['id'];


$result = mysqli_query(StarCometChat::conf()->getDB(), "SELECT * FROM `messages` where (from_user_id = ".$user_id." or to_user_id = ".$user_id.") and message like \"[[img%\""); 
if(mysqli_num_rows($result))
{
    while($row = mysqli_fetch_assoc($result))
    {
        $msg = preg_replace("/\[\[img=(.*)\]\]/", getConfArray('file_dir')."/$1", $row['message']); 
        
        if(@unlink($msg))
        {
            echo "Successfully deleted:".$msg."\n";
        }
        else
        {
            echo "Failed to delete: ".$msg."\n";
        }
    }
} 


mysqli_query(StarCometChat::conf()->getDB(), "delete FROM `messages` where from_user_id = ".$user_id." or to_user_id = ".$user_id.""); 
mysqli_query(StarCometChat::conf()->getDB(), "delete FROM `users_relations` WHERE user_id = ".$user_id." or to_user_id = ".$user_id.""); 
mysqli_query(StarCometChat::conf()->getDB(), "delete FROM `abuse` where `user_id_from` = ".$user_id." or  `user_id_to` = ".$user_id." "); 

