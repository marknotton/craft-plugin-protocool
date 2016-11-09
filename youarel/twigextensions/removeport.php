<?php
namespace Craft;

class removeport extends \Twig_Extension {

  public function getName() {
    return Craft::t('Remove Port');
  }


  public function getFilters() {
		return array(
      'removePort' => new \Twig_Filter_Method($this, 'removePort')
		);
	}

  public function getFunctions() {
    return array(
      'removePort' => new \Twig_Function_Method($this, 'removePort')
    );
  }


  public function removePort($url) {

    $urlParts = parse_url($url);

    unset($urlParts['port']);

    return implode($urlParts);

  }
}
