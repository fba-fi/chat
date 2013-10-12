<?php

require_once dirname(__FILE__) . "/simpletest/autorun.php";
require_once dirname(__FILE__) . "/../lib/db/textfile.php";
require_once dirname(__FILE__) . "/../lib/common.php";

class TestCommon extends UnitTestCase
{
    function test_join_paths() {
        $path = join_paths('foo','bar');
        $this->assertEqual($path, 'foo/bar');
        $path = join_paths('foo','/bar/');
        $this->assertEqual($path, 'foo/bar/');
        $path = join_paths('foo','bar', 'baz');
        $this->assertEqual($path, 'foo/bar/baz');
        $path = join_paths('foo','bar/', '/baz');
        $this->assertEqual($path, 'foo/bar/baz');
    }
}

?>
