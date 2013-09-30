<?PHP
define('ft_check','shop');
  require_once('../includes/config/init_common.php');
//  require_once('../includes/config/init.php');
  session_name('pmp_test');

  session_start();
  if (empty($_REQUEST)) {
    session_destroy();
    session_start();
  }
  //print_r($_REQUEST);
  $imagesize = 20;
  error_reporting(0);
  $pmp_id = (int)is($_REQUEST['pmp_id'],167);
  if (!isset($_SESSION['pmp']) || $pmp_id <>$_SESSION['pmp']->pmp_id) {
   $pmp = $_SESSION['pmp'] = PlaceMapPart::loadfull($pmp_id);
    if ($pmp) $stats = $_SESSION['stats'] = $pmp->getstats();
  }
  if (!$pmp = $_SESSION['pmp']) {
    return;
  }
  $stats = $_SESSION['stats'];

  switch ($_REQUEST['load']) {
     case 'zones' :
        $responce->page = 1;
        $responce->total = count($pmp->zones);
        $responce->records = count($pmp->zones);
        if (!empty($pmp->zones)) {
          $i=0;
          foreach($pmp->zones as $zone_ident => $zone) {
            $responce->rows[$i]['id']=$zone_ident;
            $responce->rows[$i]['cell']=array('color' =>"<span style='background-color:{$zone->pmz_color};'><b>&emsp;</b></span> ",
                                              'name'=>"$zone_ident {$zone->pmz_name}",
                                              'seats' =>$stats->zones[$zone_ident]);
            $i++;
          }
        }
        echo json_encode($responce);
        break;
     case 'cats' :
        $responce->page = 1;
        $responce->total = count($pmp->categories);
        $responce->records = count($pmp->categories);
        if (!empty($pmp->categories)) {
          $i=0;
          foreach($pmp->categories as $ident => $category) {
            $responce->rows[$i]['id']=$ident;
            $responce->rows[$i]['cell']=array("<span style='background-color:{$category->category_color};'><b>&emsp;</b></span> ",
                                              "$ident {$category->category_name}",
                                              "{$category->category_price} {$_SHOP->organizer_currency}",
                                              $stats->categories[$ident],
                                              $category->category_numbering);
            $i++;
          }
        }
        echo json_encode($responce);
        break;
     case 'resizegrid':
       $size['height'] = isset($_POST['height'])? ($_POST['height']):$pmp->pmp_height*($imagesize+2);
       $size['width'] = isset($_POST['width'])? ($_POST['width']):$pmp->pmp_width*($imagesize+2);
       $size['height'] = (int) round ( $size['height'] /($imagesize+2));
       $size['width'] = (int) round ( $size['width'] /($imagesize+2));
       if ($pmp->pmp_height > $size['height']) {
         $pmp->data = array_splice($pmp->data,0, $size['height']);
       }
       if ($pmp->pmp_height < $size['height']) {
        $pmp->data = array_pad($pmp->data, $size['height'], array($size['width']));
       }
       foreach($pmp->data as $key=>$valye) {
         if ($pmp->pmp_width > $size['width']) {
           $pmp->data[$key] = array_splice($pmp->data[$key],0, $size['width']);
         }
         if ($pmp->pmp_width < $size['width']) {
           $pmp->data[$key] = array_pad($pmp->data[$key],$size['width'],array(0));
         }
       }
       $pmp->pmp_width  = $size['width'];
       $pmp->pmp_height = $size['height'];
       $_SESSION['pmp'] = $pmp;
       break;
     case 'setgrid':
      $type   = (isset($_POST['type']))? (string)($_POST['type']):'';
      $myid   = (isset($_POST['id']))? ($_POST['id']):0;
      $select = (isset($_POST['selection']))? ($_POST['selection']):'';
 //     $select = explode('||',$select);
      $array = array();
      foreach ($select as $key) {
        $key = explode('_',$key);
        $array[$key[1]][$key[2]] = $key;
      }
      switch ($type) {
        case 'RE':
        case 'RW':
        case 'SS':
        case 'SN':
        case 'E':
        case 'T':
          $pmp->set_label($type, $array, $myid);

          break;
        case 'CLR':
           $pmp->clear($array);
           break;
          case 'SEAT':
            var_dump($zone = (int)substr($_POST['zone_id'],3 ));
            if (!empty($zone)) {
              $pmp->set_zone($zone,$array,false);
            };
            var_dump($zone = (int)substr($_POST['cat_id'],3 ));
            if (!empty($zone)) {
              $pmp->set_category($zone,$array,false);
            };
            var_dump($pmp->data);
            break;

        default:
          ;
      } // switch
       $_SESSION['pmp'] = $pmp;
       ;
      break;

    case 'grid':
      $responce->maxrows = $pmp->pmp_height;
      $responce->maxcols = $pmp->pmp_width;
      $responce->shift   = $pmp->pmp_shift;
      $responce->data    = array();
      $minsize = array(0,0);
        for($j = 0;$j < $pmp->pmp_height;$j++) { //
            for($k = 0;$k < $pmp->pmp_width;$k++) {
                $label = $pmp->data[$j][$k];
                $name = 's_'.$k.'_'.$j;
                if ($label[PM_ZONE] === 'L') {
                   if ($label[PM_LABEL_TYPE] === 'T' ) {
                     $responce->data[$name] = array(array($k,$j,$label[PM_LABEL_SIZE],1),$label[PM_LABEL_TYPE],$label[PM_LABEL_TEXT]);
                     calcmin($minsize,$j ,$k+ $label[PM_LABEL_SIZE], 1);
                   } else {
                     $responce->data[$name] = array(array($k,$j,1,1),$label[PM_LABEL_TYPE]);
                     calcmin($minsize,$j ,$k , 1);
                   }

                } elseif ($label[PM_ZONE] || $label[PM_CATEGORY]) {
                    $label = array_pad($label,PM_STATUS,0);
                    $responce->data[$name] = array(array($k,$j,1,1),'S',$label[PM_ROW],$label[PM_SEAT],$label[PM_CATEGORY],$label[PM_ZONE],is($label[PM_STATUS],0),is($label[PM_ID],0));
                    calcmin($minsize,$j ,$k , 1);
                }
            }
        }
        $responce->minrows = $minsize[0];
        $responce->mincols = $minsize[1];
        $responce->zones   = array();
        $responce->categories = array();
        foreach ($pmp->zones as $data) {
         $responce->zones[]      = $data;
        }
        foreach ($pmp->categories as $data) {
          $responce->categories[]      = $data;
        }
        echo json_encode($responce);
       break;
    default: var_dump($pmp);

  }

  function placeMapStyle($style){
    return vsprintf('top: %dpx;left: %dpx; height: %dpx; width: %dpx; ',$style);
  }

  function calcmin(&$minsize, $x,$y,$imagesize){
    if ($minsize[0]<($x+1)) { $minsize[0] = ($x+1); }
    if ($minsize[1]<($y+1)) { $minsize[1] = ($y+1); }
  }

?>