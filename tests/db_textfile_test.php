<?php

require_once dirname(__FILE__) . "/simpletest/autorun.php";
require_once dirname(__FILE__) . "/../lib/db/textfile.php";
require_once dirname(__FILE__) . "/../lib/common.php";

class TestDatabaseTextFile extends UnitTestCase
{

    private $databaseDirectory = '../data/chat-testing-database';
    private $db;

    function setup() {
        $_SERVER['REMOTE_ADDR'] = 'tes.tip.adr.ess';

        $db = new DatabaseTextFile($this->databaseDirectory);

        $db->setup();

        $this->assertNotNull($db->databaseDirectory);
        $this->assertTrue(file_exists($db->databaseDirectory));

        $this->assertNoPattern('/^\/data/', $db->getRecentFilename());
        $this->assertTrue(file_exists($db->getRecentFilename()));

        $this->db = $db;
    }

    function teardown() {
        rmtree($this->databaseDirectory);
    }

    function test_storeMessage() {
    }
    function test_getClientIP() {
    }
    function test_saveUserIP() {
    }
    function test_formatMessage() {
    }
    function test_getRecentFilename () {
        $this->assertTrue(is_file($this->db->getRecentFilename()));
    }
    function test_getArchiveFilename () {
        $this->assertTrue(is_dir(dirname($this->db->getArchiveFilename())));
    }
    function test_getUserFilename () {
        $this->assertTrue(is_dir(dirname($this->db->getUserFilename())));
    }
    function test_writeToRecentFile() {
    }
    function test_writeToArchiveFile() {
    }

    function test_getMessageInfo() {
        $db = $this->db;

        # db with two message
        $db->saveMessage('test', 'foo bar baz');
        $db->saveMessage('tester', 'foo bar baz');
        $db->saveMessage('tester', 'foo bar baz');
        $db->saveMessage('tester3', 'foo bar baz');

        $messages = $db->getLatestMessages();
        $this->assertNotEqual($messages, '');

        #$this->dump(scandir(dirname($db->getUserFilename())));

        $lines = file($db->getUserFilename());
        $this->assertTrue($lines != '');

        # Get message info for the message
        $messages = $db->getLatestMessages();

        $this->assertPattern("/ id=/", $messages);
        preg_match("/ id='(.*?)'/", $messages, $matches);

        $info = $db->getMessageInfo($matches[1]);

        $this->assertPattern('/\btest\b/', $info);
        $this->assertPattern('/\btester\b/', $info);
        $this->assertPattern('/\btester3\b/', $info);
    }

    function test_getLatestMessages() {

        $db = $this->db;

        # Test empty db
        $messages = $db->getLatestMessages();
        $this->assertEqual($messages, '');

        # db with one message
        $db->saveMessage('test', 'foo bar baz');
        $messages = $db->getLatestMessages();
        $this->assertNotEqual($messages, '');

        # db with two messages
        $db->saveMessage('test', 'foo bar baz');
        $messages = $db->getLatestMessages();
        $this->assertNotEqual($messages, '');

        $this->assertPattern('/test/', $messages);

        $this->dump($messages);


    }
}
