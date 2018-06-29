<?php
require_once __DIR__ . '/vendor/autoload.php';

use Sunnysideup\UpgradeToSilverstripe4\ModuleUpgrader;

$obj = ModuleUpgrader::create()
    //->setRunImmediately(true)
    ->setLogFolderDirLocation('/var/www')
    ->setAboveWebRootDirLocation('/var/www')
    ->setWebRootName('__upgradeto4__')
    ->setArrayOfModules(
        [
            1 => [
              'VendorName' => 'sunnysideup',
              'VendorNamespace' => 'Sunnysideup',
              'PackageName' => 'metatags',
              'PackageNamespace' => 'Metatags',
              'GitLink' => 'git@github.com:sunnysideup/silverstripe-metatags.git',
              'UpgradeAsFork' => false
            ]
        ]
    )
    ->setNameOfTempBranch('4.1-TEMP-upgrade')
    //->setComposerEnvironmentVars('COMPOSER_HOME="/home/UserName"')
    ->setLocationOfUpgradeModule(__DIR__ .'/vendor/silverstripe/upgrader/bin/upgrade-code')
    ->setStartFrom('InspectAPIChanges-2')
    ->setEndWith('InspectAPIChanges-2')
    ->run();
