
<?

include "../config.php";
include "include/common.php";
include "$db_backend";
include "$auth_backend";

if ( ! auth_user_logged_in() ) {
    print "Sorry, no access.";
    exit;
}

$chatname = get_chatname();
if ( ! db_chat_exists($chatname)) {
    print "No such chat";
    exit;
};

db_delete_latest_message($chatname, auth_get_username());

print "Message deleted.";

?>
