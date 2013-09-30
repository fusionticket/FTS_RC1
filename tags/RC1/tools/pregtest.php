<?php
$data = array();



  
  function replaceVar($matches){
    array_push($this->vars,$matches[1]);
    $data['order_email'] = "email@email.com";
    $data['order_name'] = "Chris Jenkins";
    return ''.$data[$matches[1]].'';
  }
  
  function varsToValues($string){
    return preg_replace_callback('/\$(\w+)/','replaceVar',$string);
  }
echo "Start Tests <br/>";


$text[] = 'this is text $name with vars in it';
$text[] = 'this is text with quotes email="$order_email" with vars in it name="$order_name"';

echo "Orginal Text <br/>";
foreach($text as $value){
  echo $value."<br/>";    
}
echo "<br/> Replace \" for \\\" <br/>";
foreach($text as $value){
  echo str_replace('"','\"',$value)."<br/>";  
}

echo '<br/> Replace $name with email data version <br/>';

foreach($text as $value){
  echo varsToValues($value)."<br/>";
}

//preg_replace_callback()



?>