<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2009
 */

require_once(INC.'classes'.DS.'model.ort.php');
require_once(TEST_PATH.    'models/_model.test.php');
class TestOfOrtModel extends TestOfModels {

  public $model = false;

  function __construct() {
    parent::__construct('Test Ort model class');
  }

  function setUp() {
    $this->model = new ort ;
    list($this->_tableName, $this->_idName, $this->_columns) = $this->model->_test();
  }

  function testortload() {

  }
}

?>