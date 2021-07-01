<?php

require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'creole/PreparedStatement.php';

include_once 'creole/util/Blob.php';
include_once 'creole/util/Clob.php';

/**
 * Unit tests for PreparedStatement class.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 *
 * @version $Id: PreparedStatementTest.php,v 1.10 2006/01/17 15:53:43 hlellelid Exp $
 */
class PreparedStatementTest extends PHPUnit2_Framework_TestCase
{
    protected $conn;

    public function setUp()
    {
        DriverTestManager::restore();
    }

    public function __construct()
    {
        $this->conn = DriverTestManager::getConnection();
    }

    protected function expectException(Exception $e, $msg)
    {
        if (false === stripos($e->getMessage(), $msg)) {
            $this->fail('Expected exception to contain text: '.$msg);
        }
    }

    /**
     * Supports getBlob() and setBlob() tests.
     *
     * @see ResultSetTest::getBlob()
     * @see PreparedStatementTest::setBlob()
     *
     * @return Blob
     */
    public function createBlob()
    {
        // read in the file
        $b = new Blob();
        $b->setInputFile(CREOLE_TEST_BASE.'/etc/lob/creole.png');

        return $b;
    }

    /**
     * Supports getClob() and setClob() tests.
     *
     * @see ResultSetTest::getClob()
     * @see PreparedStatementTest::setClob()
     *
     * @return Clob
     */
    public function createClob()
    {
        // read in the file
        $c = new Clob();
        $c->setInputFile(CREOLE_TEST_BASE.'/etc/lob/creoleguide.txt');

        return $c;
    }

    /**
     * Set BLOB value.
     *
     * @param Blob $blob The BLOB to insert into database.
     */
    public function setBlob(Blob $blob)
    {
        $exch = DriverTestManager::getExchange('PreparedStatementTest.setBlob');
        $this->conn->setAutoCommit(false);
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setInt(1, 1); // pkey
        $stmt->setString(2, 'TestName');
        $stmt->setBlob(3, $blob);
        $stmt->executeUpdate();
        $this->conn->commit();
        $this->conn->setAutoCommit(true);
    }

    /**
     * Set CLOB value.
     *
     * @param Clob $clob The CLOB to insert into database.
     */
    public function setClob(Clob $clob)
    {
        $exch = DriverTestManager::getExchange('PreparedStatementTest.setClob');
        $this->conn->setAutoCommit(false);
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setInt(1, 1); // pkey
        $stmt->setString(2, 'TestName');
        $stmt->setClob(3, $clob);
        $stmt->executeUpdate();
        $this->conn->commit();
        $this->conn->setAutoCommit(true);
    }

    /**
     * Note that limit & resultset scrolling behavior is extensively tested in ResultSetTest.
     *
     * @test
     */
    public function setLimit()
    {
        $exch = DriverTestManager::getExchange('ResultSetTest.ALL_RECORDS');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setLimit(10);

        $rs = $stmt->executeQuery(ResultSet::FETCHMODE_NUM);

        $this->assertEquals(10, $rs->getRecordCount());
    }

    /**
     * @test
     */
    public function setOffset()
    {
        $exch = DriverTestManager::getExchange('ResultSetTest.ALL_RECORDS');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setLimit(10);
        $stmt->setOffset(5);
        $rs = $stmt->executeQuery(ResultSet::FETCHMODE_NUM);

        $rs->next();

        $this->assertEquals(6, $rs->getInt(1));

        $rs->close();

        // test setting offset w/ no limit
        $stmt->setLimit(0);
        $stmt->setOffset(6);
        $rs = $stmt->executeQuery(ResultSet::FETCHMODE_NUM);
        $rs->next();
        $this->assertEquals(7, $rs->getInt(1));

        // try changing it
        // try changing the offset info
        $stmt->setOffset(4);
        $stmt->setLimit(10);
        $rs = $stmt->executeQuery(ResultSet::FETCHMODE_NUM);
        $rs->next();
        $this->assertEquals(5, $rs->getInt(1), 0, 'Expected new first row to have changed after changing offset.');

        $stmt->close();
    }

    /**
     * - test passing params to executeQuery()
     * - test fetchmodes.
     *
     * @test
     */
    public function executeQuery()
    {
        $exch = DriverTestManager::getExchange('PreparedStatementTest.GET_BY_PKEY');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $rs = $stmt->executeQuery([1], ResultSet::FETCHMODE_NUM);
        $rs->next();

        $this->assertEquals(1, $rs->getInt(1));

        $rs->close();

        // make sure that getupdatecount returns null

        $this->assertTrue((null === $stmt->getUpdateCount()), 'Expected getUpdateCount() to return NULL since last statement was a query.');

        $stmt->close();
    }

    /**
     * @test
     */
    public function executeUpdate()
    {
        $exch = DriverTestManager::getExchange('PreparedStatementTest.setBoolean');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->executeUpdate([true, 1]);
        $this->assertEquals(1, $stmt->getUpdateCount());
        $this->assertTrue((null === $stmt->getResultSet()), 'Expected getResultSet() to return NULL since last statement was an update.');
        $stmt->close();
    }

