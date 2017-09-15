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
 * Url address for the backend
 * @type String|String
 */

var relation_types = {0:'base', 1:'favorite', 2:'loc'}

var StarCometChat = function()
{
    return this;
}

StarCometChat.opt = {};

/**
 * Chat address
 * The home_dir parameter contains the location address of php chat scripts
 */
StarCometChat.opt.home_dir = undefined; 

/**
 * The path to the folder with the pictures sent to the chat
 * @type String
 */
StarCometChat.opt.file_dir = "chatFiles"; 
 
/**
 * Part of the way to the section with users
 * @type String
 * For example https://comet-server.com/user/
 * And then the user's login will be added to this
 */
StarCometChat.opt.user_url_tpl = "//"+window.location.host+"/"; 

/**
 * callback function called when the chat window is closed.
 * @type function
 */
StarCometChat.opt.onCloseChat = undefined

/**
 * Part of the way to avatars
 * @type String
 * For example https://comet-server.com/avatar/
 * And then the user's login will be added to this
 */
StarCometChat.opt.user_avatar_url_tpl = "//"+window.location.host+"/"; 

/**
 * Using sound notifications
 * @type Number
 */
StarCometChat.opt.useSsound = 1;

/**
 * Number of history messages downloaded for 1 time
 * Must match the one specified in config.php
 * @var int
 */
StarCometChat.opt.page_size = 30;

/**
 * User data from contact list
 */
StarCometChat.opt.data = undefined;

/**
 * id interlocutor with whom the dialogue is open
 * @type int
 * @private
 */
StarCometChat.interlocutor_id = undefined;

/**
 * List of files attached to the message
 * @private
 */
StarCometChat.uploadFilesList = undefined;

/**
 * The sum of all unread messages
 * @type Number
 * @private
 */
StarCometChat.newMessagesSum = 0;

/**
 * Indicates how initialization was
 * @type Boolean
 * @private
 */
StarCometChat.initSuccess = false;

/**
 * Indicates that initiation was already started
 * @type Boolean
 * @private
 */
StarCometChat.initProgress = false;

StarCometChat.first_dialog = -2

/**
 * List of offered translation options
 * @type Array
 * @link https://tech.yandex.ru/translate/doc/dg/concepts/langs-docpage/ List of supported languages
 */
StarCometChat.opt.langs = [{name:'ru', textName:['Русский']}, {name:'en', textName:['Английский']}]
    
/**
 * Chat initialization
 * options:{
 *  user_id: 1,
 *  user_key: "32 sign authorization key"
 *  open:true // Do I need to open a window or just initialize the chat and get contact information
 * }
 *
 * @public
 */
