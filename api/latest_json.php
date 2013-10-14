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

$messageid = null;
if (isset($_REQUEST["messageid"])) {
        $messageid = $_REQUEST['messageid'];
}

$messages = $db->getLatestMessages($messageid);
foreach ($messages as $key => $value) {
    unset($messages[$key]["client_ip"]);
}
print json_encode(array_reverse($messages));

?>
