<?php
namespace Craft;

class YouarelPlugin extends BasePlugin {
  public function getName() {
    return Craft::t('Youarel');
  }

  public function getVersion() {
    return '0.1';
  }

  public function getSchemaVersion() {
    return '0.1';
  }

  public function getDescription() {
    return 'Adds a small collection of filters and functions that can query and modify a url string.';
  }

  public function getDeveloper() {
    return 'Yello Studio';
  }

  public function getDeveloperUrl() {
    return 'http://yellostudio.co.uk';
  }

  public function getDocumentationUrl() {
    return 'https://github.com/marknotton/craft-plugin-youarel';
  }

  public function getReleaseFeedUrl() {
    return 'https://raw.githubusercontent.com/marknotton/craft-plugin-youarel/master/youarel/releases.json';
  }

  public function addTwigExtension() {
    Craft::import('plugins.youarel.twigextensions.segment');
    Craft::import('plugins.youarel.twigextensions.params');
    Craft::import('plugins.youarel.twigextensions.slugify');
    Craft::import('plugins.youarel.twigextensions.removeport');
    Craft::import('plugins.youarel.twigextensions.baseurl');
    return array(
      new segment(),
      new params(),
      new slugify(),
      new removeport(),
      new baseurl()
    );
  }

}
