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

include_once $_SERVER['DOCUMENT_ROOT'].'/config.php';


$conf = array();

/**
 * ip адрес с которого разрешено вызывать api методы управления чатом
 * Или false если ограничение отключено (не безопасно)
 */
$conf['trusted_ip'] = $_SERVER['SERVER_ADDR'];       // На пример "159.8.8.107"
$conf['max_img_size'] = 1024*1024*4;   // Максимальный размер загружаемого изображения
$conf['cookie_expire'] = 3600*24;                    // Время жизни кук авторизации
 
/**
 * url адрес дирректории из которой работает чат
 * используется в админке
 */
$conf['home_dir'] = "http://comet-server.ru/doc/CometQL/Star.Comet-Chat";
$conf['host_name'] = "http://comet-server.ru";

/**
 * api ключ для работы яндекс переводчика
 * Получать здесь https://tech.yandex.ru/translate/
 */
$conf['yandex_translate_key'] = "trnsl.1.1.20150628T023941Z.e709b71aa86ebce5.3bd7a5a621ac9d1e1c5fe7ba87afebf351fda5ca";

/**
 * Папка для загрузки изображений
 */
$conf['file_dir'] = "chatFiles";


/**
 * Фрагмент url адреса страницы профиля пользователя
 * используется в админке
 */
$conf['user_url_tpl'] = "http://comet-server.ru/doc/CometQL/Star.Comet-Chat/backend-example/userPage.php?name=";
 
/**
 * Часть пути к аватаркам
 * @type String
 * Например http://comet-server.ru/avatar/
 * И к этому потом будет добавлен логин пользователя
 */
$conf['user_avatar_url_tpl'] = ""; 
  
/**
 * Доступ к БД
 */
$conf['mysql_db'] = "StarCometChat";
$conf['mysql_user'] = "StarCometChat";
$conf['mysql_pw'] = "RLUJ4TXE22XL5JTh";
$conf['mysql_host'] = "localhost"; 
/**
 * Доступ к комет серверу
 * Получить ключи доступа можно здесь https://comet-server.ru/menu_id/10
 */
$conf['cometQL_dev_id'] = 15;
$conf['cometQL_key'] = "lPXBFPqNg3f661JcegBY0N0dPXqUBdHXqj2cHf04PZgLHxT6z55e20ozojvMRvB8";

/**
 * Разрешает доступ только для запросов с доверенных ip адресов
 */
function testIpOrDie()
{
    if ( getConfArray("trusted_ip") != false && $_SERVER['REMOTE_ADDR'] != getConfArray("trusted_ip"))
    {
        die("Нет доступа с ip ".$_SERVER['REMOTE_ADDR']);
    }
}

function getConfArray($val)
{
    global $conf;
    return $conf[$val];
}

/**
 * URL для запроса хеша авторизации
 * Используется только в getUsersHash
 */
$conf['URL_getUsersHash'] = 'http://comet-server.ru/doc/CometQL/Star.Comet-Chat/backend-example/chat_get_user_hash.php';
/**
 * URL для запроса информации о пользователях в json
 * Используется только в getUsersInfo
 */
$conf['URL_getUsersInfo'] = 'http://comet-server.ru/doc/CometQL/Star.Comet-Chat/backend-example/chat_get_users.php'; 
 


header("Content-Type: text/html; charset=utf-8");
header("Access-Control-Allow-Origin: *");

ini_set('display_errors','on');
error_reporting(E_ALL);

class StarCometChat
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
    private $admin_ids = array();

    /**
     * Количество сообщений истории подгружаемых за 1 раз
     * Должно совпадать с тем что указанно в chat.js
     * @var int
     */
    public $page_size = 30;

    private function __construct()
    {
        global $conf;
    }

    /**
     * @return app
     */
    public static function conf()
    {
        if(!self::$conf)
        {
            self::$conf = new StarCometChat();
        }
        return self::$conf;
    }

    public function getAdminIds()
    { 
        /*
         * Пример того как выбрать список админов из бд если чат интегрируется в бд сайта а не как отдельное веб приложение с отдельной бд
        if(count($this->admin_ids) > 0)
        {
            return $this->admin_ids;
        }
        
        $result = app::dbQuery("SELECT id FROM `users` where status in(".USER_STATUS_MODERATOR.", ".USER_STATUS_ADMINISTRATOR.")");
        if(!$result || !mysqli_num_rows($result))
        {
            return $this->admin_ids;
        }
        else
        {
            while($row = mysqli_fetch_assoc($result))
            {
                $this->admin_ids[] = $row['id'];
            }
        }*/
        $this->admin_ids = array(1, 2, 3);
        return $this->admin_ids; 
    }

    public function getDB()
    {
        if(!$this->db)
        {
            /**
             * Доступ к БД
             */
            $this->db = mysqli_connect(getConfArray('mysql_host'), getConfArray('mysql_user'), getConfArray('mysql_pw'), getConfArray('mysql_db'));
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
            $this->comet = mysqli_connect("app.comet-server.ru", getConfArray('cometQL_dev_id'), getConfArray('cometQL_key'), "CometQL_v1");
            if ( !$this->comet ) die ("Невозможно подключение к CometQL");
        }

        return $this->comet;
    }
}

