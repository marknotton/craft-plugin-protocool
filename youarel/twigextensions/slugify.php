<?php
namespace Craft;

class slugify extends \Twig_Extension {

  public function getName() {
    return Craft::t('Slugify');
  }

  public function getFilters() {
    return array('slugify' => new \Twig_Filter_Method($this, 'slugify'));
  }

  public function getFunctions() {
    return array('slugify' => new \Twig_Function_Method($this, 'slugify'));
  }

  function slugify($string){
    return ElementHelper::createSlug($string);
  }
}