    //
    // Test the setters
    //

    /**
     * @test
     */
    public function setArray()
    {
        $exch = DriverTestManager::getExchange('PreparedStatementTest.setArray');
        $stmt = $this->conn->prepareStatement($exch->getSql());

        $array = ['Hello', "Bob's", 'Animals'];

        $stmt->setArray(1, $array);
        $stmt->setInt(2, 1); // pkey
        $stmt->executeUpdate();
        $stmt->close();

        $exch = DriverTestManager::getExchange('PreparedStatementTest.getArray');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setInt(1, 1);
        $rs = $stmt->executeQuery(ResultSet::FETCHMODE_NUM);
        $rs->next();

        $this->assertEquals($array, $rs->getArray(1));

        $rs->close();
        $stmt->close();

        // Injection test.  Can we add a string that causes the db to generate an SQL error
        $exch = DriverTestManager::getExchange('PreparedStatementTest.setArray');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setArray(1, "Normal TExt ' \' More # $%@ \\\\'''''\"\"'");
        $stmt->setInt(2, 1); // pkey
        $stmt->executeUpdate();
        $stmt->close();
    }

    /**
     * @test
     */
    public function setBoolean()
    {
        $exch = DriverTestManager::getExchange('PreparedStatementTest.setBoolean');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setBoolean(1, true);
        $stmt->setInt(2, 1); // pkey
        $stmt->executeUpdate();
        $stmt->close();

        $exch = DriverTestManager::getExchange('PreparedStatementTest.getBoolean');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setInt(1, 1);
        $rs = $stmt->executeQuery(ResultSet::FETCHMODE_NUM);
        $rs->next();

        $this->assertTrue($rs->getBoolean(1));

        $rs->close();
        $stmt->close();

        // Injection test.  Can we add a string that causes the db to generate an SQL error
        $exch = DriverTestManager::getExchange('PreparedStatementTest.setBoolean');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setBoolean(1, "Normal TExt ' \' More # $%@ \\\\'''''\"\"'");
        $stmt->setInt(2, 1); // pkey
        $stmt->executeUpdate();
        $stmt->close();
    }

    /**
     * @test
     */
    public function setDate()
    {
        $exch = DriverTestManager::getExchange('PreparedStatementTest.setDate');
        $stmt = $this->conn->prepareStatement($exch->getSql());

        $now = time();
        $stmt->setDate(1, $now);
        $stmt->setInt(2, 1); // pkey
        $stmt->executeUpdate();
        $stmt->close();

        $exch = DriverTestManager::getExchange('PreparedStatementTest.getDate');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setInt(1, 1);
        $rs = $stmt->executeQuery(ResultSet::FETCHMODE_NUM);
        $rs->next();

        // we are only storing w/ date resolution, so we need to fix that

        $this->assertEquals(date('d/m/Y', $now), $rs->getDate(1, 'd/m/Y'), 0, 'date() formatters did not produce expected results.');
        $this->assertEquals(strftime('%x', $now), $rs->getDate(1, '%x'), 0, 'strftime() formatters did not produce expected results.');

        $rs->close();
        $stmt->close();

        // Injection test.  Can we add a string that causes the db to generate an SQL error
        $exch = DriverTestManager::getExchange('PreparedStatementTest.setString');
        // intentionally using setString query; the idea is to test the setDate() method, not the db's ability
        // to accept string in date col
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setDate(1, "Normal TExt ' \' More # $%@ \\\\'''''\"\"'");
        $stmt->setInt(2, 1); // pkey
        $stmt->executeUpdate();
        $stmt->close();
    }

    /**
     * @test
     */
    public function setFloat()
    {
        $exch = DriverTestManager::getExchange('PreparedStatementTest.setFloat');
        $stmt = $this->conn->prepareStatement($exch->getSql());

        $stmt->setFloat(1, 8.55);
        $stmt->setInt(2, 1); // pkey
        $stmt->executeUpdate();
        $stmt->close();

        $exch = DriverTestManager::getExchange('PreparedStatementTest.getFloat');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setInt(1, 1);
        $rs = $stmt->executeQuery(ResultSet::FETCHMODE_NUM);
        $rs->next();

        $this->assertEquals(8.55, $rs->getFloat(1));

        $rs->close();
        $stmt->close();

        // Injection test.  Can we add a string that causes the db to generate an SQL error
        $exch = DriverTestManager::getExchange('PreparedStatementTest.setFloat');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setFloat(1, "Normal TExt ' \' More # $%@ \\\\'''''\"\"'");
        $stmt->setInt(2, 1); // pkey
        $stmt->executeUpdate();
        $stmt->close();
    }

