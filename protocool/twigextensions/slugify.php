<?php
namespace Craft;

use Twig_Filter_Method;

class slugify extends \Twig_Extension {

  public function getName() {
    return Craft::t('Slugify');
  }

  public function getFilters() {
    return array(
      'slugify' => new Twig_Filter_Method($this, 'slugify')
    );
  }

  public function slugify($string) {  
    return ElementHelper::createSlug((string)$string);
  }
}