StarCometChat.init = function(options)
{
    $('.StarCometChat').hide();

    if(StarCometChat.initProgress === true)
    {
        return;
    }
    StarCometChat.initProgress = true;

    StarCometChat.opt.useSsound = (window.localStorage['StarCometChat.useSsound'] != 0)/1

    for(var i in options)
    {
        StarCometChat.opt[i] = options[i]
    }

    var html = '<audio class="StarCometChat-msg-audio">\
                <source src="'+StarCometChat.opt.home_dir+'/audio/msg.mp3" />\
        </audio>\
        \
        <div class="StarCometChat-holder">\
            <div class="StarCometChat-avatar StarCometChat-mobile-hidden">\
                <img src="'+StarCometChat.opt.home_dir+'/img/avata.png" class="StarCometChat-userAvatar">\
            </div>\
            <div class="StarCometChat-header"><div class="StarCometChat-userName"></div></div>\
            \
            <div class="StarCometChat-admin-link"></div>\
            \
            <div class="StarCometChat-header-btn-right">\
                <img src="'+StarCometChat.opt.home_dir+'/img/sound.png" onclick="StarCometChat.toggleSound()" class="toggleSoundBtn" >\
                <img src="'+StarCometChat.opt.home_dir+'/img/close.png" onclick="StarCometChat.closeChat()">\
            </div>\
\
            <table cellpadding="0" cellspacing="0" style="border-spacing:0px; border-collapse:collapse;">\
                <tr style="vertical-align: top;">\
                    <td class="StarCometChat-left-column StarCometChat-mobile-hidden" >\
                        <div class="StarCometChat-left-top-column" >\
                            <div class="StarCometChat-picLine">\
                               <div>\
                                   <div class="StarCometChat-inline StarCometChat-select" onclick="StarCometChat.tabShowBase();" ><div class="StarCometChat-pic1" title="Избранные" ></div></div>\
                                   <div class="StarCometChat-inline" onclick="StarCometChat.tabShowSearch()"    ><div class="StarCometChat-pic2" title="Поиск"  ></div></div>\
                                   <div class="StarCometChat-inline" onclick="StarCometChat.tabShowLoc()"       ><div class="StarCometChat-pic3"  title="Заблокированные" ></div></div>\
                               </div>\
                            </div>\
                            <div class="StarCometChat-all-message"></div>\
                            <div class="StarCometChat-left-tabs">\
                                <div class="StarCometChat-left-tab StarCometChat-left-tab-select StarCometChat-left-base-tab"  onclick="StarCometChat.tabShowBase(); ">\
                                    Messages\
                                </div>\
                                <div class="StarCometChat-left-tab StarCometChat-left-favorite-tab" onclick="StarCometChat.tabShowFavorite(); ">\
                                    Favorite\
                                </div>\
                            </div>\
                            <div class="StarCometChat-left-search">\
                                <input type="text" placeholder="Search"  class="StarCometChat-searchInContacts" \
                                       onchange="StarCometChat.searchInContacts()"\
                                       onkeydown="StarCometChat.searchInContacts()">\
                            </div>\
                        </div>\
                        <div class="StarCometChat-list-holder">\
                            <div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>\
                            <div class="viewport">\
                                <div class="overview StarCometChat-contact-list" >  \
                               </div>\
                            </div>\
                        </div>\
                    </td>\
                    <td class="StarCometChat-right-column">\
                        <div class="StarCometChat-right-top">\
                        <a target="_blank" class="StarCometChat-user-url-page" >\
                            <div class="StarCometChat-inline" style="float: left;" onclick="StarCometChat.OpenUserPage()">\
                                <div class="StarCometChat-statusTo StarCometChat-statusTo-point"></div>\
                                <div><img src="'+StarCometChat.opt.home_dir+'/img/avata2.png" class="StarCometChat-avatarTo" ></div>\
                            </div>\
                            <div class="StarCometChat-inline" style="float: left;margin-left: 10px;">\
                                <div class="StarCometChat-nameTo"></div>\
                                <div class="StarCometChat-statusTo StarCometChat-statusTo-text">Online</div>\
                                <div class="StarCometChat-lastOnlineTimeTo StarCometChat-small-color"></div>\
                            </div>\
                        </a>\
                           \
                            <div class="StarCometChat-inline" style="float: right;"> \
                                <div class="StarCometChat-inline StarCometChat-top-btn-open-list" onclick="$(\'.StarCometChat-top-btn-list ul\').toggle();  $(\'.StarCometChat-top-btn-open-list\').toggle(); event.stopPropagation()">\
                                    <img src="'+StarCometChat.opt.home_dir+'/img/ul-open.png">\
                                </div>\
                                <div class="StarCometChat-inline StarCometChat-top-btn-list"> \
                                    <ul class="StarCometChat-small-color" >\
                                        <li onclick="StarCometChat.sendAbuse()"><img src="'+StarCometChat.opt.home_dir+'/img/pic4.png">Complain</li> \
                                        <li onclick="StarCometChat.toggleBlockUser()" class="toggle-block-user-btn"><img src="'+StarCometChat.opt.home_dir+'/img/pic1.png"><span>Block</span></li>\
                                        <li onclick="StarCometChat.toggleFavoritUser()" class="toggle-favorit-user-btn"><img src="'+StarCometChat.opt.home_dir+'/img/star.png"><span>To favorites</span></li> \
                                    </ul> \
                                </div>\
                            </div>\
                        </div>\
\
                        <div class="StarCometChat-right-chat">\
                            <div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>\
                            <div class="viewport">\
                                <div class="overview StarCometChat-message-list" > \
                                   \
                                </div>\
                            </div>\
                        </div>\
\
                        <div class="StarCometChat-list-show-btn StarCometChat-list-toggle-btn"  onclick="StarCometChat.toggleLeftColumn()">&lt;</div>\
                        <div class="StarCometChat-list-hide-btn StarCometChat-list-toggle-btn"  onclick="StarCometChat.toggleLeftColumn()">&gt;</div>\
                        <div class="StarCometChat-block-alert" >User suspended by you<br><a href="#"  onclick="StarCometChat.toggleBlockUser(); return false;">Unlock</a></div>\
                        <div class="StarCometChat-not-paid-alert" ></div>\
 \
                        <div class="StarCometChat-input-holder">\
                            <table style="width: 100%;">\
                                <tr>\
                                    <td>\
                                        <textarea class="StarCometChat-input-text StarCometChat-input-message-text" placeholder="Message" onkeydown="StarCometChat.keydownSendMessage(event)"  ></textarea>\
                                    </td>\
                                    <td class="StarCometChat-input-btn-block" >\
                                        <div onclick="StarCometChat.sendMessage()" title="Send Ctrl+Enter" >\
                                            <img src="'+StarCometChat.opt.home_dir+'/img/carousel_arrow_right.png">\
                                        </div>\
                                        <div class="StarCometChat-input-file"> \
                                            <input type="file" onchange="StarCometChat.uploadFile(event)">\
                                        </div>\
                                    </td>\
                                </tr>\
                            </table>\
                            <div class="StarCometChat-send-text"  >Send Ctrl+Enter</div>\
                            <div class="StarCometChat-file-attachment" >Attached file</div>\
                            <div class="StarCometChat-copyInfo" >\
                                <a href="https://comet-server.com/wiki/doku.php/comet:star-comet-chat" target="_blank" >Chat is working on comet-server.com</a>\
                            </div>\
                        </div>\
                    </td>\
                </tr>\
            </table>\
        </div>'

    $(".StarCometChat").remove()

    $("body").append("<div class='StarCometChat-shadow'></div>")
             .append("<div class='StarCometChat-shadow-load'></div>")
             .append("<div class='StarCometChat' style='display: none;' onclick='if(arguments[0].target == this) StarCometChat.closeChat();' >\
                        <div class='StarCometChat-center-chat-style'>"+html+"</div>\
                     </div>");
    
    // Для убирания выподающих списков переводчика
    $(document).click(function() 
    {
            // all dropdowns
            $('.StarCometChat-wrapper-dropdown').removeClass('StarCometChat-wrapper-active');
    });
    
    if(StarCometChat.opt.open)
    { 
        $(".StarCometChat-shadow").show();
        $(".StarCometChat-shadow-load").show();
    }

    if(!StarCometChat.opt.useSsound)
    {
        $(".toggleSoundBtn").attr("src",  StarCometChat.opt.home_dir+"/img/soundNone.png");
    }
    else
    {
        $(".toggleSoundBtn").attr("src", StarCometChat.opt.home_dir+"/img/sound.png");
    }


    $('.StarCometChat-list-holder').tinyscrollbar();
    $('.StarCometChat-right-chat').tinyscrollbar();

    $.cookie("user_id", StarCometChat.opt.user_id);
    $.cookie("user_key", StarCometChat.opt.user_key);

    // Закрытие выподающего списка с действиями "Пожаловатья" и "Блокировать" по клику в любое место
    $('.StarCometChat-holder').bind("click", function()
    {
        if($('.StarCometChat-width-min').length != 0)
        {
            // Включён режим мобильного
            $('.StarCometChat-top-btn-list ul').hide()
            $('.StarCometChat-top-btn-open-list').show()
        }
    })
 
    var myInfo = function(data)
    {
        data = JSON.parse(data)
        StarCometChat.opt.data = {}
        if(!data.success)
        {
            console.log(data)
            //alert(data.error)
            StarCometChat.initSuccess = true;
            StarCometChat.opt.data.error = data.error
            
            if(StarCometChat.opt.open)
            {
                // Открываем первый диалог
                StarCometChat.openDialog();
            }
            return;
        }

        StarCometChat.opt.data = data;
        console.log(data)
        $('.StarCometChat-userName').html(data.myInfo.name)
        $('.StarCometChat-userAvatar').attr('src', StarCometChat.opt.user_avatar_url_tpl+data.myInfo.avatar_url);

        if(data.myInfo.is_admin)
        {
                 $(".StarCometChat-admin-link").show()
                 .html("<form method='POST' action='"+StarCometChat.opt.home_dir+"/admin/admin.php'><input type='submit' value='A' ><input type='hidden' value='"+StarCometChat.opt.user_id+"' name='user_id' ><input type='hidden' value='"+StarCometChat.opt.user_key+"' name='user_key' ></form>");
        }

        // Сортируем контакты по количеству новых сообщений
        var sortable_contacts = []
        for(var i in data.contacts)
        {
            if(!/^[0-9]+$/img.test(i))
            {
                continue;
            }
            sortable_contacts.push(data.contacts[i])
        }

        sortable_contacts.sort(function(b, a)
        {
            if(a.newMessages/1 - b.newMessages/1 !== 0)
            {
                return a.newMessages/1 - b.newMessages/1
            }

            return a.last_message_time/1 - b.last_message_time/1
        })

        var html = "";
        for(var i in sortable_contacts)
        {
            StarCometChat.newMessagesSum += sortable_contacts[i].newMessages/1
            html += StarCometChat.getContactHtml(sortable_contacts[i])
            StarCometChat.addSubscriptionToUserStatus(sortable_contacts[i].user_id)
        }

        StarCometChat.updateNewMessagesSum()
        $('.StarCometChat-contact-list').html(html);

        StarCometChat.tabShowBase();
        StarCometChat.initSuccess = true;


        // Определяем какой первый диалог в списке на открытие
        for(var i=0; i<sortable_contacts.length; i++ )
        {
            if(sortable_contacts[i].relation_type/1 == 0)
            {
                StarCometChat.first_dialog = sortable_contacts[i].user_id;
                break;
            }
        }

        if(!StarCometChat.openDialog_id && StarCometChat.opt.open)
        {
            // Открываем первый диалог
            StarCometChat.openDialog(StarCometChat.first_dialog);
        }
        else if(StarCometChat.openDialog_id)
        { 
            // Открываем диалог запрошеный до инициализации чата
            StarCometChat.openDialog(StarCometChat.openDialog_id);
        }
        StarCometChat.tabShowBase();

        if(StarCometChat.opt.success &&  typeof StarCometChat.opt.success === "function")
        {
            StarCometChat.opt.success();
        }
    }

    $(window).resize(function()
    {
        StarCometChat.chatUpdateSize()
    });


    jQuery.ajax({
        url: StarCometChat.opt.home_dir+"/myInfo.php",
        type: "POST",
        /*dataType:'json',*/
        data:"user_id="+encodeURIComponent(options.user_id)+"&user_key="+encodeURIComponent(StarCometChat.opt.user_key),
        success: myInfo
    });

    // Обработка входящих сообщений
    CometServer().subscription("msg.newMessage", function(event)
    {
        console.log("Сообщение newMessage");
        event.data.message = CometServer().Base64.decode(event.data.message);
        
        if(StarCometChat.interlocutor_id == event.data.from_user_id)
        {
            console.log("Сообщение доставлено в открытый диалог.");
            // Сообщение доставлено в открытый диалог.
            var dateTex = new Date();
            var html = StarCometChat.getMessageHtml({id:event.data.id, message:event.data.message, time:dateTex.getTime(), from_user_id:event.data.from_user_id})
            $('.StarCometChat-message-list').html( $('.StarCometChat-message-list').html() + html);
            $('.StarCometChat-right-chat').data("plugin_tinyscrollbar").update("bottom");

            StarCometChat.bindUpdateScrollForImgLoad()

            //Пометить прочитанным
            jQuery.ajax({
                url: StarCometChat.opt.home_dir+"/markAsReadMessage.php",
                type: "POST",
                /*dataType:'json',*/
                data:"from_user_id="+event.data.from_user_id+"&user_id="+encodeURIComponent(StarCometChat.opt.user_id)+"&user_key="+encodeURIComponent(StarCometChat.opt.user_key),
                success: function(result)
                {
                    console.log(result);
                }
            });
        }
        else if(event.data.new_contact != false && !StarCometChat.opt.data.contacts[event.data.from_user_id])
        {
            console.log("Это первое сообщение в диалоге", event.data)
            // Это первое сообщение в диалоге
            StarCometChat.opt.data.contacts[event.data.from_user_id] = {
                relation_type:0,
                last_online_time:0,
                newMessages:1,
            }
            
            for(var key in event.data.new_contact)
            {
                StarCometChat.opt.data.contacts[event.data.from_user_id][key] = event.data.new_contact[key];
            }

            var html = StarCometChat.getContactHtml(StarCometChat.opt.data.contacts[event.data.from_user_id])
            StarCometChat.addSubscriptionToUserStatus(event.data.from_user_id)

            $('.StarCometChat-contact-list').html(html + $('.StarCometChat-contact-list').html());
            StarCometChat.tabShowBase();
        }
        else
        {
            // @todo Когда приходит сообщение контакт должен поднятся в списке контактов на верх.
            // Сообщение доставлено но не прочитано.
            console.log("Сообщение доставлено но не прочитано.", event.data)
            StarCometChat.incrementNewMessage(event.data.from_user_id)
        }

        /*if(StarCometChat.opt.useSsound)*/ $(".StarCometChat-msg-audio")[0].play();
    })

    // Обработка сообщений о том что кто то прочитал наше сообщение
    CometServer().subscription("msg.readMessage", function(event)
    {
        console.log("readMessage",event.data)
        if(StarCometChat.interlocutor_id == event.data.to_user_id)
        {
            $('.StarCometChat-message-is-read').addClass('StarCometChat-message-read');
        }
    })
}
 
