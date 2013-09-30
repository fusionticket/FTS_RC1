<?php
require_once('clock.php');

class TestOfClock extends UnitTestCase {
    function TestOfClock() {
        parent::__construct('Clock class test');
    }
    function testClockTellsTime() {
        $clock = new Clock();
        $this->assertEqual($clock->now(), time(), 'Now is the right time');
    }
    function testClockAdvance() {
        $clock = new Clock();
        $clock->advance(10);
        $this->assertEqual($clock->now(), time() + 10, 'Advancement');
    }

}
?>