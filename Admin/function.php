<?php
  function pr($arr)
  {
    echo '<pre>';
    print_r($arr);
  }
  
  function prx($arr)
  {
    echo '<pre>';
    print_r($arr);
    die();
  }
  
  function getSafeValue($con, $str)
  {
    if ($str != '') {
      $str = trim($str);
      $str = stripslashes($str);
      $str = htmlspecialchars($str);
      return pg_escape_string($con, $str);
    }
  }

