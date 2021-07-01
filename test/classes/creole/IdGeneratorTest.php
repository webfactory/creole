<?php

require_once 'PHPUnit2/Framework/TestCase.php';

/**
 * Tests for the IdGenerator class.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 *
 * @version $Revision: 1.2 $
 */
abstract class IdGeneratorTest extends PHPUnit2_Framework_TestCase
{
    protected $conn;

    protected $idgen;

    /**
     * Re-initialize the database.
     *
     * We only need to do this in setUp() method -- not in every invocation of this class --
     * since the ResultSet methods do not modify the db.
     */
    public function setUp()
    {
        DriverTestManager::restore();
        $this->idgen = $this->conn->getIdGenerator();
    }

    public function __construct()
    {
        $this->conn = DriverTestManager::getConnection();
        $this->idgen = $this->conn->getIdGenerator();
    }

    /**
     * Assert that an exception is expected to match string.
     */
    protected function expectException($excstr, $e)
    {
        if (false !== stripos($e->getMessage(), $excstr)) {
            $found = true;
        } else {
            $this->fail('Did not find expected exception containing string: '.$excstr);
        }
    }

    /**
     * Ensures that drivers are implementing the correct Id Method.
     *
     * @test
     */
    abstract public function getMethod();

    /**
     * @test
     */
    public function isBeforeInsert()
    {
        $type = $this->idgen->getIdMethod();
        if (IdGenerator::SEQUENCE === $type) {
            $this->assertTrue($this->idgen->isBeforeInsert());
        } else {
            $this->assertFalse($this->idgen->isBeforeInsert());
        }
    }

    /**
     * @test
     */
    public function isAfterInsert()
    {
        $type = $this->idgen->getIdMethod();
        if (IdGenerator::AUTOINCREMENT === $type) {
            $this->assertTrue($this->idgen->isAfterInsert());
        } else {
            $this->assertFalse($this->idgen->isAfterInsert());
        }
    }

    /**
     * @test
     */
    public function getId()
    {
        // propel (which generated the SQL dumps) has
        // convention of creating sequences w/ namelike tablename_seq
        // so we'll use that here.

        $exch = DriverTestManager::getExchange('IdGeneratorTest.getId.INIT');
        $rs = $this->conn->executeQuery($exch->getSql(), ResultSet::FETCHMODE_NUM);
        $rs->next();
        $max = $rs->getInt(1);

        $keyInfo = 'idgentest_seq';

        if (IdGenerator::SEQUENCE === $this->idgen->getIdMethod()) {
            $exch = DriverTestManager::getExchange('IdGeneratorTest.getId.SEQUENCE');
            $id = $this->idgen->getId($keyInfo);
            $stmt = $this->conn->prepareStatement($exch->getSql());
            $stmt->executeUpdate([$id, 'Test']);
        } else {
            $exch = DriverTestManager::getExchange('IdGeneratorTest.getId.AUTOINCREMENT');
            $stmt = $this->conn->prepareStatement($exch->getSql());
            $stmt->executeUpdate(['Test']);
            $id = $this->idgen->getId($keyInfo);
        }

        $this->assertEquals($max + 1, $id, 0, 'Next id was not max + 1');
    }
}
