<?php
require_once(INC.'classes'.DS.'class.shopdb.php');

class TestOfShopDB extends UnitTestCase {
    function __construct() {
      parent::__construct('ShopDB class test');
    }

    function testInit_CloseDB() {
      Global $_SHOP;
      $this->assertNull(ShopDB::$link);
      $this->assertTrue(ShopDB::init(false));
      $this->assertNotNull(ShopDB::$link);
      $this->assertIsA(ShopDB::$link,'mysqli');
      $this->assertTrue(ShopDB::close());
      $this->assertNull(ShopDB::$link);
      $this->assertTrue(ShopDB::init(false));
      $this->assertNotNull(ShopDB::$link);
    }

    function testGetServerInfo () {
      $this->dump(shopdb::GetServerInfo());
    }

    function assertTrans($intrans, $message = '%s'){
      if ($result = shopdb::query("SELECT @@autocommit")) {
          $row = $result->fetch_row();
          $result->free();
       //   $this->dump((($row[0])?'1':'0'). (($intrans)?'0':'1'));
          $this->assert(new TrueExpectation(), $row[0] == ($intrans)?0:1, $message);
      } else {
          $this->dump('assertTrans test');
      }
    }

    function testBegin () {
      $this->assertFalse(ShopDB::isTxn());
      $this->assertEqual(ShopDB::$db_trx_started, 0);
      $this->assertTrans(0);
      $this->assertTrue(ShopDB::begin('test script execution'));
      $this->assertTrue(ShopDB::isTxn());
      $this->assertEqual(ShopDB::$db_trx_started, 1);
      $this->assertTrans(1);
      $this->assertTrue(ShopDB::commit('test script execution'));
      $this->assertFalse(ShopDB::isTxn());
      $this->assertEqual(ShopDB::$db_trx_started, 0);
      $this->assertTrans(0);
    }

    function testCommit (){
      $this->assertTrue(ShopDB::begin('test script execution'));
      $this->assertTrue(ShopDB::begin('test script execution'));
      $this->assertEqual(ShopDB::$db_trx_started, 2);

      $this->assertTrue(ShopDB::commit('test script execution'));
      $this->assertTrans(1);
      $this->assertEqual(ShopDB::$db_trx_started, 1);
      $this->assertTrue(ShopDB::commit('test script execution'));
      $this->assertEqual(ShopDB::$db_trx_started, 0);
      $this->assertTrans(0);

      $this->assertFalse(ShopDB::commit('test script execution'));
      $this->assertEqual(ShopDB::$db_trx_started, 0);
      $this->assertTrans(0);
    }

    function testrollback () {
      $this->assertTrue(ShopDB::begin('test script execution'));
      $this->assertTrue(ShopDB::begin('test script execution'));
      $this->assertTrans(1);

      $this->assertTrue(ShopDB::rollback('test script execution'));
      $this->assertEqual(ShopDB::$db_trx_started, 0);
      $this->assertTrans(0);

      $this->assertFalse(ShopDB::rollback('test script execution'));
      $this->assertEqual(ShopDB::$db_trx_started, 0);
      $this->assertFalse(0);
    }

    function testquote () {
      $this->assertEqual(ShopDB::quote(null),'NULL');

      $this->assertEqual(ShopDB::quote(123),"'123'");
      $this->assertEqual(ShopDB::quote(123, false),"123");

      $this->assertEqual(ShopDB::quote('abc'),"'abc'");
      $this->assertEqual(ShopDB::quote('abc', false),"abc");

      $this->assertEqual(ShopDB::quote("a'b'c", false),"a\'b\'c");
      $this->assertEqual(ShopDB::quote('a"b"c', false),'a\"b\"c');
    }

    function testquery() {

    }

    function testinsert_id(){
    }

    function testquery_one_row () {
    }

    function testlock () {
    }

    function testunlock () {
    }

    function testaffected_rows() {
    }

    function testfetch_array() {
    }

    function testfetch_assoc() {
    }

    function testfetch_object() {
    }

    function testfetch_row() {
    }

    function testnum_rows() {
    }

    function testfreeResult() {
    }

    function testtblclose() {
    }

    function testreplacePrefix( ) {
    }

  	//function to find the number of fields in a recordSet
  	function testfieldCount() {
  	}

  	//function to find the field flags in a recordSet
  	function testfieldflags() {
  	}

  	//function to find the field name from recordSet
  	function testfieldname() {
  	}

  	//function to find the alias field name from recordSet
  	function testaliasFieldname() {
  	}

  	//function to find the table of a field name from recordSet
  	function testFieldtable() {
  	}

    function testFieldList () {
    }

    function testFieldListExt () {
    }

    function testFieldExists () {
    }

    function testTableList () {
    }

    function testTableExists () {
    }

    function testdblogging() {
    }
}
?>