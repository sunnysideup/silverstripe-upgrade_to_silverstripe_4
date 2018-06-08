<?php

require_once('../silverstripe-upgrade_to_silverstripe_4/src/MetaUpgrader.php');
$obj = MetaUpgrader::create()
    //->setRunImmediately(true)
    ->setAboveWebRootDir('/var/www')
    ->setWebrootDirName('__upgradeto4__')
    ->setVendorName('sunnysideup')
    ->setArrayOfModules(
      [
          'webpack_requirements_backend'
      ]
    )
    ->setNameOfTempBranch('4.1-TEMP-upgrade')
    //->setComposerEnvironmentVars('COMPOSER_HOME="/home/UserName"')
    ->setLocationOfUpgradeModule('~/.composer/vendor/bin/upgrade-code')
    ->setIncludeEnvironmentFileUpdate(false)
    ->setIncludeReorganiseTask(true)
    ->setIncludeWebrootUpdateTask(true)
    ->run();
