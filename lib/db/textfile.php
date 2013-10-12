<?php

require_once dirname(__FILE__) . "/../common.php";

class DatabaseTextFile
{

    public $databaseDirectory;

    function __construct($databaseDirectory)
    {
        $this->databaseDirectory = $databaseDirectory;
    }
    function reportMessage($messageid)
    {
        $filename = $this->getReportFilename($messageid);
        $lines = file($filename);

        $client_ip = get_client_ip();

        if (in_array($client_ip, $lines)) {
            return "Viesti on jo ilmoitettu";
        }

        writeReportFile($messageid);
        return "Viesti ilmoitettu asiattomaksi";
    }

    function saveMessage($username, $message)
    {
        $timestamp = date('d.m H:i:s');
        $message = $this->format_message($message);
        $ip = get_client_ip();

        $line = "$ip#$timestamp <b>$username:</b> $message\n";

        $this->writeHistoryFile($line);
        $this->writeRecentFile($line);
        $this->writeUserFile($username);
    }


    function setup()
    {

        $filenames = array(
            $this->getUserFilename(),
            $this->getRecentFilename(),
            $this->getReportFilename('foo-bar-baz')
        );

        foreach ($filenames as $filename) {
            $dir = dirname($filename);
            if (!is_dir($dir)) {
                mkdir($dir, 0750, true);
            }
        }

        touch($this->getRecentFilename());
    }

    function getLatestMessages($last_message_id)
    {
        $messages = array();
        $lines = file($this->getRecentFilename());

        foreach ($lines as $line) {

            $message_id = sha1($line);

            $match = '/^(.*?)#(.*?) +<b> *(.*?):<\/b> (.*)$/';
            if (!preg_match($match, $line, $matches)) {
                continue;
            }

            $client_id = sha1($matches[1]);
            $timestamp = $matches[2];
            $username = $matches[3];
            $message_text = $matches[4];


            $message = "<a href='#' id='$message_id' class='message'>";
            $message .= "$timestamp <strong>$username</strong></a>";
            $message .= "<strong>:</strong> $message_text<br/>\n";
            $message .= "<div class='messageinfo' id='messageinfo_$message_id'></div>\n";

            array_push($messages, $message);
        }

        return implode(" ", $messages);
    }

    function getMessageInfo($message_id)
    {

        $message_info = array();

        array_push($message_info, "Nimimerkit samasta IP-osoitteesta: ");

        # Search message with $message_id from latest messages
        $lines = file($this->getRecentFilename());

        $message = null;

        foreach ($lines as $line) {
            if ($message_id == sha1($line)) {
                $message = $line;
                break;
            }
        }

        if (!preg_match('/(.*?)#/', $message, $matches)) {
            return "Message not found: " . $message_id;
        }

        $client_id = sha1($matches[1]);

        # Print all usernames for client_id
        $lines = file(join_paths(dirname($this->getUserFilename()), $client_id));
        $nicknames = array();
        foreach ($lines as $line) {
            $username = preg_filter('/(.*)#/', '', $line);
            array_push($nicknames, chop($username));
        }

        array_push($message_info, implode(', ', $nicknames));

        return implode("", $message_info);
    }

    function delete_latest_message($username)
    {
        $filename = get_recent_filename();
        $lines = file($filename);
        $ip =get_client_ip();
        $match = "/^$ip#.*<b>$username:</";

        error_log($match);
        $lines_deleted = array();
        $lines = $lines;
        $found = false;

        foreach ($lines as $value) {
        if ((!$found) && (preg_match($match, $value) > 0)) {
            $found = true;
        } else {
            array_push($lines_deleted, $value);
            error_log($value . "\n");
        }
        }

        $lines = $lines_deleted;
        file_put_contents($filename, $lines);
    }

    function exists()
    {
        return is_file($this->getRecentFilename());
    }

    /* Private functions */

    function format_message($message)
    {

        $patterns = array(
            "/[<>]+/" => " ",
            '/\r/' => '',
            '/\n/' => '<br/>',
            '#http://([^ <]*)#' => '<a target="_blank" href="http://$1" >http://$1</a>',
            '#https://([^ <]*)#' => '<a target="_blank" href="http://$1" >http://$1</a>',
            '#HTTP://([^ <]*)#' => '<a target="_blank" href="http://$1" >http://$1</a>',
            '#HTTPS://([^ <]*)#' => '<a target="_blank" href="http://$1" >http://$1</>'
        );

        foreach ($patterns as $match => $replace) {
            $message = preg_replace($match, $replace, $message);
        }

        return $message;
    }


    /* Paths for files */

    function getRecentFilename()
    {
        return join_paths($this->databaseDirectory, 'messages/latest.txt');
    }

    function getArchiveFilename()
    {
        $timestamp = $timestamp = date('YW');
        return join_paths($this->databaseDirectory, "messages/$timestamp.histo.txt");
    }

    function getUserFilename()
    {
        $client_id = sha1(get_client_ip());
        return join_paths($this->databaseDirectory, "users/$client_id");
    }

    function getReportFilename($messageid) {
        return join_paths($this->databaseDirectory, "reports/$messageid");
    }

    /* File write operations */

    function writeUserFile($username)
    {
        $filename = $this->getUserFilename();
        $client_ip = get_client_ip();
        $user_info = "$client_ip#$username\n";
        $users = array();

        if (is_file($filename)) {
            $users = file($filename);
        }

        if (in_array($user_info, $users)) {
            return;
        }

        file_put_contents($filename, $user_info, FILE_APPEND);
    }

    function writeHistoryFile($line)
    {
        $filename = $this->getArchiveFilename();
        file_put_contents($filename, $line, FILE_APPEND);
    }

    function writeReportFile($messageid)
    {
        $filename = $this->getArchiveFilename();
        file_put_contents($filename, $client_ip, FILE_APPEND);
        return true;
    }


    function writeRecentFile($line)
    {
        $filename = $this->getRecentFilename();
        $lines[] = $line;
        if (file_exists($filename)) {
            $lines_from_file = file($filename);
            $lines_from_file = array_slice($lines_from_file, 0, 150, true);
        }
        $lines = array_merge($lines, $lines_from_file);
        file_put_contents($filename, $lines);
    }

}

?>
