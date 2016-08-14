<?php
namespace Craft;

class ProtocoolPlugin extends BasePlugin {
  public function getName() {
    return Craft::t('Protocool');
  }

  public function getVersion() {
    return '0.1';
  }

  public function getSchemaVersion() {
    return '0.1';
  }

  public function getDescription() {
    return 'A small collection of filters and functions that can query and modify a url string.';
  }

  public function getDeveloper() {
    return 'Yello Studio';
  }

  public function getDeveloperUrl() {
    return 'http://yellostudio.co.uk';
  }

  public function getDocumentationUrl() {
    return 'https://github.com/marknotton/craft-plugin-protocool';
  }

  public function getReleaseFeedUrl() {
    return 'https://raw.githubusercontent.com/marknotton/craft-plugin-protocool/master/protocool/releases.json';
  }

  public function addTwigExtension() {
    Craft::import('plugins.protocool.twigextensions.segment');
    Craft::import('plugins.protocool.twigextensions.params');
    return array(
      new segment(),
      new params()
    );

  }

}