/**
 * Переключает состояние между "Использовать звук" и "не использовать"
 * @private
 */
StarCometChat.toggleSound = function()
{
    StarCometChat.opt.useSsound = !StarCometChat.opt.useSsound
    window.localStorage['StarCometChat.useSsound'] = StarCometChat.opt.useSsound/1
    if(!StarCometChat.opt.useSsound)
    {
        $(".toggleSoundBtn").attr("src", StarCometChat.opt.home_dir+"/img/soundNone.png");
    }
    else
    {
        $(".toggleSoundBtn").attr("src", StarCometChat.opt.home_dir+"/img/sound.png");
    }
}

/**
 * возвращает кол-во непрочитанных сообщений
 * @returns {Number}
 * @public
 */
StarCometChat.countNewMessagesSum = function()
{
    return StarCometChat.newMessagesSum;
}

/**
 * возвращает кол-во непрочитанных сообщений от пользователя user_id
 * @param {int} user_id Идентификатор пользователя
 * @returns {Number}
 * @public
 */
StarCometChat.countNewMessages = function(user_id)
{
    if(StarCometChat.opt.data.contacts && StarCometChat.opt.data.contacts[user_id])
    {
        return StarCometChat.opt.data.contacts[user_id].newMessages;
    }
}

/**
 * Закрывает окно чатаa
 * @private
 */
