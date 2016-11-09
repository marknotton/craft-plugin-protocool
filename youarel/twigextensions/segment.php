<?php
namespace Craft;

class segment extends \Twig_Extension {

  public function getName() {
    return Craft::t('Segment');
  }


  public function getFilters() {
		return array(
      'segment' => new \Twig_Filter_Method($this, 'segments')
		);
	}

  public function getFunctions() {
    return array(
      'segment' => new \Twig_Function_Method($this, 'segments')
    );
  }


  public function segments() {

    // Fail if no parameters are passed
    if ( func_num_args() < 1 ){
      return false;
    }

    $arguments = func_get_args();

    $segment = null;

    // When only one parameters is apssed
    if ( func_num_args() == 1 ){
      if (preg_match('/first|last/', $arguments[0]) || is_numeric($arguments[0])) {
        $segment = $arguments[0];
      } else if (is_string($arguments[0])) {
        return $arguments[0];
      }
    }

    // When two parameters are passed
    if ( func_num_args() == 2 ){
      foreach ($arguments as &$setting) {
        if (preg_match('/first|last/', $setting) || is_numeric($setting)) {
          $segment = $setting;
        } else if (is_string($setting)) {
          $url = $setting;
        }
      }
    }

    // If no url is passed, use the current page url
    if (!isset($url)) {
      $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    // Remove HTTP or HTTPS from url string
    $url = preg_replace("(^https?://)", "", $url);

    // If no segment is found just return the URL
    if (!isset($segment)) {
      return $url;
    }

    $segments = explode('/', $url);

    // If the segment is numeric and is equal or less than the total amount of segments in the url path:
    if (is_numeric($segment) && abs($segment) <= (count($segments) - 1)) {
      if (intval($segment) < 0) {
        // For numbers in the minus, count from the end of the segments array
        return $segments[count($segments) + intval($segment)];
      } else {
        // Positive numbers will return
        return $segments[intval($segment)];
      }
    }

    // Is the string of last or first is used, grab that relivent segment
    if ($segment == 'last') {
      return end($segments);
    } else if ($segment == 'first') {
      return $segments[0];
    }

    return false;

  }
}
