<?php

require_once 'creole/CreoleBaseTest.php';

/**
 * Tests for the TableInfo class.
 *
 * -
 *
 * @author Hans Lellelid <hans@xmpl.org>
 *
 * @version $Revision: 1.2 $
 */
class TableInfoTest extends CreoleBaseTest
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    protected $conn;

    public function setUp()
    {
    }

    /**
     * Construct the class.  This is called before every test (method) is invoked.
     */
    public function __construct()
    {
        $this->conn = DriverTestManager::getConnection();
    }

    /**
     * @test
     */
    public function getColumns()
    {
        $table = $this->conn->getDatabaseInfo()->getTable('products');
        $cols = $table->getColumns();
        $this->assertEquals(sizeof($cols), 12);
    }

    /**
     * Test getting the products table.
     *
     * @test
     */
    public function getColumn()
    {
        $table = $this->conn->getDatabaseInfo()->getTable('products');
        $col = $table->getColumn('ProductID');
        $this->assertEquals($col->getName(), 'ProductID');
        $this->assertEquals($col->type, CreoleTypes :: INTEGER);

        $col = $table->getColumn('ProductName');
        $this->assertEquals($col->getName(), 'ProductName');
        $this->assertEquals($col->size, 40);
        $this->assertEquals($col->defaultValue, '');
        $this->assertEquals($col->type, CreoleTypes :: VARCHAR);

        $this->assertEquals($col->isAutoIncrement(), false);

        //i think we need more tests for every type of column...
    }

    /**
     * @test
     */
    public function getColumn_Scale()
    {
        $table = $this->conn->getDatabaseInfo()->getTable('products');
        $col = $table->getColumn('UnitPrice');
        print_r($col);
        $this->assertEquals($col->getName(), 'UnitPrice');
        $this->assertEquals($col->getType(), CreoleTypes::DOUBLE);
        $this->assertEquals($col->getScale(), 2);
    }

    /**
     * Test getting the indexes.
     *
     * @test
     */
    public function getIndexes()
    {
        $table = $this->conn->getDatabaseInfo()->getTable('indexes');
        $indexes = $table->getIndexes();
        $this->assertEquals(sizeof($indexes), 3); //not including primary key!!!

        $this->assertNotNull($this->findIndex($table, 'ProductNameIDX'));
        $this->assertNotNull($this->findIndex($table, 'ComplexIDX'));
        $this->assertNotNull($this->findIndex($table, 'UniqueComplexIDX'));
    }

    /**
     * Test getting the complex indexes info.
     *
     * @test
     */
    public function complexIndexInfo()
    {
        $table = $this->conn->getDatabaseInfo()->getTable('indexes');

        $index = $this->findIndex($table, 'ComplexIDX');
        $columns = $index->getColumns();
        $this->assertEquals(sizeof($columns), 3);

        $this->assertEquals($columns[0]->getName(), 'SupplierID');
        $this->assertEquals($columns[1]->getName(), 'CategoryID');
        $this->assertEquals($columns[2]->getName(), 'UnitPrice');
        $this->assertFalse($index->isUnique());
    }

    /**
     * Test getting the unique indexes info.
     *
     * @test
     */
    public function uniqueIndexInfo()
    {
        $table = $this->conn->getDatabaseInfo()->getTable('indexes');

        $index = $this->findIndex($table, 'UniqueComplexIDX');
        $columns = $index->getColumns();
        $this->assertEquals(sizeof($columns), 3);
        $this->assertTrue($index->isUnique());
    }

    /**
     * Test foreign key info.
     *
     * @test
     */
    public function foreignKeyInfo()
    {
        $table = $this->conn->getDatabaseInfo()->getTable('ref_table');

        $this->assertEquals(sizeof($table->getForeignKeys()), 2);
        $refs = $table->getForeignKey('RefID1')->getReferences();
        $this->assertEquals(sizeof($refs), 1);
        $this->assertEquals($refs[0][0]->getName(), 'RefID1');
        $this->assertEquals($refs[0][1]->getName(), 'UniqueCol1');

        $refs = $table->getForeignKey('RefID2')->getReferences();
        $this->assertEquals(sizeof($refs), 1);
        $this->assertEquals($refs[0][0]->getName(), 'RefID2');
        $this->assertEquals($refs[0][1]->getName(), 'UniqueCol2');
    }

    protected function findIndex($table, $name)
    {
        $indexes = $table->getIndexes();
        foreach ($indexes as $index) {
            if ($index->getName() == $name) {
                return $index;
            }
        }

        return null;
    }
}
