<?php

require_once dirname(__FILE__) . "/../lib/common.php";
require_once dirname(__FILE__) . "/../lib/db/textfile.php";

$db = new DatabaseTextFile(getDatabaseDirectory());
$chatname = get_chatname();

if (!$db->exists()) {
    if ($chatname) {
        $db->setup();
        echo "Created new database: '$chatname'";
    } else {
        echo "Invalid parameters!";
        exit;
    }
}

$messageid = $_REQUEST['messageid'];

print $db->getLatestMessages($messageid);

?>