StarCometChat.closeChat = function()
{
    $(".StarCometChat-shadow").hide();
    $(".StarCometChat-shadow-load").hide();
    $('.StarCometChat').hide();
    
    if(StarCometChat.opt.onCloseChat)
    {
        StarCometChat.opt.onCloseChat()
    }
}

/**
 * @private
 */
StarCometChat.updateNewMessagesSum = function()
{
    if(StarCometChat.newMessagesSum < 0)
    {
        StarCometChat.newMessagesSum = 0
    }

    if(StarCometChat.newMessagesSum == 0)
    {
        $('.StarCometChat-all-message').hide();
    }
    else
    {
        $('.StarCometChat-all-message').html(StarCometChat.newMessagesSum).show()
        $('.StarCometChat-tabShowLoc .StarCometChat-all-message').hide()
        $('.StarCometChat-tabShowSearch .StarCometChat-all-message').hide()
    }
}

/**
 * Поиск по контактам
 * @private
 */
StarCometChat.searchInContacts = function()
{
    if(StarCometChat.searchInContacts_TimeoutId)
    {
        clearTimeout(StarCometChat.searchInContacts_TimeoutId)
        StarCometChat.searchInContacts_TimeoutId = false;
    }

    StarCometChat.searchInContacts_TimeoutId = setTimeout(function()
    {
        var searchStr = $('.StarCometChat-searchInContacts').val()
        console.log(searchStr)

        if(!searchStr || searchStr.length === 0)
        {
            $(".StarCometChat-list").show()

            // Если мы на вкладке заблокированных пользователей то прячем обычных и избранных пользователей
            $(".StarCometChat-tabShowLoc .StarCometChat-favorite-user").hide()
            $(".StarCometChat-tabShowLoc .StarCometChat-base-user").hide()
            return;
        }
        $(".StarCometChat-list").removeClass("StarCometChat-search-result")


        var reg = new RegExp("^"+searchStr, "i")
        for(var i in StarCometChat.opt.data.contacts)
        {
            var val = StarCometChat.opt.data.contacts[i]
            if(!val.name || !reg.test(val.name))
            {
                continue;
            }
            console.log(val, reg.test(val.name))

            // Результат подходит под условие поиска
            $('.StarCometChat-userId-'+val.user_id).addClass("StarCometChat-search-result")
        }

        $(".StarCometChat-list").hide()
        $(".StarCometChat-search-result").show()

        // Если мы на вкладке заблокированных пользователей то прячем обычных и избранных пользователей
        $(".StarCometChat-tabShowLoc .StarCometChat-favorite-user").hide()
        $(".StarCometChat-tabShowLoc .StarCometChat-base-user").hide()
    }, 200)

}

/**
 * Блокирует или разблокирует пользователя с которым открыт диалог в данный момент
 * @private
 */
StarCometChat.toggleBlockUser = function()
{
    var user_id = StarCometChat.interlocutor_id

    var setBlock = 2;
    if(!$('.StarCometChat-userId-'+user_id).hasClass('StarCometChat-loc-user'))
    {
        // Пользователь не был до этого заблокирован и мы его блокируем
        $('.StarCometChat-userId-'+user_id).addClass('StarCometChat-loc-user').removeClass('StarCometChat-favorite-user').removeClass('StarCometChat-base-user')
        StarCometChat.tabShowLoc();
        $(".toggle-block-user-btn span").html('Unlock')
    }
    else
    {
        // Пользователь был до этого заблокирован и мы его разблокируем
        setBlock = 0
        $('.StarCometChat-userId-'+user_id).removeClass('StarCometChat-loc-user').addClass('StarCometChat-base-user')
        StarCometChat.tabShowBase();
        $(".toggle-block-user-btn span").html('Block')
    }

    StarCometChat.opt.data.contacts[user_id].relation_type = setBlock;



    jQuery.ajax({
        url: StarCometChat.opt.home_dir+"/blockUser.php",
        type: "POST",
        dataType:'json',
        data:"block_user_id="+encodeURIComponent(user_id)+"&block="+setBlock+"&user_id="+encodeURIComponent(StarCometChat.opt.user_id)+"&user_key="+encodeURIComponent(StarCometChat.opt.user_key),
        success: function(data)
        {
            console.log(data)
            if(data.error)
            {
                setTimeout(function(){
                    alert(data.error)
                    StarCometChat.toggleBlockUser()
                }, 500)
            }
        }
    });

    StarCometChat.interlocutor_id = 0
    StarCometChat.openDialog(user_id)
}

StarCometChat.toggleFavoritUser = function()
{ 
    var user_id = StarCometChat.interlocutor_id

    var setBlock = 1;
    if(!$('.StarCometChat-userId-'+user_id).hasClass('StarCometChat-favorite-user'))
    {
        // Пользователь не был до этого заблокирован и мы его блокируем
        $('.StarCometChat-userId-'+user_id).addClass('StarCometChat-favorite-user').removeClass('StarCometChat-loc-user').removeClass('StarCometChat-base-user')
        StarCometChat.tabShowFavorite(); 
    }
    else
    {
        // Пользователь был до этого заблокирован и мы его разблокируем
        setBlock = 0
        $('.StarCometChat-userId-'+user_id).removeClass('StarCometChat-favorite-user').addClass('StarCometChat-base-user').removeClass('StarCometChat-loc-user')
        StarCometChat.tabShowBase(); 
    }

    StarCometChat.opt.data.contacts[user_id].relation_type = setBlock;



    jQuery.ajax({
        url: StarCometChat.opt.home_dir+"/blockUser.php",
        type: "POST",
        dataType:'json',
        data:"block_user_id="+encodeURIComponent(user_id)+"&block="+setBlock+"&user_id="+encodeURIComponent(StarCometChat.opt.user_id)+"&user_key="+encodeURIComponent(StarCometChat.opt.user_key),
        success: function(data)
        {
            console.log(data)
            if(data.error)
            {
                setTimeout(function(){ 
                    
                }, 500)
            }
        }
    });

    StarCometChat.interlocutor_id = 0
    StarCometChat.openDialog(user_id)
}

/**
 * Отправляет жалобу на пользователя
 * @private
 */
