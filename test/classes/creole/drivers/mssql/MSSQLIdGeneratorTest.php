<?php

require_once 'creole/IdGeneratorTest.php';

/**
 * Tests for the MSSQL IdGenerator class.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 *
 * @version $Revision: 1.1 $
 */
class MSSQLIdGeneratorTest extends IdGeneratorTest
{
    /**
     * Ensures that drivers are implementing the correct Id Method.
     *
     * @test
     */
    public function getMethod()
    {
        $this->assertEquals(IdGenerator::AUTOINCREMENT, $this->idgen->getIdMethod(), 0, 'MSSQL Id method should be AUTOINCREMENT (but is not)');
    }
}
