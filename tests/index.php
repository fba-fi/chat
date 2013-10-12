<?php
require_once(dirname(__FILE__) . '/simpletest/autorun.php');

class AllTests extends TestSuite {
    function AllTests() {
        parent::__construct();
        $filenames = scandir('.');
        # Autodiscover *_test.php files
        foreach ($filenames as $filename) {
            if (preg_match("/_test.php$/", $filename)) {
            $this->addFile($filename);
            }
        }
    }
}
?>

