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

    // $urlParts = parse_url($url);
    //
    // echo $urlParts['scheme'].'://'.$urlParts['host'].$urlParts['path'].'<br /><br />';
    //
    // echo $urlParts['scheme'].$urlParts['host'].$urlParts['path'].'<br /><br />';
    //
    // echo $urlParts['scheme'].'<br />';
    // echo $urlParts['host'].'<br />';
    // echo $urlParts['path'].'<br /><br />';
    //
    // echo "<pre>";
    //   var_dump($urlParts);
    // echo "</pre><br /><br />";
    //
    // echo str_replace(":3000", '', $url).'<br /><br />';

    return $url;

  }
}
