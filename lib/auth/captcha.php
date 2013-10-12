<?php

include dirname(__FILE__) . "/../common.php";

$salt = "93hc9c1cc02bhc63oxb0dodt7j";

session_init();
set_secret();

if (isset($_REQUEST['mode'])) {
    if ($_REQUEST['mode'] === 'logout') {
        reset_session();
        redirect_to_index();
    }
}


/* Public API */

function set_secret() {
    global $_SESSION;
    if (isset($_SESSION["secret"]) && $_SESSION["secret"] != "") {
        return;
    }
    $_SESSION["secret"] = sprintf("%03.0f", rand(0, 999));
}

function auth_user_logged_in() {
    global $SALT, $_SESSION;

    if (!isset($_SESSION["secret"])) {
        set_secret();
    }

    if (!isset($_SESSION["answer"])) {
        $_SESSION["answer"] = 'fail';
    }

    if ($_SESSION["secret"] == $_SESSION["answer"]) {
        return true;
    } else {
        return false;
    }
}

function auth_get_username() {
    global $_SESSION;
    if ( auth_user_logged_in() ) {
        $username = $_SESSION["username"];
        return $username;
    } else {
        return null;
    }
}


/* Private functions */

/**
* Generates version 4 UUID: random
*/
function uuid4() {
    if (!function_exists('uuid_create')){
        return false;
    }

    uuid_create(&$context);

    uuid_make($context, UUID_MAKE_V4);
    uuid_export($context, UUID_FMT_STR, &$uuid);
    return trim($uuid);
}

function reset_session() {
        global $_SESSION;
        session_destroy();
        session_start();

        $_SESSION["answer"] = "";
        $_SESSION["username"] = "";
        set_secret();
}

function session_init() {
    session_start();

    if(isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $_SESSION["answer"] = $password;
        $_SESSION["username"] = $username;

        redirect_to_index();
    }
}

function redirect_to_index() {
        header( 'Location: index.php?chatname=' . get_chatname() );
        print('<a href="index.php" target="_top">Back to main page</a>');
        exit();
}

function get_secret() {
    return $_SESSION["secret"];
}

?>