StarCometChat.sendAbuse = function()
{
    var user_id = StarCometChat.interlocutor_id

    if(!confirm("Are you sure you want to report a user?"))
    {
        return;
    }

    jQuery.ajax({
        url: StarCometChat.opt.home_dir+"/abuseToUser.php",
        type: "POST",
        /*dataType:'json',*/
        data:"abuse_to="+encodeURIComponent(user_id)+"&user_id="+encodeURIComponent(StarCometChat.opt.user_id)+"&user_key="+encodeURIComponent(StarCometChat.opt.user_key),
        success: function()
        {
            alert("Accepted")
        }
    });
}

/**
 * Выполняет подписку на статус пользователя для показа online или offline
 * @param {int} userId
 * @private
 */
StarCometChat.addSubscriptionToUserStatus = function(userId)
{
    // Подписываемся на информацию о статусе пользователя online
    CometServer().subscription("user_status_"+userId+".online", function(event)
    {
        var user_id = event.server_info.pipe.replace(/[^0-9]/g, "")
        $(".StarCometChat-userId-"+user_id).addClass("StarCometChat-online-user")

        $(".StarCometChat-right-top").addClass("StarCometChat-online-user")

        if(StarCometChat.interlocutor_id == user_id)
        {
            $(".StarCometChat-right-top").addClass("StarCometChat-online-user");
        }
    })

    // Подписываемся на информацию о статусе пользователя offline
    CometServer().subscription("user_status_"+userId+".offline", function(event)
    {
        var user_id = event.server_info.pipe.replace(/[^0-9]/g, "")
        $(".StarCometChat-userId-"+user_id).removeClass("StarCometChat-online-user")

        if(StarCometChat.interlocutor_id == user_id)
        {
            var time = new Date()
            $('.StarCometChat-lastOnlineTimeTo').html("last visit: "+StarCometChat.getTimeString(time.getTime()))
            $(".StarCometChat-right-top").removeClass("StarCometChat-online-user");
        }
    })
}

/**
 * Возвращает html контакта для сипска контактов
 * @param object contact
 * @returns {String}
 * @private
 */
StarCometChat.getContactHtml = function(contact)
{
    var newMessages = "0";
    var showNewMessages = 'display:none';
    if(contact.newMessages > 0 )
    {
        newMessages = contact.newMessages;
        showNewMessages = ''
    }

    var online = ""
    if(contact.last_online_time == 0)
    {
        online = "StarCometChat-online-user";
    }

    var info = [];
    if(contact.age > 0)
    {
        info.push(contact.age+' years old');
    }
    
    if(contact.city && contact.city.length > 0)
    {
        info.push(contact.city);
    }
    
    return '<div class="StarCometChat-list StarCometChat-'+relation_types[contact.relation_type]+'-user StarCometChat-userId-'+contact.user_id+' '+online+'"  onclick="StarCometChat.openDialog('+contact.user_id+')" >\
        <div class="StarCometChat-count-message" style="'+showNewMessages+'" >'+newMessages+'</div>\
        <div class="StarCometChat-list-avatar">\
            <div class="StarCometChat-statusTo StarCometChat-statusTo-point"></div>\
            <img src="'+StarCometChat.opt.user_avatar_url_tpl+contact.avatar_url+'">\
        </div>\
        <div>'+contact.name+'</div>\
        <div>'+info.join(" | ")+'</div>\
    </div>'
}

/**
 * Увеличивает сщётчик новых сообщений в списке контактов
 * @param int from_user_id
 * @private
 */
StarCometChat.incrementNewMessage = function(from_user_id)
{
    var count = parseInt($('.StarCometChat-userId-'+from_user_id+' .StarCometChat-count-message').show().html());
    $('.StarCometChat-userId-'+from_user_id+' .StarCometChat-count-message').html(count+1)

    if(!StarCometChat.opt.data.contacts[from_user_id].newMessages)
    {
        StarCometChat.opt.data.contacts[from_user_id].newMessages = 1;
    }
    else
    {
        StarCometChat.opt.data.contacts[from_user_id].newMessages += 1;
    }


    StarCometChat.newMessagesSum += 1
    StarCometChat.updateNewMessagesSum()
}

/**
 * Сбрасывает сщётчик новых сообщений в списке контактов
 * @param int from_user_id
 * @private
 */
StarCometChat.resetNewMessage = function(from_user_id)
{
    StarCometChat.opt.data.contacts[from_user_id].newMessages = 0;
    StarCometChat.newMessagesSum -= $('.StarCometChat-userId-'+from_user_id+' .StarCometChat-count-message').html()/1
    StarCometChat.updateNewMessagesSum()

    $('.StarCometChat-userId-'+from_user_id+' .StarCometChat-count-message').hide().html('0');
}

/**
 * @private
 */
StarCometChat.getUserInfo = function(user_id)
{
    return StarCometChat.opt.data.contacts[StarCometChat.interlocutor_id];
}

StarCometChat.getTimeString = function(unixTime)
{
    var m = moment(unixTime, "x");

    if(m.isAfter(moment().subtract(1, 'day')))
    {
        return m.format("HH:mm")
    }
    else if(m.isAfter(moment().startOf('year')))
    {
        return m.format("HH:mm DD.MM")
    }

    return m.format("DD-MM-YYYY HH:mm")
}


StarCometChat.translateMessage = function(message_id, language)
{
    jQuery.ajax({
        url: StarCometChat.opt.home_dir+"/translateMessage.php",
        type: "POST",
        data:"message_id="+message_id+"&language="+language+"&user_id="+encodeURIComponent(StarCometChat.opt.user_id)+"&user_key="+encodeURIComponent(StarCometChat.opt.user_key),
        success: function(data)
        {
            var data = JSON.parse(data)
            $(".StarCometChat-messge-"+message_id+" .StarCometChat-message-text")
                    .append("<div class='StarCometChat-message-translate StarCometChat-message-lang-"+language+"' >" + data.text
                    + '<div class="StarCometChat-Ylogo" ><a href="http://translate.yandex.ru/">Translated by Yandex.Translator</a></div></div>');
            $('.StarCometChat-right-chat').data("plugin_tinyscrollbar").update("relative");
        }
    });
}

/**
 * @private
 */
