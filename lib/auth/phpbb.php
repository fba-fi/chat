<?php

/* phpBB authentication backend for SPLL-chat */

if ( gethostname() === 'purjelautaliitto.fi' ) {
    $phpbb_root_path = '/home/purje719/public_html/pulinat.purjelautaliitto.fi/surfbb/';
} elseif ( $chat_name === 'kite' ) {
    $phpbb_root_path = '/home/purje719/public_html/pulinat.purjelautaliitto.fi/kitebb/';
} else if ( $chat_name === 'surf' ) {
    $phpbb_root_path = '/home/purje719/public_html/pulinat.purjelautaliitto.fi/surfbb/';
}

# set_include_path(get_include_path() . PATH_SEPARATOR . $chat_path);

define('IN_PHPBB', true);
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$mode = $_REQUEST['mode'];
if (in_array($mode, array('login', 'logout', 'confirm', 'sendpassword', 'activate')))
{
    define('IN_LOGIN', true);
}


# connect to to phpBB session
$user->session_begin();
#var_dump($user);
$auth->acl($user->data);
$user->setup();

if(isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $autologin = true;
    $result = $auth->login($username, $password, $autologin);
    header( 'Location: index.php?chatname=' . get_chatname() );
    print('<a href="index.php" target="_top">Back to main page</a>');
    exit;
}

if (isset($_REQUEST['mode'])) {
    if ($_REQUEST['mode'] === 'logout') {

        $user->session_kill();
        header( 'Location: index.php?chatname=' . get_chatname() );
        print('<a href="index.php" target="_top">Back to main page</a>');
        exit();

    }
}

/* Public API */

function auth_get_session_id () {
    global $user;
    return $user->session_id;
}


function auth_user_logged_in() {
    global $user, $auth;

    return $user->data['is_registered'];
}

function auth_get_username() {
    global $user;
    if ( auth_user_logged_in() ) {
        $username = $user->data['username'];
        return $username;
    } else {
        return null;
    }
}

/* Private functions */



/* not needed */

?>

