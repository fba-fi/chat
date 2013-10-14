<?php

function get_client_ip($client_ip=null)
{
        if ($client_ip != null) {
            return $client_ip;
        } else if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
            return $_SERVER['REMOTE_ADDR'];
        } else {
            return '0.0.0.0';
        }
}

function class_page_selected($pagename) {
    if (get_chatname() == $pagename) {
        return " selected ";
    } else {
        return "";
    }
}

/**
 * Delete a file or directory recursively.
 * @param string $path
 */
function rmtree($path)
{
        if (is_dir($path))
        {
                foreach (scandir($path) as $name)
                {
                        if (in_array($name, array('.', '..')))
                        {
                                continue;
                        }
                        $subpath = $path.DIRECTORY_SEPARATOR.$name;
                        rmtree($subpath);
                }
                rmdir($path);
        }
        else
        {
                unlink($path);
        }
}
 

function join_paths()
    {
    $paths = array();

    foreach (func_get_args() as $arg) {
        if ($arg !== '') { $paths[] = $arg; }
    }

    return preg_replace('#/+#','/',join('/', $paths));
}

function get_chatname() {
    if (defined('_POST') && isset($_POST['chatname'])) {
        $chatname = $_POST['chatname'];
    } else if (isset($_REQUEST['chatname'])) {
        $chatname = $_REQUEST['chatname'];
    } else {
        return null;
    }
     
    $chatname = preg_replace('/[^\w]+/', '', $chatname);
    return $chatname;
}

function getDatabaseDirectory() {
        return join_paths(dirname(__FILE__), '../data', get_chatname());
}


function hex_dump($data, $newline="\n")
{
  static $from = '';
  static $to = '';

  static $width = 16; # number of bytes per line

  static $pad = '.'; # padding for non-visible characters

  if ($from==='')
  {
    for ($i=0; $i<=0xFF; $i++)
    {
      $from .= chr($i);
      $to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
    }
  }

  $hex = str_split(bin2hex($data), $width*2);
  $chars = str_split(strtr($data, $from, $to), $width);

  $offset = 0;
  foreach ($hex as $i => $line)
  {
    echo sprintf('%6X',$offset).' : '.implode(' ', str_split($line,2)) . ' [' . $chars[$i] . ']' . $newline;
    $offset += $width;
  }
}


?>