StarCometChat.getMessageHtml = function(message)
{
    var text = message.message.replace(/\n/mg, "<br>")
                              .replace(/\[\[img=([A-z0-9\._]+)\]\]/mg, "<a href='"+StarCometChat.opt.file_dir+"/$1' target='_blank' ><img src='"+StarCometChat.opt.file_dir+"/$1'></a>")


    var langHtml = "";
    if(message.id > 0)
    {
        for(var i in StarCometChat.opt.langs)
        {
            langHtml += "<li><a href='#' onclick=\"$(this).hide(); StarCometChat.translateMessage("+message.id+", '"+StarCometChat.opt.langs[i].name+"'); return false;\" >"+StarCometChat.opt.langs[i].textName+"</a></li>";
        }

        var langHtml =  '<div id="StarCometChat-translate-button-'+message.id+'" class="StarCometChat-wrapper-dropdown" tabindex="1" onclick="$(this).toggleClass(\'StarCometChat-wrapper-active\'); event.stopPropagation();">\
                            <span title="translate" >Tr</span>\
                            <ul class="StarCometChat-dropdown">' + langHtml + '</ul>\
                        </div>';
    }
    if(message.from_user_id && message.from_user_id == StarCometChat.interlocutor_id)
    {
        return '<div class="StarCometChat-messge StarCometChat-messge-'+message.id+'">\
                    <img src="'+StarCometChat.opt.user_avatar_url_tpl+StarCometChat.getUserInfo(StarCometChat.interlocutor_id).avatar_url+'" class="StarCometChat-message-avatar" >\
                    <div>\
                        <div class="StarCometChat-message-pic-left"></div>\
                        <div class="StarCometChat-message-text">\
                            <div class="StarCometChat-message-original" >'+text+'</div>\
                        </div>\
                        <div class="StarCometChat-message-time" data-time="'+message.time+'">'+StarCometChat.getTimeString(message.time)+'</div>\
                        '+langHtml+'\
                    </div>\
                </div>';
    }
    else
    {
        var isRead = "";
        if(parseInt(message.read_time) > 0)
        {
            isRead = "StarCometChat-message-read";
        }
        
        
        return  '<div class="StarCometChat-messge-my StarCometChat-messge-'+message.id+'">\
                    '+langHtml+'\
                    <div class="StarCometChat-message-is-read '+isRead+'" ></div>\
                    <div class="StarCometChat-message-time uptodate" data-time="'+message.time+'">'+StarCometChat.getTimeString(message.time)+'</div>\
                    <div class="StarCometChat-message-text">'+text+'</div>\
                    <div class="StarCometChat-message-pic-right"></div>\
                </div>'//+langHtml;
    }
}


/**
 * @private
 */
StarCometChat.openDialogPage = function(page)
{
    $('.StarCometChat-loadLastPage').hide();

    if(StarCometChat.interlocutor_id < 0)
    {
        return;
    }
    
    jQuery.ajax({
        url: StarCometChat.opt.home_dir+"/getLastMessage.php",
        type: "POST",
        data:"user_id_to="+StarCometChat.interlocutor_id+"&page="+page+"&user_id="+encodeURIComponent(StarCometChat.opt.user_id)+"&user_key="+encodeURIComponent(StarCometChat.opt.user_key),
        success: function(data)
        {
            var data = JSON.parse(data)
            console.log(data)

            var html = "";
            for(var i in data.history)
            {
                html = StarCometChat.getMessageHtml(data.history[i]) + html
            }
            if(data.history.length == StarCometChat.opt.page_size)
            {
                html = "<div class='StarCometChat-loadLastPage' onclick='StarCometChat.openDialogPage("+(page+1)+")'  >Load previous messages</div>" + html;
            }

            $('.StarCometChat-message-list').html(html + $('.StarCometChat-message-list').html());
            $('.StarCometChat-right-chat').data("plugin_tinyscrollbar").update("top");
            StarCometChat.bindUpdateScrollForImgLoad("top")
        }
    });
}

/**
 * Открывает диалог с пользователем user_id
 * @param object user_id
 * @public
 */
