<?php

class ftReporter extends HtmlReporter {

  function paintHeader($test_name) {
    parent::paintHeader($test_name) ;
    echo 'Header<br>';
  }

  function paintFooter($test_name) {
    parent::paintFooter($test_name) ;
    echo 'Footer<br>';

  }

  function paintStart($test_name, $size) {
    parent::paintStart($test_name, $size);
  }

  function paintEnd($test_name, $size) {
    parent::paintEnd($test_name, $size);
  }

  function paintPass($message) {
    parent::paintPass($message);
    echo 'pass:' + $message;
  }

  function paintFail($message) {
    parent::paintFail($message);
  }
}

?>