    /**
     * @test
     */
    public function setInt()
    {
        $exch = DriverTestManager::getExchange('PreparedStatementTest.setInt');
        $stmt = $this->conn->prepareStatement($exch->getSql());

        $stmt->setInt(1, 50);
        $stmt->setInt(2, 1); // pkey
        $stmt->executeUpdate();
        $stmt->close();

        $exch = DriverTestManager::getExchange('PreparedStatementTest.getInt');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setInt(1, 1);
        $rs = $stmt->executeQuery(ResultSet::FETCHMODE_NUM);
        $rs->next();
        $this->assertEquals(50, $rs->getInt(1));

        $rs->close();
        $stmt->close();

        // Injection test.  Can we add a string that causes the db to generate an SQL error
        $exch = DriverTestManager::getExchange('PreparedStatementTest.setInt');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setInt(1, "Normal TExt ' \' More # $%@ \\\\'''''\"\"'");
        $stmt->setInt(2, 1); // pkey
        $stmt->executeUpdate();
        $stmt->close();
    }

    /**
     * @test
     */
    public function setNull()
    {
        $exch = DriverTestManager::getExchange('PreparedStatementTest.setNull');
        $stmt = $this->conn->prepareStatement($exch->getSql());

        $stmt->setNull(1);
        $stmt->setInt(2, 1); // pkey
        $stmt->executeUpdate();
        $stmt->close();

        $exch = DriverTestManager::getExchange('PreparedStatementTest.getNull');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setInt(1, 1);
        $rs = $stmt->executeQuery(ResultSet::FETCHMODE_NUM);
        $rs->next();
        $this->assertEquals(null, $rs->getInt(1));

        $rs->close();
        $stmt->close();
    }

    /**
     * @test
     */
    public function setString()
    {
        $exch = DriverTestManager::getExchange('PreparedStatementTest.setString');
        $stmt = $this->conn->prepareStatement($exch->getSql());

        $stmt->setString(1, 'Test String');
        $stmt->setInt(2, 1); // pkey
        $stmt->executeUpdate();
        $stmt->close();

        $exch = DriverTestManager::getExchange('PreparedStatementTest.getString');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setInt(1, 1);
        $rs = $stmt->executeQuery(ResultSet::FETCHMODE_NUM);
        $rs->next();
        $this->assertEquals('Test String', $rs->getString(1));

        $rs->close();
        $stmt->close();

        // Injection test.  Can we add a string that causes the db to generate an SQL error
        $exch = DriverTestManager::getExchange('PreparedStatementTest.setString');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setString(1, "Normal TExt ' \' More # $%@ \\\\'''''\"\"'");
        $stmt->setInt(2, 1); // pkey
        $stmt->executeUpdate();
        $stmt->close();
    }

    /**
     * @test
     */
    public function setTimeInjection()
    {
        // coming soon...

        // Injection test.  Can we add a string that causes the db to generate an SQL error
        $exch = DriverTestManager::getExchange('PreparedStatementTest.setString');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setTime(1, "Normal TExt ' \' More # $%@ \\\\'''''\"\"'");
        $stmt->setInt(2, 1); // pkey
        $stmt->executeUpdate();
        $stmt->close();
    }

    /**
     * @test
     */
    public function setTimestampInjection()
    {
        // Injection test.  Can we add a string that causes the db to generate an SQL error
        $exch = DriverTestManager::getExchange('PreparedStatementTest.setString');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setTimestamp(1, "Normal TExt ' \' More # $%@ \\\\'''''\"\"'");
        $stmt->setInt(2, 1); // pkey
        $stmt->executeUpdate();
        $stmt->close();
    }

    /**
     * @test
     */
    public function setTimestamp()
    {
        // 1) set the value
        $exch = DriverTestManager::getExchange('PreparedStatementTest.setTimestamp');
        $stmt = $this->conn->prepareStatement($exch->getSql());

        $now = time(); // by defnition unix timestamps are in UTC
        $stmt->setInt(1, 1);
        $stmt->setTimestamp(2, $now);
        $stmt->executeUpdate();
        $stmt->close();

        // 2) fetch the value
        $exch = DriverTestManager::getExchange('PreparedStatementTest.getTimestamp');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setInt(1, 1);
        $rs = $stmt->executeQuery(ResultSet::FETCHMODE_NUM);
        $rs->next();
        $this->assertEquals($now, $rs->getTimestamp(1, null));

        $rs->close();
        $stmt->close();
    }

    /**
     * @test
     */
    public function setTime()
    {
        // 1) set the value
        $exch = DriverTestManager::getExchange('PreparedStatementTest.setTime');
        $stmt = $this->conn->prepareStatement($exch->getSql());

        $now = time(); // by defnition unix timestamps are in UTC
        $stmt->setInt(1, 1);
        $stmt->setTime(2, $now);
        $stmt->executeUpdate();
        $stmt->close();

        // 2) fetch the value
        $exch = DriverTestManager::getExchange('PreparedStatementTest.getTime');
        $stmt = $this->conn->prepareStatement($exch->getSql());
        $stmt->setInt(1, 1);
        $rs = $stmt->executeQuery(ResultSet::FETCHMODE_NUM);
        $rs->next();
        $this->assertEquals($now, $rs->getTime(1, null));

        $rs->close();
        $stmt->close();
    }
}
