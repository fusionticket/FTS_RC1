<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2011
 */

	$lpat=implode(array('nl','en'),"|");
var_dump(preg_match_all("/$lpat/",'nl,en-us;q=0.7,en;q=0.3',$res ));
var_dump($res);


?>