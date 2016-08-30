<?php
namespace Craft;

class baseurl extends \Twig_Extension {

  public function getName() {
    return Craft::t('Base URL');
  }

  public function getFunctions() {
    return array(
      'baseurl' => new \Twig_Function_Method($this, 'baseurl')
    );
  }

  // Returns the current base url, including protocal
  function baseurl(){

    // output: /myproject/index.php
    $currentPath = $_SERVER['PHP_SELF'];

    // output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index )
    $pathInfo = pathinfo($currentPath);

    // output: localhost
    $hostName = $_SERVER['HTTP_HOST'];

    // output: http://
    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://'?'https://':'http://';

    // return: http://localhost/myproject/
    return $protocol.$hostName.$pathInfo['dirname'];
  }
}
