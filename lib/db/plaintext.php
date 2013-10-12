
<?php

/* SPLL-chat plain-text-file backend. */

include 'config.php';


/* Configuration */


/* Public API */

function db_insert_message ($chatname, $username, $message) {
    $timestamp = date('d.m H:i:s');
    $message = format_message($message);

    $line = get_client_ip() . "#" .
        "<div class=\"message\">" .
        "<div class=\"time\">$timestamp</div>" .
        "<div class=\"username\">$username:</div>" .
        "<div class=\"text\">$message</div>" .
        "</div>\n";
    write_to_history_file($chatname, $line);
    write_to_recent_file($chatname, $line);
}

function db_get_latest_messages($chatname) {
    $filename = get_recent_filename($chatname, "r");
    if ( !file_exists($filename) ) {
        return '';
    }
    $lines = file($filename);
    $lines = preg_filter("/[^#]*#/", "", $lines);
    $lines = array_reverse($lines);
    return implode('',$lines);
}

function db_delete_latest_message($chatname, $username) {
    $filename = get_recent_filename($chatname);
    $lines = file($filename);
    $ip = get_client_ip();
    $match = "/^$ip#.*>$username:</";

    error_log($match);
    $lines_deleted = array();
    $lines = array_reverse($lines);
    $found = false;

    foreach ($lines as $value) {
        if ( (! $found ) && ( preg_match($match, $value) > 0 ) ) {
            error_log("####################### erqwerjqweölkrjqwerlkj " . $val);
            $found = true;
        } else {
            array_push($lines_deleted, $value);
            error_log($value . "\n");
        }
    }

    $lines = array_reverse($lines_deleted);
    file_put_contents($filename, $lines);
}

function db_chat_exists($chatname) {
    global $chat_path;
    return is_dir("$chat_path/data/$chatname");
}

/* Private functions */

function get_client_ip() {
    return $_SERVER['REMOTE_ADDR'];
}

function format_message($message) {
    $message = preg_replace('/\r/', '', $message);
    $message = preg_replace('/\n/', '<br/>', $message);
    return $message;
}


function get_recent_filename ($chatname) {
    global $chat_path, $recent_file;
    return "$chat_path/data/$chatname/db/recent.txt";
}

function get_archive_filename ($chatname) {
    global $chat_path, $recent_file;
    $timestamp = $timestamp = date('Y-m');
    return "$chat_path/data/$chatname/db/$timestamp.history.txt";
}

function write_to_recent_file($chatname, $line) {
    $filename = get_recent_filename($chatname);
    $lines = array();
    if ( file_exists($filename) ) {
        $lines = file($filename);
        if ( count($lines) > 100 ) {
            array_shift($lines);
        }
    }
    array_push($lines, $line);
    file_put_contents($filename, $lines);
}

function write_to_history_file($chatname, $line) {
    $filename = get_archive_filename($chatname);
    file_put_contents($filename, $line, FILE_APPEND);
}


?>
