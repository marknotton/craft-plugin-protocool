<?php
namespace Craft;

class Protocool_SegmentsService extends BaseApplicationComponent {

  public $status = null;
  private $cacheEntry = [];
  private $cacheSection = [];
  private $errors = [
    '400'=>'Bad Request',
    '403'=>'Forbidden',
    '404'=>'Page Not Found',
    '500'=>'Internal Server Error',
    '503'=>'Maintenance'
  ];

  public function setStatus() {
    $this->status = http_response_code();
  }

    // Return global data
  public function getGlobal($handle, $set = "settings") {
    if ( isset(craft()->globals->getSetByHandle($set)->$handle)) {
      return craft()->globals->getSetByHandle($set)->$handle;
    }
  }

  // Grab a particular field from a partcilar page
  // See 'entry' function below
  // $field = section or category group
  // $id    = id or slug of entry
  public function content($field = null, $id = null, $section = null) {

    $entry = $this->entry($id, $section, false);

    if (isset($entry) && isset($field)) {
      return $entry->$field;
    }
  }

  // Grab an array of commonly useed Section data by defining just the id or slug and the entries given section
  // section - Returns {array}  - Entry section name
  // title {string}  -  Section title
  // name {string}   -  Section name
  // id {string}     -  Section ID
  // handle {string} -  Section handle
  // type {string}   -  Section Type - Channel, Structure, or Single
  // url {string}    -  Section absolute url
  // uri {string}    -  Section relative url
  public function section($id) {
    // Create a unique name for caching
    $cache_name = empty($id) ? 'current' : (string)$id;

    if (!array_key_exists($cache_name, $this->cacheSection)) {

      if (is_string($id)) {
        // If $id is a string, loop through all available sections until one matches
        $sections = craft()->sections->getAllSections();
        while (list(, $sec) = each($sections)) {
          if ($sec->handle == $id) {
            $section = $sec;
            break;
          }
        }
      } else {
        // Otherwise just use the ID number
        $section = craft()->sections->getSectionById($id);
      }

      if (isset($section)) {
        $results = [
          'title' => $section->name,
          'name' => $section->name,
          'id' => $section->id,
          'handle' => $section->handle,
          'type' => $section->type,
          'url' => $section->handle, // TODO
          'uri' => $section->handle, // TODO
          // 'association' => $section->association,
        ];

        $this->cacheSection[$cache_name] = $results;
      } else {
        // When a section doesn't technically exist, create faux results.

        if (is_string($id)) {

          $title = str_replace(['_', '-'], ' ', $id);

          $results = [
            'id' => null,
            'title' => preg_replace('/\s+/', ' ', ucwords($title)),
            'slug' => preg_replace('/\s+/', '-', $id),
            'url' => '/'.$id.'/',
            'uri' => $id,
            'status' => 'live',
            'level' => false,
            'parent' => false,
            'child' => false,
          ];
        }
        $this->cacheSection[$cache_name] = $results;
      }
    }
    return $this->cacheSection[$cache_name];
  }

  // Grab an array of commonly useed Entry data by defining just the id or slug and the entries given section

  // $id      = id or slug
  // $section = section or category group
  // $full    = if 'true', the entire given entry object will be returned, rather than the common attributes

  // {{ quick.entry(2)['title'] }} - Returns homepage title
  // {{ quick.entry('about', '')['title'] }} - Returns homepage title

  // Returns an array of usable information. This is cached the first time it is called.
  // So no additial queries or http requests will trigger should the same criteria be used on the same page more than once
  // id      - Returns {string} - Entry ID
  // title   - Returns {string} - Entry title
  // slug    - Returns {string} - Entry slug
  // url     - Returns {string} - Entry absolute url
  // uri     - Returns {string} - Entry relative url
  // snippet - Returns {string} - Entry Snippet
  // status  - Returns {string} - Entry status
  // level   - Returns {string} - Entry hiarchy level
  // parent  - Returns {bool}   - Checks if entry has a parent
  // child   - Returns {bool}   - Checks if entry has a child
  // type    - Returns {string} - Returns Channel, Structure, Single, or Category
  // section - Returns {array}  - Entry section details. See section function above

  // Reverts to 'best-guess' fallbacks if special circomstance arise, like 404 error pages, or page exist but not in the cms

