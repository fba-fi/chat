<?php

require_once dirname(__FILE__) . "/simpletest/autorun.php";
require_once dirname(__FILE__) . "/../lib/common.php";

require_once('simpletest/web_tester.php');
SimpleTest::prefer(new TextReporter());
SimpleTest::ignore('WebTestCase');

class TestAPI extends WebTestCase
{

    private $latest_url = 'http://spll.fi/chat/api/latest.php';
    private $latest_json_url = 'http://spll.fi/chat/api/latest_json.php';
    private $send_url = 'http://spll.fi/chat/api/send.php';
    private $messageinfo_url = 'http://spll.fi/chat/api/messageinfo.php';

    function setup()
    {
        $this->delete_database();
    }

    function teardown()
    {
        $this->delete_database();
    }

    function delete_database()
    {
        $dbdir = join_paths(dirname(__FILE__), '../data/chattestingdatabase');
        if (is_dir($dbdir)) {
            rmtree($dbdir);
        }
    }

    function is_test_server()
    {
        return gethostname() == 'spll.fi';
    }

    function test_readLatestHTML()
    {

        print "Hostname: " . gethostname() . "<br/>\n";
        if (!$this->is_test_server()) {
            print "Server tests: skipped\n";
            return;
        }
        print "Server tests: included\n";

        $url = $this->latest_url;
        $this->assertTrue($this->get($url));
        $this->assertText('Invalid parameters');

        $this->get($url . '?chatname=chat-testing-database');
        $this->assertText("Created new database: 'chattestingdatabase'");
    }

    function test_messageinfo()
    {
        if (!$this->is_test_server()) {
            return;
        }


    }

    function test_send()
    {

        if (!$this->is_test_server()) {
            return;
        }

        $latest_url = $this->latest_url . '?chatname=chat-testing-database';
        $this->assertTrue($this->get($latest_url));
        $this->assertText('new database');

        $send_url = $this->send_url;
        $username = 'test-user';
        $messagetext = 'testing sending message 001';

        $parameters = array(
            'chatname' => 'chat-testing-database',
            'username' => $username,
            'message' => $messagetext
        );

        $this->assertTrue($this->post($send_url, $parameters));
        $this->assertText('Message sent.');

        $latest_url = $this->latest_url . '?chatname=chat-testing-database';
        $this->assertTrue($this->get($latest_url));
        $this->assertText($username);
        $this->assertText($messagetext);

        $messagetext = 'second testing message';
        $parameters = array(
            'chatname' => 'chat-testing-database',
            'username' => $username,
            'message' => $messagetext
        );

        $this->assertTrue($this->post($send_url, $parameters));
        $this->assertText('Message sent.');

        $latest_url = $this->latest_url . '?chatname=chat-testing-database';
        $this->assertTrue($this->get($latest_url));
        $this->assertText($username);
        $this->assertText($messagetext);

        $latest_url = $this->latest_json_url . '?chatname=chat-testing-database';
        $messages = json_decode($this->get($latest_url));
        # $this->dump($messages);
        foreach ($messages as $message) {
            $this->assertTrue($username == $message->username);
            $this->assertFalse(property_exists($message, 'client_ip'));
            $this->assertPattern('/\w+/', $message->text);
            $this->assertPattern('/\d+/', $message->client_id);
            $this->assertPattern('/\w+/', $message->message_id);
            $this->assertPattern('/\d+/', $message->timestamp);
        }

    }
}

?>
