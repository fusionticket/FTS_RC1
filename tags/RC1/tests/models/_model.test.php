<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2009
 */

require_once(INC.'classes'.DS.'model.php');

class TestOfModels extends UnitTestCase {

  public $model = false;

  function __construct($lable = 'Test model class') {
    parent::__construct($lable);
  }

  function setUp() {
    $this->model = false;
  }

  function tearDown() {
    unset($this->model);
  }

  function testgetFieldtype(){
    if (!$this->model) {
      $this->assertIdentical(model::MDL_NONE     , 0 );
      $this->assertIdentical(model::MDL_MANDATORY, 1 );
      $this->assertIdentical(model::MDL_IDENTIFY , 2 );

      $key = 'test';
      $this->assertIdentical(model::getFieldtype($key), 0 );
      $this->assertIdentical($key, 'test' );
      $key = '*test';
      $this->assertIdentical(model::getFieldtype($key), 1 );
      $this->assertIdentical($key, 'test' );
      $key = '#test';
      $this->assertIdentical(model::getFieldtype($key), 2 );
      $this->assertIdentical($key, 'test' );
    }
  }

  function testModelProperties() {
    if ($this->model) {

      $this->assertNotIdentical($this->_tableName, '' );
      $this->assertTrue(Shopdb::TableExists($this->_tableName), "Table unknown: {$this->_tableName}");

      $this->defs = & ShopDB::FieldListExt($this->_tableName);
      $cols = $this->_columns;
  //    print_r($this->defs);
      $iskey = false;
      foreach($cols as $key) {
        $type = model::getFieldtype($key);
        $this->assertTrue($ok = array_key_exists($key, $this->defs), "Field unknown: $key.");
        if ($ok and $key == $this->_idName) {
          $this->assertEqual($this->defs[$key]->Key, 'PRI',"_idName: {$key} is not a primery index Field ");
          $iskey = true;
        } elseif ($ok && $this->defs[$key]->Null == 'NO') {
          $this->assertEqual($type, model::MDL_MANDATORY, "Mandatory for $key missing.");
        }
      }
      if ($this->_idName && !$iskey) {
        $this->assertTrue($ok = array_key_exists($this->_idName, $this->defs), "_idName unknown: {$this->_idName}.");
        if ($ok)
          $this->assertEqual($this->defs[$this->_idName]->Key, 'PRI',"_idKey: {$this->_idName} is not a primery index Field ");
      }
    }
  }
}

?>