<?

require_once dirname(__FILE__) . "/../lib/common.php";
require_once dirname(__FILE__) . "/../lib/db/textfile.php";

#if ( ! auth_user_logged_in() ) {
#    print "Sorry, no access.";
#    exit;
#}

$chatname = get_chatname();
$message = $_POST['message'];
$username = $_POST['username'];

if ( ! preg_match('/\w+/', $message) ) {
    print "Missing message text.";
    exit;
};
if ( ! preg_match('/\w+/', $chatname) ) {
    print "Missing chat name.";
    exit;
};
if ( ! preg_match('/\w+/', $username) ) {
    print "Missing username.";
    exit;
};

$db = new DatabaseTextFile(getDatabaseDirectory());
if (!$db->exists()) {
    if ($chatname) {
        $db->setup();
        echo "Created new database: '$chatname'";
    } else {
        echo "Invalid parameters!";
        exit;
    }
}

$db->saveMessage($username, $message);

print "Message sent.";

?>