/**
 * Возвращает информацию о пользователях
 * @param array $arr
 * @return object
 *
 * Пример валидного ответа
 * [array(
 *  "user_id" => 6,
 *  "avatar_url" => "http://comet-server.ru/doc/CometQL/Star.Comet-Chat/img/avatar0.png",
 *  "name" => "Артём",
 *  "city" => "Артём",
 *  "age" => 12,
 *  "status" => "active",
 *  "login" => "lodin6"
 * ),
 * array(
 *  "user_id" => 6,
 *  "avatar_url" => "http://comet-server.ru/doc/CometQL/Star.Comet-Chat/img/avatar0.png",
 *  "name" => "Артём",
 *  "city" => "Артём",
 *  "age" => 12,
 *  "status" => "active",
 *  "login" => "lodin6"
 * )]
 *
 */
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
        curl_setopt($curl, CURLOPT_URL, getConfArray('URL_getUsersInfo'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_USERAGENT,  $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "users=".$arr);
        $out = curl_exec($curl); 
        curl_close($curl);
        return json_decode($out, 'true');
    }
    return false;
    
    /**
    * Пример того как выбрать информацию о пользователях из бд если чат интегрируется в бд сайта а не как отдельное веб приложение с отдельной бд
    if(is_array($arr))
    {
        $arr = preg_replace("#[^0-9,]#", "", implode(',', $arr));
    }
    else
    {
        $arr = (int)$arr;
    }

    $users = array();
    $result = app::dbQuery("SELECT id as user_id, name, '' as city, YEAR(Now()) - YEAR(birthdate) as age, 'active' as status, login, avatar_url  FROM `users` where id in(".$arr.")");
    if(!$result || !mysqli_num_rows($result))
    {
        return false;
    }
    else
    {
        while($row = mysqli_fetch_assoc($result))
        {
            if(empty($row['avatar_url']))
            {
                $row['avatar_url'] = app::conf()->default_avatar_url;
            }
            $users[] = $row;
        }
    }

    return $users;*/
}

/**
 * Возвращает хеш авторизации пользователя
 * @param int $user_id
 * @return string хеш авторизации пользователя на комет сервере
 */
function getUsersHash($user_id)
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
        curl_setopt($curl, CURLOPT_URL, getConfArray('URL_getUsersHash'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_USERAGENT,  $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "id=".$arr);
        $out = curl_exec($curl);
        curl_close($curl);
        return $out;
    }
    return "";
    
    /**
    * Пример того как выбрать информацию о пользователях из бд если чат интегрируется в бд сайта а не как отдельное веб приложение с отдельной бд
    $result = app::dbQuery("SELECT comet_hash FROM `users` where id = ':1' ", (int)$user_id);

    if(!$result || !mysqli_num_rows($result))
    {
        return "";
    }

    $row = mysqli_fetch_assoc($result);
    if(empty($row['comet_hash']))
    {
        $login = new login();
        $login->updateCometKey($user_id);
    }
    else
    {
        app::cometQuery("INSERT INTO users_auth (id, hash )VALUES (':1', ':2')", $user_id, $row['comet_hash']);
    }

    return $row['comet_hash'];*/
}

/**
 * дополнительная проверка доступа
 * @param type $user_id
 */
function additionalChecksAccess($user_id)
{
    /**
    * Пример того как проверить наличия прав у пользователя использовать чат из бд если чат интегрируется в бд сайта а не как отдельное веб приложение с отдельной бд
    $result = mysqli_query(StarCometChat::conf()->getDB(), "SELECT * FROM `users` WHERE (status > 3 or use_chat_to > ".date("U").") and id = ".$user_id);
    if(!mysqli_num_rows($result))
    { 
        die("Доступ запрещён. Оплатите использование чата.");
    }
     */
    
    return true;
}