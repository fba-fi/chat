<?php 
require_once dirname(__FILE__) . "/simpletest/autorun.php";

require_once('simpletest/web_tester.php');
SimpleTest::prefer(new TextReporter());


class TestIndex extends WebTestCase
{
    private $index_url = 'http://spll.fi/chat/index.php';

    function test_index_page() {
        $url = $this->index_url;
        $this->assertTrue($this->get($url . '?chatname=chat-testing-database'));

        $this->assertText('chattestingdatabase');
        $this->assertPattern('<html>');
        $this->assertPattern('</html>');
    }
}


?>
