<?php
	/**
    This file is part of WideImage.

    WideImage is free software; you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation; either version 2.1 of the License, or
    (at your option) any later version.

    WideImage is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public License
    along with WideImage; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

    * @package Tests
  **/
  define('ft_check','testing the system');

  define('INC', realpath(dirname(__FILE__) . '/../includes') . DIRECTORY_SEPARATOR);

  require_once(INC. "config".DIRECTORY_SEPARATOR."init_common.php");
  $_SHOP->session_name = "TestSession";

	define('TEST_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

//	if (isset($_SERVER['argc']) and $_SERVER['argc'] > 1)
//		$path_to_simpletest = $_SERVER['argv'][1];
//	else
	define('SIMPLE_TEST', INC.'libs'.DIRECTORY_SEPARATOR.'simpletest'. DIRECTORY_SEPARATOR);

	error_reporting(E_ALL);

	require_once(SIMPLE_TEST . 'unit_tester.php');
	require_once(SIMPLE_TEST . 'mock_objects.php');
	require_once(SIMPLE_TEST . 'reporter.php');
//	require_once(SIMPLE_TEST . 'extensions/webunit_reporter.php');
//	require_once(TEST_PATH.    'ftreport.php');

  function getFiles(&$rdi, $test) {
    if (!is_object($rdi)) return;
    for ($rdi->rewind();$rdi->valid();$rdi->next()) {
      if ($rdi->isDot()) continue;

      if ($rdi->isDir()) {
        if ($rdi->hasChildren())
          getFiles($rdi->getChildren(), $test);
      } elseif ($rdi->isFile() && preg_match('/\.test\.php$/', $rdi->getFilename())) {
    	//	echo "Found test: {$rdi->getPathname()}\n";
    		$test->addFile($rdi->getPathname());
    	}
    }
  }

	function collect_tests($dir, $test) {
    getFiles(new RecursiveDirectoryIterator('.'), $test);
	}

//echo "<pre>";
	$test = new TestSuite('FusionTicket tests');
	collect_tests(dirname(__FILE__), $test);
	$reporter = new HtmlReporter();
	$test->run($reporter);
//echo "</pre>";
