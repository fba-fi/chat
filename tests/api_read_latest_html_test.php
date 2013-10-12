<?php

require_once dirname(__FILE__) . "/simpletest/autorun.php";
require_once dirname(__FILE__) . "/../lib/common.php";

require_once('simpletest/web_tester.php');
SimpleTest::prefer(new TextReporter());
SimpleTest::ignore('WebTestCase');

class TestAPI extends WebTestCase
{

    private $latest_url = 'http://spll.fi/chat/api/latest.php';
    private $send_url = 'http://spll.fi/chat/api/send.php';

    function setup() {
        $this->delete_database();
    }

    function teardown() {
        $this->delete_database();
    }

    function delete_database() {
        $dbdir = join_paths(dirname(__FILE__), '../data/chattestingdatabase');
        if (is_dir($dbdir)) {
            rmtree($dbdir);
        }
    }

    function is_test_server() {
        return gethostname() == 'spll.fi';
    }

    function test_readLatestHTML() {

        print "Hostname: " . gethostname() . "<br/>";
        if (!$this->is_test_server()) {
            print "Server tests: skipped";
            return;
        }
        print "Server tests: included";

        $url = $this->latest_url;
        $this->assertTrue($this->get($url));
        $this->assertText('Invalid parameters');

        $this->get($url . '?chatname=chat-testing-database');
        $this->assertText("Created new database: 'chattestingdatabase'");
    }

    function test_send() {
        if (!$this->is_test_server()) {
            return;
        }

        $latest_url = $this->latest_url . '?chatname=chat-testing-database';
        $this->assertTrue($this->get($latest_url));
        $this->assertText('new database');

        $send_url = $this->send_url;
        $username = 'test-user';
        $message = 'testing sending message 001';

        $parameters = array(
            'chatname' => 'chat-testing-database',
            'username' => $username,
            'message' => $message
        );

        $this->assertTrue($this->post($send_url, $parameters));
        $this->assertText('Message sent.');

        $latest_url = $this->latest_url . '?chatname=chat-testing-database';
        $this->assertTrue($this->get($latest_url));
        $this->assertText($username);
        $this->assertText($message);

        $message = 'second testing message';
        $parameters = array(
            'chatname' => 'chat-testing-database',
            'username' => $username,
            'message' => $message
        );

        $this->assertTrue($this->post($send_url, $parameters));
        $this->assertText('Message sent.');

        $latest_url = $this->latest_url . '?chatname=chat-testing-database';
        $this->assertTrue($this->get($latest_url));
        $this->assertText($username);
        $this->assertText($message);

    }
}

?>
