<?php
class Clock {
    var $_offset;

    function Clock() {
        $this->_offset = 0;
    }

    function now() {
        return time() + $this->_offset;
    }

    function advance($offset) {
        $this->_offset += $offset;
    }
}
?>