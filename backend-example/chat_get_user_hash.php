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
 * The POST request is sent to the address and in the id parameter the list of user's hash identifiers is needed
 * Answer in json
 */ 

include './config.php';
testIpOrDie();

echo getUserHash((int)$_POST["id"]);



