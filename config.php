<?php
/**
 * Плагин чата для сайта знакомств
 *
 * @author Трапенок Виктор Викторович, Levhav@ya.ru, 89244269357
 * Буду рад новым заказам на разработку чего ни будь.
 *
 * Levhav@ya.ru
 * Skype:Levhav
 * 89244269357
 */



$conf = array();
$conf['admin_ids'] = array(1, 2, 3); // Список id пользователей с правами администратора

/**
 * ip адрес с которого разрешено вызывать api методы управления чатом
 * Или false если ограничение отключено (не безопасно)
 */
$conf['trusted_ip'] = false; // На пример "159.8.8.107"

// @todo Перенести сюда все настроки которые надо будет менять пользователям в момент интеграции чата


/**
 * Разрешает доступ только для запросов с доверенных ip адресов
 */
function testIpOrDie()
{
    if ( getConfArray("trusted_ip") != false && $_SERVER['HTTP_X_REAL_IP'] != getConfArray("trusted_ip"))
    {
        die("Нет доступа с ip ".$_SERVER['HTTP_X_REAL_IP']);
    }
}

function getConfArray($val)
{
    global $conf;
    return $conf[$val];
}


header("Content-Type: text/html; charset=utf-8");
header("Access-Control-Allow-Origin: *");

ini_set('display_errors','on');
error_reporting(E_ALL);

class app
{
    /**
     * @var app
     */
    private static $conf = false;

    public $db = false;
    public $comet = false;

    /**
     * Список id пользователей с правами администратора
     * @var array
     */
    public $admin_ids = array();

    /**
     * Количество сообщений истории подгружаемых за 1 раз
     * Должно совпадать с тем что указанно в chat.js
     * @var int
     */
    public $page_size = 30;

    private function __construct()
    {
        global $conf;
        $this->admin_ids = $conf['admin_ids'];
    }

    /**
     * @return app
     */
    public static function conf()
    {
        if(!self::$conf)
        {
            self::$conf = new app();
        }
        return self::$conf;
    }

    public function getDB()
    {
        if(!$this->db)
        {
            /**
             * Доступ к БД
             */
            $this->db = mysqli_connect("localhost", "StarCometChat", "RLUJ4TXE22XL5JTh", "StarCometChat");
            if ( !$this->db ) die ("Невозможно подключение к MySQL");

            mysqli_query($this->db, "SET CHARACTER SET 'utf8'");
        }

        return $this->db;
    }

    public function getComet()
    {
        if(!$this->comet)
        {
            /**
             * Доступ к комет серверу
             */
            $this->comet = mysqli_connect("app.comet-server.ru", "15", "lPXBFPqNg3f661JcegBY0N0dPXqUBdHXqj2cHf04PZgLHxT6z55e20ozojvMRvB8", "CometQL_v1");
            if ( !$this->comet ) die ("Невозможно подключение к CometQL");
        }

        return $this->comet;
    }
}


function getUsersInfo($arr)
{
    if(is_array($arr))
    {
        $arr = implode(',', $arr);
    }
    else
    {
        $arr = (int)$arr;
    }

    if( $curl = curl_init())
    {
        curl_setopt($curl, CURLOPT_URL, 'http://comet-server.ru/doc/CometQL/Star.Comet-Chat/backend-example/chat_get_users.php');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_USERAGENT,  $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "users=".$arr);
        $out = curl_exec($curl);
        curl_close($curl);
        return json_decode($out, 'true');
    }
    return false;
}

function getUsersHash($arr)
{
    if(is_array($arr))
    {
        $arr = implode(',', $arr);
    }
    else
    {
        $arr = (int)$arr;
    }

    if( $curl = curl_init())
    {
        curl_setopt($curl, CURLOPT_URL, 'http://comet-server.ru/doc/CometQL/Star.Comet-Chat/backend-example/chat_get_user_hash.php');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_USERAGENT,  $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "id=".$arr);
        $out = curl_exec($curl);
        curl_close($curl);
        return $out;
    }
    return "";
}
