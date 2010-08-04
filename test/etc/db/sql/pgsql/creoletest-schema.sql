
-----------------------------------------------------------------------------
-- products
-----------------------------------------------------------------------------

DROP TABLE products CASCADE;


CREATE TABLE products
(
	ProductID INTEGER  NOT NULL,
	ProductName VARCHAR(40) default '' NOT NULL,
	SupplierID INTEGER,
	CategoryID INTEGER,
	QuantityPerUnit VARCHAR(20),
	UnitPrice DECIMAL(12,2),
	UnitsInStock INTEGER,
	UnitsOnOrder INTEGER,
	ReorderLevel INTEGER,
	Discontinued BOOLEAN default 'f' NOT NULL,
	Notes TEXT,
	OrderDate DATE,
	PRIMARY KEY (ProductID)
);

COMMENT ON TABLE products IS '';


SET search_path TO public;
-----------------------------------------------------------------------------
-- blobtest
-----------------------------------------------------------------------------

DROP TABLE blobtest CASCADE;


CREATE TABLE blobtest
(
	BlobID INTEGER  NOT NULL,
	BlobName VARCHAR(30)  NOT NULL,
	BlobData BYTEA  NOT NULL,
	PRIMARY KEY (BlobID)
);

COMMENT ON TABLE blobtest IS '';


SET search_path TO public;
-----------------------------------------------------------------------------
-- clobtest
-----------------------------------------------------------------------------

DROP TABLE clobtest CASCADE;


CREATE TABLE clobtest
(
	ClobID INTEGER  NOT NULL,
	ClobName VARCHAR(30)  NOT NULL,
	ClobData TEXT  NOT NULL,
	PRIMARY KEY (ClobID)
);

COMMENT ON TABLE clobtest IS '';


SET search_path TO public;
-----------------------------------------------------------------------------
-- idgentest
-----------------------------------------------------------------------------

DROP TABLE idgentest CASCADE;

DROP SEQUENCE idgentest_seq;

CREATE SEQUENCE idgentest_seq;


CREATE TABLE idgentest
(
	ID INTEGER  NOT NULL,
	Name VARCHAR(40) default '' NOT NULL,
	PRIMARY KEY (ID)
);

COMMENT ON TABLE idgentest IS '';


SET search_path TO public;
-----------------------------------------------------------------------------
-- temporaltest
-----------------------------------------------------------------------------

DROP TABLE temporaltest CASCADE;


CREATE TABLE temporaltest
(
	ID INTEGER  NOT NULL,
	timecol TIME,
	datecol DATE,
	timestampcol TIMESTAMP,
	PRIMARY KEY (ID)
);

COMMENT ON TABLE temporaltest IS '';


SET search_path TO public;
-----------------------------------------------------------------------------
-- temporaltest
-----------------------------------------------------------------------------

DROP TABLE temporaltest CASCADE;


CREATE TABLE temporaltest
(
	ID INTEGER  NOT NULL,
	timecol TIME,
	datecol DATE,
	timestampcol TIMESTAMP,
	PRIMARY KEY (ID)
);

COMMENT ON TABLE temporaltest IS '';


SET search_path TO public;
-----------------------------------------------------------------------------
-- indexes
-----------------------------------------------------------------------------

DROP TABLE indexes CASCADE;


CREATE TABLE indexes
(
	ProductName VARCHAR(50),
	SupplierID INTEGER,
	CategoryID INTEGER,
	UnitPrice DECIMAL(12,2),
	CONSTRAINT UniqueComplexIDX UNIQUE (SupplierID,CategoryID,UnitPrice)
);

COMMENT ON TABLE indexes IS '';


SET search_path TO public;
CREATE INDEX ProductNameIDX ON indexes (ProductName);

CREATE INDEX ComplexIDX ON indexes (SupplierID,CategoryID,UnitPrice);

-----------------------------------------------------------------------------
-- fk_test
-----------------------------------------------------------------------------

DROP TABLE fk_test CASCADE;


CREATE TABLE fk_test
(
	UniqueCol1 INTEGER,
	UniqueCol2 INTEGER,
	CONSTRAINT fk_test_U_1 UNIQUE (UniqueCol1),
	CONSTRAINT fk_test_U_2 UNIQUE (UniqueCol2)
);

COMMENT ON TABLE fk_test IS '';


SET search_path TO public;
-----------------------------------------------------------------------------
-- ref_table
-----------------------------------------------------------------------------

DROP TABLE ref_table CASCADE;


CREATE TABLE ref_table
(
	RefID1 INTEGER  NOT NULL,
	RefID2 INTEGER  NOT NULL,
	PRIMARY KEY (RefID1,RefID2)
);

COMMENT ON TABLE ref_table IS '';


SET search_path TO public;
ALTER TABLE ref_table ADD CONSTRAINT ref_table_FK_1 FOREIGN KEY (RefID1) REFERENCES fk_test (UniqueCol1);

ALTER TABLE ref_table ADD CONSTRAINT ref_table_FK_2 FOREIGN KEY (RefID2) REFERENCES fk_test (UniqueCol2);
