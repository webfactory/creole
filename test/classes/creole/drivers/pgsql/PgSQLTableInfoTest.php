<?php

require_once 'creole/metadata/TableInfoTest.php';

/**
 * PgSQLTableInfoTest tests.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 *
 * @version $Revision: 1.1 $
 */
class PgSQLTableInfoTest extends TableInfoTest
{
    /**
     * Test getting the products table.
     *
     * @test
     */
    public function getColumn()
    {
        $table = $this->conn->getDatabaseInfo()->getTable('products');
        $col = $table->getColumn('productid');
        $this->assertEquals($col->getName(), 'productid');
        $this->assertEquals($col->type, CreoleTypes :: INTEGER);

        $col = $table->getColumn('productname');
        $this->assertEquals($col->getName(), 'productname');
        $this->assertEquals($col->size, '40');
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
        $col = $table->getColumn('unitprice');
        $this->assertEquals($col->getName(), 'unitprice');
        $this->assertEquals($col->getType(), CreoleTypes::NUMERIC);
        $this->assertEquals($col->getScale(), '2');
        $this->assertEquals($col->getPrecision(), '12');
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

        $this->assertNotNull($this->findIndex($table, 'productnameidx'));
        $this->assertNotNull($this->findIndex($table, 'complexidx'));
        $this->assertNotNull($this->findIndex($table, 'uniquecomplexidx'));
    }

    /**
     * Test getting the complex indexes info.
     *
     * @test
     */
    public function complexIndexInfo()
    {
        $table = $this->conn->getDatabaseInfo()->getTable('indexes');

        $index = $this->findIndex($table, 'complexidx');
        $columns = $index->getColumns();
        $this->assertEquals(sizeof($columns), 3);

        $this->assertEquals($columns[0]->getName(), 'supplierid');
        $this->assertEquals($columns[1]->getName(), 'categoryid');
        $this->assertEquals($columns[2]->getName(), 'unitprice');
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

        $index = $this->findIndex($table, 'uniquecomplexidx');
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
        $refs = $table->getForeignKey('ref_table_fk_1')->getReferences();
        $this->assertEquals(sizeof($refs), 1);
        $this->assertEquals($refs[0][0]->getName(), 'refid1');
        $this->assertEquals($refs[0][1]->getName(), 'uniquecol1');

        $refs = $table->getForeignKey('ref_table_fk_2')->getReferences();
        $this->assertEquals(sizeof($refs), 1);
        $this->assertEquals($refs[0][0]->getName(), 'refid2');
        $this->assertEquals($refs[0][1]->getName(), 'uniquecol2');
    }
}
