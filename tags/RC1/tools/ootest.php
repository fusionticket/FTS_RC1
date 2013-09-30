<?php

 abstract class albert {
  static $MYCLASS =  __CLASS__;
  protected static $_test = __CLASS__;


  var $ida =431;

   static function echo1(){
    echo '__METHOD__';
  }
}

class bert  extends albert {
  static $MYCLASS =  __CLASS__;
  protected static $_test = __CLASS__;

  var $idb = 123;

   function echo2(){
    echo ':';echo __METHOD__;

    print_r(debug_backtrace())  ;
  }

}

  function EncodeSecureCode() {
    $code = base64_encode('testing me here');
    echo urlencode ($code.'='); //  }
  }

  function DecodeSecureCode($codestr ='', $loging=false) {
    If (empty($codestr) and isset($_REQUEST['sor'])) $codestr =$_REQUEST['sor'];
   //
    If (!empty($codestr)) {
      //$code = urldecode( $code) ;
//      print_r( $codestr );
      $text = base64_decode($codestr);
      $code = explode(':',$text);
    //  print_r( $text );
      $code[0] = base_convert($code[0],36,10);
      $code[1] = base_convert($code[1],36,10);
//      print_r( $code );
//      print_r( $order );

      if (!is_object($order) and isset($this) and ($this instanceof Order)) $order = $this;
      if (!is_object($order)) $order = self::load($code[1], true);
      if (!is_object($order)) return -1;

      $md5 = $order->order_session_id.':'.$order->order_user_id .':'. $order->order_tickets_nr .':'.
                  $order->order_handling_id .':'. $order->order_total_price;

      if ($loging) {
        ShopDB::dblogging('decode:'.$text.'|'.$code[2].'='.md5($md5, true));
        ShopDB::dblogging('Code:  '.print_r( $code, true));
        ShopDB::dblogging('order: '.print_r( $order, true));
      }
//      if ($code[0] > time()) return -2;
      if ($code[1] <> $order->order_id) return -3;
      if ($code[2] <> md5($md5, true)) return -4;
      return true;
    } else
      return -5;
  }
  EncodeSecureCode();
  echo "<br>\n".$_POST['action'];
?>

<form method='post' >
   <input type="hidden" name="action" value="<?php EncodeSecureCode(); ?>">
   <input type='submit' value='login'>
</form>