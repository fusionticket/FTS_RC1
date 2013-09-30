<?Php

if (!defined('DS')) {
/**
 * shortcut for / or \ (depending on OS)
 */
	define('DS', DIRECTORY_SEPARATOR);
}

function Filelist($dir, &$cont,  $search= '*.php') {
  if ($handle = opendir($dir)){
    while ($file = readdir($handle)) {
      if ($file != "." && $file != ".." && $file != ".svn"){
//        If (is_dir($dir.$file)){
//          filelist($dir.$file.DS, $cont, $search);
//        } else
        if (preg_match("/^{$filetype}(.*?\w+).php/", $file, $matches)) {
          $cont[] =  $dir.$file;
        }
      }
    }
    closedir($handle);
  }
}

$files = array();
require_once('..'.DS.'includes'.DS.'config'.DS.'defines.php');
require_once('..'.DS.'includes'.DS.'config'.DS.'init_shop.php');
require_once('..'.DS.'includes'.DS.'config'.DS.'init.php');

filelist('..'.DS.'includes'.DS.'config'.DS,$files);
filelist('..'.DS.'includes'.DS.'classes'.DS,$files);
filelist('..'.DS.'includes'.DS.'classes'.DS.'payments'.DS,$files);
filelist('..'.DS.'includes'.DS.'admin'.DS,$files);
//filelist('..'.DS.'includes'.DS.'install'.DS, $files);
foreach($files as $file){
 // echo $file,"<br>\n";
 require_once($file);
}


?>