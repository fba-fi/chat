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

$client_ip = get_client_ip();
$messageid = $_REQUEST["messageid"];

$message = $db->findMessageWithID($messageid);

$clientinfo = $db->getClientInfo($message["client_ip"]);
$clientinfo["message_id"] = $messageid;

$reports = $db->getReports($messageid);

$clientinfo["reported_by_user"] = in_array($client_ip, $reports);
$clientinfo["can_delete"] = $clientinfo["client_ip"] == $client_ip;

$clientinfo["report_count"] = sizeof($reports);
$clientinfo["reports"] = $reports;

# unset($clientinfo["client_ip"]);

print json_encode($clientinfo);

?>
