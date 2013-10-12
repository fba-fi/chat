<?php

function get_client_ip()
{
if (defined('_SERVER') && in_array('REMOTE_ADDR', $_SERVER)) {
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

?>
