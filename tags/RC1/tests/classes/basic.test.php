<?php
require_once(INC.'classes/basics.php');
//require_once(INC.'config/init.php');

/**
 * TestOfBasic
 *
 * @package
 * @author niels
 * @copyright Copyright (c) 2009
 * @version $Id$
 * @access public
 */
class TestOfBasic extends UnitTestCase {
    function __construct() {
       parent::__construct('Basics.php test');
    }

    function setup(){
       if(function_exists("date_default_timezone_set") and
          function_exists("date_default_timezone_get")) {
         @date_default_timezone_set(@date_default_timezone_get());
       }
    }

    function testBasicFormatDate() {
       $this->assertIdentical('01 05 2009', formatdate('2009-05-01',"%d %m %Y"),'2009-05-01');
       $this->assertIdentical('01 05 2009', formatdate('01-05-2009',"%d %m %Y"),'01-05-2009');
       $this->assertIdentical('01 05 2009', formatdate('05/01/2009',"%d %m %Y"),'05/01/2009');
       $this->assertIdentical('01 05 2009', formatdate('01.05.2009',"%d %m %Y"),'01.05.2009');
    }

    /**
     * print out type and content of the given variable if DEBUG-define (in config/core.php) > 0
     * @param mixed $var     Variable to debug
     * @param boolean $escape  If set to true variables content will be html-escaped
     */
    function testDebug() {
    }

    /**
     * Recursively strips slashes from all values in an array
     * @param mixed $value
     * @return mixed
     */
    function testStripslashesDeep(){
    }
    /**
     * Recursively urldecodes all values in an array
     * @param mixed $value
     * @return mixed
     */
    function testUrldecodeDeep(){
    }

    /** write a string to the log in tmp/logs
     *@param string $what string to write to the log
     *@param int $where log-level to log (default: KATA_DEBUG)
     */
    function testWriteLog() {
    }

    /**
     * Loads files from the from LIB-directory
     * @param string filename without .php
     */
    function Testuses() {
    }

    function TestFindClass() {
    }

    /**
     * Gets an environment variable from available sources.
     * Used as a backup if $_SERVER/$_ENV are disabled.
     *
     * @param  string $key Environment variable name.
     * @return string Environment variable setting.
     */
    function testEnv(){
    }


    /**
     * Merge a group of arrays
     * @param array First array
     * @param array etc...
     * @return array All array parameters merged into one
     */
    function testAm() {
    }


    /**
     * Convenience method for htmlspecialchars. you should use this instead of echo to avoid xss-exploits
     * @param string $text
     * @return string
     */
    function testhtmlspecialchars() {
    }

    /**
     * convenience method to check if given value is set. if so, value is return, otherwise the default
     * @param mixed $arg value to check
     * @param mixed $default value returned if $value is unset
     */
    function testIS() {
    }

    function testEmpt(){
    }

    function testCon() {
    }

    function testconstructBase() {
    }

    function testEscape (){
      // _esc
    }

    function testcheck_event(){
    }

    function testcheck_system() {
    }

    function testformatAdminDate(){
    }

    function testformatTime(){
    }

    function teststringDatediff() {
    }

    function testsubtractDaysFromDate() {
    }

    function testtrace(){
    }

    function testaddDaysToDate() {
    }

    function testget_loc() {
    }

    function teststripTagsInBigString(){
    }

    function testwp_entities(){
    }

    function testclean() {
    }

    /**
     * This function creates a md5 password code to allow login true WWW-Authenticate
     *
     */
    function testmd5pass() {
    }

    /**
     * This function creates a md5 password code to allow login true WWW-Authenticate
     *
     */
    function testsha1pass() {
    }

    function testisBase64Encoded(){
    }
}
?>