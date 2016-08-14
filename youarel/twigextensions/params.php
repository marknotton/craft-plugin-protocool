<?php
namespace Craft;

class params extends \Twig_Extension {

  public function getName() {
    return Craft::t('Params');
  }


  public function getFilters() {
		return array(
      'params' => new \Twig_Filter_Method($this, 'params')
		);
	}

  public function getFunctions() {
    return array(
      'params' => new \Twig_Function_Method($this, 'params')
    );
  }

  function params(){

    // Fail if not parameters are passed
    if ( func_num_args() < 1 ){
      return false;
    }

    $arguments = func_get_args();

    $argumentsCount = func_num_args();

    $url      = null;
    $variable = null;
    $value    = null;
    $newParams= null;

    if ( isset($arguments) ){
      foreach ($arguments as &$setting) {
        if ( gettype($setting) == 'array') {
          $newParams = $setting;
        } else if (filter_var($setting, FILTER_VALIDATE_URL) || strpos($setting, '.') || strpos($setting, '/')) {
          $url = $setting;
        } else if ( is_null($variable)) {
          $variable = $setting;
        } else if ( is_null($value)) {
          $value = $setting;
        }
      }
    }

    // If an array of settings was passed, ignore any strings that might have been passed
    if (!is_null($newParams) ) {
      $variable = null;
      $value    = null;
    }

    // If both a variable and value were set, create a new array
    if (!is_null($variable) && !is_null($value)) {
      $newParams = array($variable => $value);
    }

    // If a variable was set, but not a value... assume this was meant to be the opposite. So a value can be add/updated even without the variable name
    if (!is_null($variable) && is_null($value)) {
      $newParams = array(false => $variable);
    }

    // Use current url if one was not defined
    if (is_null($url)) {
      $url = (isset($_SERVER['HTTPS']) ? "https" : "http")."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }

    // When no value or an array were passed, just return the original url
    if (is_null($newParams) && is_null($value)) {
      return $url;
    }

    // echo 'url: '.$url.'<br>';
    // echo 'variable: '.$variable.'<br>';
    // echo 'value: '.$value.'<br>';

    // Returns any existing parameters from the $url, as a string
    $currentParams = parse_url($url, PHP_URL_QUERY);

    // Update parameters if they exist
    if (!is_null($currentParams)) {
      foreach(explode("&", $currentParams) as $query) {

        // Asign $key as the variable, and $value as the value. If a variable isn't defined, set it to null
        list($key, $value) = (strpos($query, '=') !== false) ? explode("=", $query) : [null, $query];

        if(array_key_exists($key, $newParams)) {
          if($newParams[$key]) {
            // Updates first instance of an existing parameter
            $url = preg_replace('/'.$key.'='.$value.'/', $key.'='.$newParams[$key], $url);
          } else {
            // Removes any duplicates
            $url = preg_replace('/&?'.$key.'='.$value.'/', '', $url);
          }
        }
      }
    }

    // Add any new parameters
    foreach($newParams as $key => $value) {
      if($value && !preg_match('/'.$key.'=/', $url)) {
        $url .= (parse_url($url, PHP_URL_QUERY) ? '&' : '?').($key != false ? $key.'=' : '').$value;
      }
    }

    return $url;
  }
}
