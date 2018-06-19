<?php
require_once __DIR__ . '/vendor/autoload.php';

use Sunnysideup\UpgradeToSilverstripe4\MetaUpgrader;

$obj = MetaUpgrader::create()
    //->setRunImmediately(true)
    ->setLogFolderDirLocation('/var/www')
    ->setAboveWebRootDirLocation('/var/www')
    ->setWebRootName('__upgradeto4__')
    ->setArrayOfModules(
        [
            1 => [
              'VendorName' => 'sunnysideup',
              'VendorNamespace' => 'Sunnysideup',
              'PackageName' => 'webpack_requirements_backend',
              'PackageNamespace' => 'WebpackRequirementsBackend',
              'GitLink' => 'git@github.com:sunnysideup/silverstripe-webpack_requirements_backend.git',
              'UpgradeAsFork' => false
            ]
        ]
    )
    ->setNameOfTempBranch('4.1-TEMP-upgrade')
    //->setComposerEnvironmentVars('COMPOSER_HOME="/home/UserName"')
    ->setLocationOfUpgradeModule(__DIR__ .'/vendor/silverstripe/upgrader/bin/upgrade-code')
    ->setStartFrom('')
    ->setEndWith('ResetWebRootDir-2')
    ->run();
