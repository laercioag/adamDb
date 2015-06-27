<?php

require_once 'vendor/autoload.php';

use PicoDb\Database;

class MysqlDatabaseTest extends PHPUnit_Framework_TestCase
{
    private $db;

    public function setUp()
    {
        $this->db = new Database(array('driver' => 'mysql', 'hostname' => 'localhost', 'username' => 'root', 'password' => '', 'database' => 'picodb'));
        $this->db->getConnection()->exec('CREATE DATABASE IF NOT EXISTS `picodb`');
        $this->db->getConnection()->exec('DROP TABLE IF EXISTS foobar');
        $this->db->getConnection()->exec('DROP TABLE IF EXISTS schema_version');
        $this->db->log_queries = true;
    }

    public function testEscapeIdentifer()
    {
        $this->assertEquals('`a`', $this->db->escapeIdentifier('a'));
        $this->assertEquals('a.b', $this->db->escapeIdentifier('a.b'));
        $this->assertEquals('`c`.`a`', $this->db->escapeIdentifier('a', 'c'));
        $this->assertEquals('a.b', $this->db->escapeIdentifier('a.b', 'c'));
        $this->assertEquals('SELECT COUNT(*) FROM test', $this->db->escapeIdentifier('SELECT COUNT(*) FROM test'));
        $this->assertEquals('SELECT COUNT(*) FROM test', $this->db->escapeIdentifier('SELECT COUNT(*) FROM test', 'b'));
    }

    public function testEscapeIdentiferList()
    {
        $this->assertEquals(array('`c`.`a`', '`c`.`b`'), $this->db->escapeIdentifierList(array('a', 'b'), 'c'));
        $this->assertEquals(array('`a`', 'd.b'), $this->db->escapeIdentifierList(array('a', 'd.b')));
    }

    public function testThatPreparedStatementWorks()
    {
        $this->db->getConnection()->exec('CREATE TABLE foobar (id INT AUTO_INCREMENT NOT NULL, something TEXT, PRIMARY KEY (id)) ENGINE=InnoDB');
        $this->db->execute('INSERT INTO foobar (something) VALUES (?)', array('a'));
        $this->assertEquals(1, $this->db->getLastId());
        $this->assertEquals('a', $this->db->execute('SELECT something FROM foobar WHERE something=?', array('a'))->fetchColumn());
    }

    /**
     * @expectedException PicoDb\SQLException
     */
    public function testBadSQLQuery()
    {
        $this->db->execute('INSERT INTO foobar');
    }

    public function testDuplicateKey()
    {
        $this->db->getConnection()->exec('CREATE TABLE foobar (something CHAR(1) UNIQUE) ENGINE=InnoDB');

        $this->db->execute('INSERT INTO foobar (something) VALUES (?)', array('a'));
        $this->db->execute('INSERT INTO foobar (something) VALUES (?)', array('a'));

        $this->assertEquals(1, $this->db->execute('SELECT COUNT(*) FROM foobar WHERE something=?', array('a'))->fetchColumn());
    }
}