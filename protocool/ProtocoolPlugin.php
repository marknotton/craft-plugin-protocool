<?php
namespace Craft;

class ProtocoolPlugin extends BasePlugin {
  public function getName() {
    return Craft::t('Protocool');
  }

  public function getVersion() {
    return '0.1';
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
    Craft::import('plugins.quick.twigextensions.link');
    Craft::import('plugins.quick.twigextensions.checkfields');
    Craft::import('plugins.quick.twigextensions.fileexists');
    Craft::import('plugins.quick.twigextensions.version');
    Craft::import('plugins.quick.twigextensions.slugify');

    return array(
      new link(),
      new checkfields(),
      new fileexists(),
      new version(),
      new slugify()
    );
  }

  public function init() {
    if (!craft()->isConsole() && !craft()->request->isCpRequest())  {
      craft()->urlManager->setRouteVariables(
        array(
          'segments' => craft()->protocool,
        )
      );
    }
  }
}
