<?php

require_once dirname(__FILE__) . "/simpletest/autorun.php";
require_once dirname(__FILE__) . "/../lib/db/textfile.php";
require_once dirname(__FILE__) . "/../lib/common.php";

class TestDatabaseTextFile extends UnitTestCase
{

    private $databaseDirectory = '../data/chat-testing-database';
    private $db;

    function setup()
    {
        $_SERVER['REMOTE_ADDR'] = 'tes.tip.adr.ess';

        $db = new DatabaseTextFile($this->databaseDirectory);

        $db->setup();

        $this->assertNotNull($db->databaseDirectory);
        $this->assertTrue(is_dir($db->databaseDirectory));

        $this->assertNoPattern('/^\/data/', $db->getRecentFilename());
        $this->assertTrue(file_exists($db->getRecentFilename()));

        $this->db = $db;
    }
    function test_reportMessage()
    {
        $db = $this->db;

        $this->assertTrue($db->saveReport('test-id', '0.0.0.0'));
        $this->assertFalse($db->saveReport('test-id', '0.0.0.0'));

        $this->assertTrue($db->saveReport('test-id', '0.0.0.1'));
        $this->assertTrue($db->saveReport('test-id', '0.0.0.2'));

        $reports = $db->getReports('test-id');

        #$this->dump(exec("find '$this->databaseDirectory' -ls"));

        $this->assertEqual(3, sizeof($reports));

    }

    function teardown()
    {
        rmtree($this->databaseDirectory);
    }

    function test_getClientFilename()
    {
        $db = $this->db;

        $client_id = sha1(get_client_ip());
        $clients_dir = dirname($this->db->getClientFilename($client_id));
        $this->assertTrue(is_dir($clients_dir));

        $filename = $this->db->getClientFilename(sha1('0.0.0.0'));
        $this->assertPattern('/' . sha1('0.0.0.0') . '$/', $filename);

        $filename = $this->db->getClientFilename(sha1('0.0.0.1'));
        $this->assertPattern('/' . sha1('0.0.0.1') . '$/', $filename);
    }

    function test_storeMessage()
    {
    }

    function test_saveUserIP()
    {
    }

    function test_formatMessage()
    {
    }

    function test_getRecentFilename ()
    {
        $this->assertTrue(is_file($this->db->getRecentFilename()));
    }

    function test_getArchiveFilename ()
    {
        $this->assertTrue(is_dir(dirname($this->db->getArchiveFilename())));
    }


    function test_writeToRecentFile()
    {
    }

    function test_writeToArchiveFile()
    {
    }

    function test_getClientInfo()
    {
        $db = $this->db;

        # db with two message
        $db->saveMessage('test0', 'foo bar baz');
        $db->saveMessage('test er', 'foo bar baz');

        $db->saveMessage('test er', 'foo bar baz', '0.0.0.1');
        $db->saveMessage('test1', 'foo bar baz', '0.0.0.1');

        $clients_dir = dirname($db->getClientFilename('foo-id'));
        $this->assertEqual(4, sizeof(scandir($clients_dir)));

        #$this->dump(scandir($clients_dir));

        # Get message info for the message
        $messages = $db->getLatestMessages();
        #$this->dump($messages);

        foreach ($messages as $message) {

            $info = $db->getClientInfoWithMessageID($message["message_id"]);
            if ($message["client_ip"] == 'tes.tip.adr.ess') {
                $this->assertTrue(in_array('test0', $info["nicknames"]));
            } else {
                $this->assertTrue(in_array('test1', $info["nicknames"]));
            }
            $this->assertTrue(in_array('test er', $info["nicknames"]));

        }

    }

    function test_getLatestMessages()
    {

        $db = $this->db;

        # No messages
        $messages = $db->getLatestMessages();
        $this->assertNotNull($messages);
        $this->assertTrue(sizeof($messages) == 0);


        # One message
        $db->saveMessage('test1', 'foo bar baz');
        $messages = $db->getLatestMessages();
        $this->assertNotNull($messages);
        $this->assertTrue(sizeof($messages) == 1);

        # Two messages
        $db->saveMessage('test2', 'foo baz bar', '0.0.0.1');
        $messages = $db->getLatestMessages();
        $this->assertNotNull($messages);
        $this->assertTrue(sizeof($messages) == 2);

        #$this->dump(scandir(dirname($db->getRecentFilename())));
        #$this->dump(file($db->getRecentFilename()));

        $message = $messages[0];
        $this->assertNotNull($message["message_id"]);
        $this->assertNotNull($message["client_id"]);
        $this->assertNotNull($message["client_ip"]);
        $this->assertNotNull($message["timestamp"]);
        $this->assertPattern('/0.0.0.1/', $message["client_ip"]);
        $this->assertPattern('/test2/', $message["username"]);
        $this->assertPattern('/foo baz bar/', $message["text"]);
    
        $message = $messages[1];
        $this->assertNotNull($message["message_id"]);
        $this->assertNotNull($message["client_id"]);
        $this->assertNotNull($message["client_ip"]);
        $this->assertNotNull($message["timestamp"]);

        $this->assertPattern('/tes.tip.adr.ess/', $message["client_ip"]);
        $this->assertPattern('/test1/', $message["username"]);
        $this->assertPattern('/foo bar baz/', $message["text"]);

    }
}