  public function entry() {

    $id      = null;
    $section = null;
    $full    = false;

    // Atleast one single string arugment should be passed
    if ( func_num_args() >= 1 ){
      $id = func_get_arg(0);
    }

    foreach (array_slice(func_get_args(), 1) as &$setting) {
      if ( gettype($setting) == 'string' ) {
        $section = $setting;
      }

      if ( gettype($setting) == 'boolean' ) {
        $full = $setting;
      }
    }

    //TODO: Refine results by using $section
    //TODO: If entry cannot be found, assume it's a category and redo the checks.
    //TODO: Make is so if the page doesn't exist, return nothing

    // Create a unique name for caching
    if(empty($id) && empty($section)) {
      $cache_name = 'current';
    } else {
      $cache_name = (string)$id.$section;
    }

    // echo func_get_arg(0);
    // echo is_numeric($id) ? '1' : '2';
      // if ( empty($id)) {
        // $id = craft()->urlManager->getMatchedElement()->id;
      // }

    // If the cache name doesn't exist in the cache,
    // Then create and store the results.
    if (!array_key_exists($cache_name, $this->cacheEntry)) {

      // echo "<br>This entry was queried just once: ".$cache_name."<br>";

      if ( is_null($id) && $cache_name == 'current' && isset(craft()->urlManager->getMatchedElement()->id)) {
        $id = (int)craft()->urlManager->getMatchedElement()->id;
        // echo "new id set:".$id;
      }

      if (!empty($id)) {

        // TODO: Little check to avoid errors on pages that don't exist in craft
        // TODO: Determine a way to get the entry type of the given entry settings. Important for Category and Tag pages
        $type = 'Entry';

        if ( isset($type) && $type == "Category") {
          $criteria = craft()->elements->getCriteria(ElementType::Category);
        } else {
          $criteria = craft()->elements->getCriteria(ElementType::Entry);
        }

        if (is_string($id)) {
          $criteria->slug = $id;
        } else {
          $criteria->id = (is_numeric($id) && intval($id) > 0) ? (int)$id : craft()->urlManager->getMatchedElement()->id;
        }

        if ( !empty($section) ) {
          if ( $section == "single" || $section == "channel" || $section == "structure") {
            $criteria->section = $section;
          }
        }

        $criteria->status = null;
        $criteria->limit = 1;

        $entry = $criteria->first();

        if ($full == true) {
          $this->cacheEntry[$cache_name] = $entry;
        } else {
          if (isset($entry)) {

            $results = [
              'id' => $entry->id,
              'title' => $entry->title,
              'slug' => $entry->slug,
              'url' => $entry->url,
              'uri' => $entry->uri,
              'status' => $entry->status,
              'snippet' => isset($entry->snippet) ? $entry->snippet : false,
              'level' => $entry->level,
              'parent' => ($entry->getParent() ? 'parent' : false),
              'child' => ($entry->hasDescendants() ? 'child' : false),
            ];

            // Entry specific data
            if ( $type == "Entry") {
              $section = $entry->section;
              // echo "<pre>"; var_dump($section); echo "</pre>";  die();
              $results['type'] = $entry->getType()->name;
              $results['section'] = $this->section($section->handle);
            }

            // Category specific data
            if ( $type == "Category") {
              $group = $entry->group;
              $results['type'] = 'category';
              $results['group'] = [
                'name' => $group->name,
                'id' => $group->id,
                'handle' => $group->handle,
              ];
            }

            $this->cacheEntry[$cache_name] = $results;
          } else {
            // If the entry is not found, use the slug or id from the first param
            // to try and find a section instead.
            $section = $this->section($id);
            if (!is_null($section)) {
              return $section;
            } else {
              // If the entry or category doesn't exist
              return "Doesn't exist";
            }
          }
        }
      } else {

        // When a page doesn't technically exist, create faux results.
        $segments = craft()->request->getSegments();
        $segment = end($segments);
        $title = str_replace(['_', '-'], ' ', $segment);

        $results = [
          'id' => null,
          'title' => preg_replace('/\s+/', ' ', ucwords($title)),
          'slug' => preg_replace('/\s+/', '-', $segment),
          'url' => '/'.craft()->request->getPath().'/',
          'uri' => craft()->request->getPath(),
          'snippet' => null,
          'status' => 'live',
          'level' => count($segments),
          'parent' => false,
          'child' => false,
        ];

        // Error page checks
        foreach ($this->errors as $error => $value) {
          if ($this->status == $error) {
            $results['error'] = $error;
            $results['title'] = $results['title'].' | '.(craft()->config->get('devMode') ? $error.' - ' : '').$value;
          }
        }

        $this->cacheEntry[$cache_name] = $results;
      }
    }
    return $this->cacheEntry[$cache_name];
  }

  // Grab a certain segment of a given URL string
  // quick.urlSegment(2)                                               - Returns 2nd segment of current page url
  // quick.urlSegment("http://www.website.com/segment/seg/s", 2)       - Returns 2nd url segment : 'seg'
  // quick.urlSegment("http://www.website.com/segment/seg/s", 0)       - Returns the website url without any segments : 'http://www.website.com'
  // quick.urlSegment("http://www.website.com/segment/seg/s", 'last')  - Returns the last segment : 's'
  // quick.urlSegment("http://www.website.com/segment/seg/s", 'first') - Returns the first segment : 'segment'
  // quick.urlSegment("http://www.website.com/segment/seg/s", -3)      - Returns the third segment from the end : 'segment'
  public function urlSegment() {

    // Fail if not parameters are passed
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
      return $segments[1];
    }

    return false;

  }


  // Add or update parameters within a [url] string.

  // URL and just one string, results in adding the value to the end of the url and not editing any existing parameters
  // params('http://www.website.com?foo=bar', 'test');
  // http://www.website.com?foo=bar&test

  // URL and an array will ignore any other strings passed. Any variables that already exist in the url will be overwritten. Everything else will be added to the end.
  // params('http://www.website.com?foo=bar', ['foo'=>'jazz', 'ping'=>'pong', 'test']);
  // http://www.website.com?foo=jazz&ping=pong&test

  // URL and two additional string variables will be added as a variable and value respectively.
  // params('http://www.website.com', 'foo', 'bar')
  // http://www.website.com?foo=bar

  // Just two strings variables will be added as a variable and value respectively. Ommitting a url will fallback to the current to the current page url
  // params('foo', 'bar')
  // http://www.website.com?foo=bar

  // Just one string will be added as a value. Ommitting a url will fallback to the current to the current page url
  // params('test')
  // http://www.website.com?test
  function params(){

    // Fail if not parameters are passed
    if ( func_num_args() < 1 ){
      return false;
    }

    $arguments = func_get_args();

    $argumentsCount = func_num_args();

    $url       = null;
    $variable  = null;
    $value     = null;
    $newParams = null;

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
