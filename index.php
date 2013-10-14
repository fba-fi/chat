<?php

include dirname(__FILE__) . "/lib/common.php";
include dirname(__FILE__) . "/lib/db/textfile.php";
include dirname(__FILE__) . "/lib/auth/captcha.php";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <!-- Mobile viewport optimized -->
    <meta name="HandheldFriendly" content="true" />
    <meta name="viewport" content="width=device-width, initial-scale=1"/> 

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <link rel="stylesheet" type="text/css" href="static/jquery-ui-1.10.3.custom/css/smoothness/jquery-ui-1.10.3.custom.min.css"/>
    <script type="text/javascript" src="static/jquery-ui-1.10.3.custom/js/jquery-1.9.1.js"></script>
    <script type="text/javascript" src="static/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.min.js"></script>
    <script type="text/javascript" src="static/chat.js"></script>

    <link href="static/style.css" rel="stylesheet" type="text/css">

</head>
<body>

<div id="wrapper">

    <div id="navi">

        <div class="navi chatlinks">

            <a href="http://www.purjelautaliitto.fi/" target="_top">SPLL-ETUSIVU</a>

            <a  class="<? echo class_page_selected("surf") ?>"
                href="/chat/surf"
                target="_top">Purjelautailu</a>

            <a  class="<? echo class_page_selected("kite") ?>"
                href="/chat/kite" 
                target="_top">Leijalautailu</a>

                <div class="logout">
                    <?  if (auth_get_username()) { ?>
                    <span class="username"><? print auth_get_username() ?></span>   
                    <a href="index.php?chatname=<? echo get_chatname() ?>&mode=logout">Logout</a>
                    <? } ?>
                </div>

        </div>

        <div class="clear"></div>

    </div>

    <form>
        <input type="hidden"
               id="currentchatname"
               name="chatname"
               value="<? echo get_chatname() ?>"/>
    </form>


    <div id="form_container">

        <div class="halfwidth formarea">

            <? if (auth_user_logged_in()) { ?>

                <form id="messageform">
                    <input type="hidden" id="chatname" name="chatname"
                            value="<? echo get_chatname() ?>"/>

                    <input type="hidden" id="username" name="username"
                            value="<? echo auth_get_username() ?>"/>
                    <p>
                    <textarea id="message" name="message" wrap rows=4></textarea>
                    </p>
                </form>

                <p>
                    <a href="#" id="sendmessage" class="button">Lähetä</a>
                    <a href="#" id="clearmessage" class="button">Tyhjennä</a>
                    <a href="#" id="deletelatest" class="button">Poista viimeinen viesti</a>
                </p>

            <? } else { ?>

                <form action="index.php" method="post" id="login">

                        <p>Oikea vastaus bottivarmistukseen on <? echo get_secret() ?>

                        <dl class="login">
                            <dt><label for="username">Nimimerkki:</label></dt>
                            <dd><input type="text" tabindex="1" name="username" id="username" size="25" value="<? echo auth_get_username() ?>" class="inputbox autowidth" /></dd>
                            <dt><label for="password">Bottivarmistus:</label></dt>
                            <dd><input type="text" tabindex="2" id="password" name="password" size="25" value="" class="inputbox autowidth" /></dd>
                            <dt><label>&nbsp;</label></dt>
                            <dd><input type="submit" name="login" tabindex="6" value="Kirjaudu sisään" class="button1" /></dd>
                        </dl>


                        <input type="hidden" name="redirect" value="/kite/keskustelu/index.php" />
                        <input type="hidden" name="chatname" value="<? echo get_chatname() ?>" />

                </form>

            <? } ?>

        </div>

        <div class="halfwidth disclaimer">

            <h1>SPLL <? echo get_chatname() ?>-keskustelua </h1>

            <p>Tämä on SPLL:n kite-jaoksen keskustelupalsta. Suomen Purjelautailuliitto ry. ei
                ota vastuuta keskustelupalstan kirjoituksista, vaan jokainen palstalle 
                kirjoittava on vastuussa omista kirjoituksistaan.</p>

            <p><a href="http://pulinat.purjelautaliitto.fi/kitebb/">Kite Bulletin Board</a> /
                <a href="http://www.purjelautaliitto.fi/kite/doku.php?id=ohjeet:faq">FAQ - Kysytyimmät KITE-kysymykset</a> /
                <a href="http://www.purjelautaliitto.fi/kite/doku.php?id=ohjeet:sanasto">Leijalautailusanasto</a></p>

        </div>

        <div class="clear"></div>

    </div> <!-- form_container -->


    <div id="chat_container"> <div id="chat"></div> </div>

</div>

<div id="dialog" title="Basic dialog" style="display:none">
  <p>This is the default dialog which is useful for displaying information. The dialog window can be moved, resized and closed with the 'x' icon.</p>
</div>

</body>
</html>
