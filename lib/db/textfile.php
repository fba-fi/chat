<?php

require_once dirname(__FILE__) . "/../common.php";

class DatabaseTextFile
{

    public $databaseDirectory;

    function __construct($databaseDirectory)
    {
        $this->databaseDirectory = $databaseDirectory;
    }

    function saveReport($messageid, $client_ip=null)
    {
        $filename = $this->getReportsFilename($messageid);

        $lines = array();
        if (is_file($filename)) {
            $lines = file($filename);
        }

        $client_ip = get_client_ip($client_ip);
        if (in_array("$client_ip\n", $lines)) {
            return False;
        }

        $this->writeReportFile($messageid, $client_ip);

        return True;
    }

    function getReports($messageid)
    {
        $reports = array();
        $filename = $this->getReportsFilename($messageid);
        if (is_file($filename)) {
            $lines = file($this->getReportsFilename($messageid));
            foreach ($lines as $line) {
                array_push($reports, chop($line));
            }
        }
        return $reports;
    }

    function saveMessage($username, $message, $client_ip=null)
    {
        $timestamp = date('d.m H:i:s');
        $message = $this->formatMessage($message);

        if (is_null($client_ip)) {
            $client_ip = get_client_ip();
        }

        $line = "$client_ip#$timestamp <b>$username:</b> $message\n";

        $this->writeHistoryFile($line);
        $this->writeRecentFile($line);
        $this->writeClientFile($client_ip, $username);
    }

    function setup()
    {
        $dirs = array('clients', 'messages', 'reports');
        foreach ($dirs as $dir) {
            $dir = join_paths($this->databaseDirectory, $dir);
            if (!is_dir($dir)) {
                mkdir($dir, 0750, true);
            }
        }
        touch($this->getRecentFilename());
    }

    function parseMessage($line)
    {

        $match = '/^(.*?)#(.*?) +<b> *(.*?):<\/b> (.*)$/';
        if (!preg_match($match, $line, $matches)) {
            return null;
        }

        return array(
            "message_id" => sha1($line),
            "client_ip" => $matches[1],
            "client_id" => sha1($matches[1]),
            "timestamp" => $matches[2],
            "username" => $matches[3],
            "text" => $matches[4]
        );
    }

    function getLatestMessages($last_message_id=null)
    {
        $messages = array();
        $lines = file($this->getRecentFilename());

        foreach ($lines as $line) {

            $message_id = sha1($line);
            if ($last_message_id == $message_id) {
                break;
            }

            $message = $this->parseMessage($line);
            array_push($messages, $message);
        }

        return $messages;
    }

    function findMessageWithID($message_id)
    {
        $lines = file($this->getRecentFilename());
        foreach ($lines as $line) {
            if ($message_id == sha1($line)) {
                return $this->parseMessage($line);
            }
        }
        return null;
    }

    function getClientInfo($client_ip)
    {
        $nicknames = array();
        $client_id = sha1($client_ip);
        $lines = file($this->getClientFilename($client_id));

        foreach ($lines as $line) {
            $username = preg_filter('/(.*)#/', '', $line);
            array_push($nicknames, chop($username));
        }

        $message_info = array(
            "client_id" => $client_id,
            "client_ip" => $client_ip,
            "nicknames" => $nicknames
        );

        return $message_info;
    }

    function getClientInfoWithMessageID($message_id)
    {
        $message = $this->findMessageWithID($message_id);
        return $this->getClientInfo($message["client_ip"]);
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

    function formatMessage($message)
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

    function getClientFilename($client_id)
    {
        return join_paths($this->databaseDirectory, "clients", $client_id);
    }

    function getReportsFilename($messageid)
    {
        return join_paths($this->databaseDirectory, "reports", $messageid);
    }

    /* File write operations */

    function writeClientFile($client_ip, $username)
    {
        $filename = $this->getClientFilename(sha1($client_ip));

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

    function writeReportFile($messageid, $client_ip)
    {
        $filename = $this->getReportsFilename($messageid);
        file_put_contents($filename, "$client_ip\n", FILE_APPEND);
        return true;
    }

    function writeRecentFile($line)
    {
        $filename = $this->getRecentFilename();
        $file = fopen($filename, 'c+');

        # Wait until we get exclusive lock
        if (!flock($file, LOCK_EX)) {
            return -1;
        }

        # Append file data
        $new_lines[] = $line;
        $lines_from_file = null;
        while (!feof($file)) {
            $lines_from_file .= fread($file, 1024*1024);
        }

        # Split and limit to first 150 lines
        $lines_from_file = split('\n', $lines_from_file);
        $lines_from_file = array_slice($lines_from_file, 0, 150, true);
        $lines_from_file = array_merge($new_lines, $lines_from_file);
        $message_count = sizeof($lines_from_file);
        $lines_from_file = join("", $lines_from_file);

        #print "<pre>";
        #print hex_dump($lines_from_file);
        #print "</pre>";

        # Write new data
        ftruncate($file, 0);
        rewind($file);
        fwrite($file, $lines_from_file);

        # Flush and release lock
        fflush($file);
        flock($file, LOCK_UN); 
        fclose($file);

        return $message_count;

    }
}

?>