StarCometChat.openDialog = function(user_id)
{
    console.log("StarCometChat.openDialog("+user_id+")")
    if(!user_id)
    {
        // Открыть первый диалог в списке
        user_id = StarCometChat.first_dialog;
    }
    
    if(user_id == StarCometChat.opt.user_id)
    {  
        console.error("Не надо писать самому себе"); 
        // Открыть первый диалог в списке
        user_id = StarCometChat.first_dialog;
    }

    if(StarCometChat.initSuccess !== true)
    {
        StarCometChat.openDialog_id = user_id;
        return;
    }
    StarCometChat.openDialog_id = 0;

    $('.StarCometChat').show();  
    $(".StarCometChat-shadow").show();

    if(StarCometChat.interlocutor_id == user_id)
    {
        // Диалог уже открыт.
        return;
    }

    StarCometChat.interlocutor_id = user_id
    $('.StarCometChat-selected-user').removeClass('StarCometChat-selected-user')
    $('.StarCometChat-userId-'+user_id).addClass('StarCometChat-selected-user')

    $(".StarCometChat-right-top").animate({opacity:0})
    $(".StarCometChat-message-list").animate({opacity:0})
    $(".StarCometChat-input-holder").animate({opacity:0}, function(){  $(".StarCometChat-input-holder").hide() })

    if(StarCometChat.opt.data.error)
    {
        $(".StarCometChat-not-paid-alert").show().html(StarCometChat.opt.data.error)
        StarCometChat.chatUpdateSize()
        return;
    }
    
    if(StarCometChat.opt.data.contacts[user_id] && StarCometChat.opt.data.contacts[user_id].relation_type == 2)
    {
        $(".StarCometChat-block-alert").show()
        StarCometChat.chatUpdateSize()
        return;
    }
    $(".StarCometChat-block-alert").hide()

    if(StarCometChat.opt.data.myInfo.error)
    {
        $(".StarCometChat-not-paid-alert").show().html(StarCometChat.opt.data.myInfo.error)
        StarCometChat.chatUpdateSize()
        return;
    }
    $(".StarCometChat-not-paid-alert").hide()

    if(StarCometChat.interlocutor_id < 0)
    {
        return;
    }
    
    jQuery.ajax({
        url: StarCometChat.opt.home_dir+"/getLastMessage.php",
        type: "POST",
        data:"user_id_to="+user_id+"&user_id="+encodeURIComponent(StarCometChat.opt.user_id)+"&user_key="+encodeURIComponent(StarCometChat.opt.user_key),
        success: function(data)
        {
            $('.StarCometChat').show();
            var data = JSON.parse(data)
            console.log(data)

            if(data.new_contact !== false && !StarCometChat.opt.data.contacts[user_id] )
            {
                // Человек ещё не в списке контактов
                StarCometChat.opt.data.contacts[user_id] = {
                    relation_type:0, 
                    newMessages:1,
                }
 
                for(var key in data.new_contact)
                {
                    StarCometChat.opt.data.contacts[user_id][key] = data.new_contact[key];
                }
 
                var html = StarCometChat.getContactHtml(StarCometChat.opt.data.contacts[user_id])

                $('.StarCometChat-contact-list').html(html + $('.StarCometChat-contact-list').html());
                StarCometChat.tabShowBase();
                $('.StarCometChat-userId-'+user_id).addClass('StarCometChat-selected-user')
            }

            $('.StarCometChat-nameTo').html(StarCometChat.opt.data.contacts[user_id].name)

            if(StarCometChat.opt.data.contacts[user_id].relation_type == 0)
            {
                $(".toggle-block-user-btn span").html('Block')
            }
            else
            {
                $(".toggle-block-user-btn span").html('Unblock')
            }
            $(".StarCometChat-user-url-page").attr("href", StarCometChat.opt.user_url_tpl+StarCometChat.opt.data.contacts[user_id].login)
            $(".StarCometChat-avatarTo").attr("src", StarCometChat.opt.user_avatar_url_tpl+StarCometChat.opt.data.contacts[user_id].avatar_url)

            var html = "";
            for(var i in data.history)
            {
                html = StarCometChat.getMessageHtml(data.history[i]) + html
            }

            if(data.history.length == StarCometChat.opt.page_size)
            {
                html = "<div class='StarCometChat-loadLastPage' onclick='StarCometChat.openDialogPage(1)'  >Load previous messages</div>" + html;
            }


            $('.StarCometChat-message-list').html(html);
            $('.StarCometChat-right-chat').data("plugin_tinyscrollbar").update("bottom");
            StarCometChat.bindUpdateScrollForImgLoad()

            if(data.last_online_time/1 > 0)
            {
                $(".StarCometChat-right-top").removeClass("StarCometChat-online-user");
                $('.StarCometChat-lastOnlineTimeTo').html("last visit: "+StarCometChat.getTimeString(data.last_online_time*1000))
            }
            else if(data.last_online_time/1 < 0)
            {
                $(".StarCometChat-right-top").removeClass("StarCometChat-online-user");
                $('.StarCometChat-lastOnlineTimeTo').html("")
            }
            else
            {
                $(".StarCometChat-right-top").addClass("StarCometChat-online-user");
            }

            if(!StarCometChat.opt.data.myInfo.error)
            {
                StarCometChat.resetNewMessage(user_id)
                $(".StarCometChat-right-top").animate({opacity:1})
                $(".StarCometChat-message-list").animate({opacity:1})

                $(".StarCometChat-input-holder").show().animate({opacity:1}, function(){  $(".StarCometChat-input-holder").show(); })
            }
            else
            {
                $(".StarCometChat-not-paid-alert").show().html(StarCometChat.opt.data.myInfo.error)
            }
            StarCometChat.chatUpdateSize()
        }
    });
}

/**
 * @private
 */
StarCometChat.uploadFile = function(event)
{
    StarCometChat.uploadFilesList = undefined;

    if(!/^image\/(jpeg|png|jpg)$/i.test(event.target.files[0].type))
    {
        // @todo выводить предупреждение
        // не верный тип файла
        console.log("не верный тип файла " + event.target.files[0].type)
        $(".StarCometChat-file-attachment").html("Invalid file type").animate({'opacity': 1})
        return;
    }

    if( event.target.files[0].size > 1024*1024*4)
    {
        // @todo выводить предупреждение
        // файл слишком большой
        console.log("файл слишком большой " + event.target.files[0].size)
        $(".StarCometChat-file-attachment").html("File too large").animate({'opacity': 1})
        return;
    }
    StarCometChat.uploadFilesList = event.target.files[0]
    $(".StarCometChat-file-attachment").html("File attached").animate({'opacity': 1})
}

/**
 * @private
 */
StarCometChat.sendMessage = function()
{
    if(!StarCometChat.interlocutor_id)
    {
        // Диалог не открыт.
        console.log("Диалог не открыт.")
        return;
    }

    var text = $('.StarCometChat-input-message-text').val();

    var fd = new FormData();
    fd.append('user_id_to', StarCometChat.interlocutor_id);
    fd.append('message', text);
    fd.append('user_id', StarCometChat.opt.user_id);
    fd.append('user_key', StarCometChat.opt.user_key);

    if(StarCometChat.uploadFilesList)
    {
        console.log("файл" , StarCometChat.uploadFilesList)
        fd.append('img', StarCometChat.uploadFilesList);
        StarCometChat.uploadFilesList = undefined; 
        $(".StarCometChat-file-attachment").animate({'opacity': 0})
    }

    $('.StarCometChat-input-message-text').val('');
    jQuery.ajax({
        url: StarCometChat.opt.home_dir+"/sendMessage.php",
        type: "POST",
        data: fd,
        processData: false,
        contentType: false,
        success: function(data)
        {
            var data = JSON.parse(data)

            var dateTex = new Date();
            var html = StarCometChat.getMessageHtml({message:data.message_text, time:dateTex.getTime()})
            $('.StarCometChat-message-list').html( $('.StarCometChat-message-list').html() + html);

            StarCometChat.bindUpdateScrollForImgLoad()

            $('.StarCometChat-right-chat').data("plugin_tinyscrollbar").update("bottom");
            console.log(data)
        }
    });
}

StarCometChat.keydownSendMessage = function(event)
{
    if (event.ctrlKey && event.keyCode === 13)
    {
        StarCometChat.sendMessage()
    }
}

/**
 * Обновляет скрол после того как догрузятся фотки вставленные в нутрь сообщений
 * @private
 */
StarCometChat.bindUpdateScrollForImgLoad = function(scrollPosition)
{
    if(!scrollPosition)
    {
        scrollPosition = "bottom"
    }

    $('.StarCometChat-message-list img').bind("load", function()
    {
        // Подписываемся на событие onload для изображений чтоб потом обновить скрол, так как после загрузки изображения высота меняется и надо обновить область проккрутки.
        $('.StarCometChat-right-chat').data("plugin_tinyscrollbar").update(scrollPosition);
    })
}

