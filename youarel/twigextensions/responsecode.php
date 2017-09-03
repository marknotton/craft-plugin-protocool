<?php
namespace Craft;

class responsecode extends \Twig_Extension {

  public function getName() {
    return Craft::t('Response Code');
  }

  public function getFunctions() {
    return array('responsecode' => new \Twig_Function_Method($this, 'responsecode'));
  }

  function responsecode(){
    return http_response_code();
  }
}