/**
 * @public
 */
StarCometChat.tabShowBase = function()
{
    StarCometChat.updateNewMessagesSum();
    $('.StarCometChat-left-tabs').show();
    $('.StarCometChat-left-search').hide();
    $('.StarCometChat-picLine .StarCometChat-select').removeClass('StarCometChat-select');
    $('.StarCometChat-pic1').parent().addClass('StarCometChat-select');

    $('.StarCometChat-base-user').show();
    $('.StarCometChat-favorite-user').hide();
    $('.StarCometChat-loc-user').hide();
    $('.StarCometChat-list-holder').data("plugin_tinyscrollbar").update("bottom");

    $('.StarCometChat-contact-list')
            .addClass('StarCometChat-tabShowBase')
            .removeClass('StarCometChat-tabShowLoc')
            .removeClass('StarCometChat-tabShowFavorite')
            .removeClass('StarCometChat-tabShowSearch')
    
    $('.StarCometChat-left-tab').removeClass('StarCometChat-left-tab-select');
    $('.StarCometChat-left-base-tab').addClass('StarCometChat-left-tab-select');
    
    StarCometChat.chatUpdateSize()
}

/**
 * @public
 */
StarCometChat.tabShowLoc = function()
{
    StarCometChat.updateNewMessagesSum();
    $('.StarCometChat-left-tabs').hide();
    $('.StarCometChat-left-search').show();
    $('.StarCometChat-picLine .StarCometChat-select').removeClass('StarCometChat-select');
    $('.StarCometChat-pic3').parent().addClass('StarCometChat-select');

    $('.StarCometChat-favorite-user').hide();
    $('.StarCometChat-base-user').hide();
    $('.StarCometChat-loc-user').show();
    $('.StarCometChat-list-holder').data("plugin_tinyscrollbar").update("bottom");

    $('.StarCometChat-contact-list')
            .addClass('StarCometChat-tabShowLoc')
            .removeClass('StarCometChat-tabShowBase')
            .removeClass('StarCometChat-tabShowFavorite')
            .removeClass('StarCometChat-tabShowSearch')
    StarCometChat.chatUpdateSize()
}

/**
 * @public
 */
StarCometChat.tabShowFavorite = function()
{
    $('.StarCometChat-list').hide();
    $('.StarCometChat-favorite-user').show();
    $('.StarCometChat-list-holder').data("plugin_tinyscrollbar").update("bottom");

    $('.StarCometChat-contact-list')
            .addClass('StarCometChat-tabShowFavorite')
            .removeClass('StarCometChat-tabShowBase')
            .removeClass('StarCometChat-tabShowLoc')
            .removeClass('StarCometChat-tabShowSearch')
    
    $('.StarCometChat-left-tab').removeClass('StarCometChat-left-tab-select');
    $('.StarCometChat-left-favorite-tab').addClass('StarCometChat-left-tab-select');
    
    StarCometChat.chatUpdateSize()
}

/**
 * @public
 */
StarCometChat.tabShowSearch = function()
{
    StarCometChat.updateNewMessagesSum();
    $('.StarCometChat-left-tabs').hide();
    $('.StarCometChat-left-search').show();
    $('.StarCometChat-picLine .StarCometChat-select').removeClass('StarCometChat-select');
    $('.StarCometChat-pic2').parent().addClass('StarCometChat-select');

    $('.StarCometChat-base-user').show();
    $('.StarCometChat-loc-user').show();
    $('.StarCometChat-list-holder').data("plugin_tinyscrollbar").update("bottom");

    $('.StarCometChat-contact-list')
            .addClass('StarCometChat-tabShowSearch')
            .removeClass('StarCometChat-tabShowBase')
            .removeClass('StarCometChat-tabShowLoc')
            .removeClass('StarCometChat-tabShowFavorite')
    StarCometChat.chatUpdateSize()
}

/**
 * @public
 */
StarCometChat.toggleLeftColumn = function()
{
    $('.StarCometChat-holder').toggleClass('StarCometChat-mobile');

    if(!$('.StarCometChat-holder').hasClass('StarCometChat-mobile'))
    {
        $('.StarCometChat-list-holder').data("plugin_tinyscrollbar").update("bottom");
    }
    StarCometChat.chatUpdateSize()
}

/**
 * @public
 */
StarCometChat.toggleMobileMode = function()
{
    $('.StarCometChat').toggleClass('StarCometChat-width-min')
    StarCometChat.chatUpdateSize()
}

/**
 * @private
 * @param int deltaSize То насколько надо изменить высоту чата
 */
StarCometChat.chatResize = function(deltaSize)
{
    if(deltaSize > 260)
    {
        deltaSize = 260;
    }
    else if(deltaSize < 0)
    {
        deltaSize = 0;
    }

    $('.StarCometChat-right-chat').css({'height':486-deltaSize});
    $('.StarCometChat-list-holder .viewport').css({'height':527-deltaSize});
    $('.StarCometChat-list-show-btn').css({'margin-top':-300+deltaSize/4});
    $('.StarCometChat-list-hide-btn').css({'margin-top':-220+deltaSize/4});

    $('.StarCometChat-right-chat .viewport').css({'height':486-deltaSize});

    $('.StarCometChat-right-chat').data("plugin_tinyscrollbar").update("bottom");
    $('.StarCometChat-list-holder').data("plugin_tinyscrollbar").update("top");
}

/**
 * обновляет высоту чата чтоб он вмещался в окно браузера
 * @private
 */
StarCometChat.chatUpdateSize = function()
{
    StarCometChat.chatResize(840 - $(window).height());

    if($(window).width() < 990 && !$('.StarCometChat').hasClass('StarCometChat-width-min'))
    {
        $('.StarCometChat').addClass('StarCometChat-width-min')

        $('.StarCometChat-holder').addClass('StarCometChat-mobile');
        $('.StarCometChat-list-holder').data("plugin_tinyscrollbar").update("bottom");
        StarCometChat.chatUpdateSize()

        StarCometChat.chatUpdateSize()
    }

    if($(window).width() > 990 && $('.StarCometChat').hasClass('StarCometChat-width-min'))
    {
        $('.StarCometChat').removeClass('StarCometChat-width-min')

        $('.StarCometChat-holder').removeClass('StarCometChat-mobile');
        $('.StarCometChat-list-holder').data("plugin_tinyscrollbar").update("bottom");
        StarCometChat.chatUpdateSize()
    }
}
