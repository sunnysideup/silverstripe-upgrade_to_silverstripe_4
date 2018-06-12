<?php
require_once __DIR__ . '/vendor/autoload.php';

use Sunnysideup\UpgradeToSilverstripe4\MetaUpgrader;

$obj = MetaUpgrader::create()
    //->setRunImmediately(true)
    ->setLogFolderLocation('/var/www')
    ->setAboveWebRootDir('/var/www')
    ->setWebRootDirName('__upgradeto4__')
    ->setArrayOfModules(
        [
            1 => [
              'VendorName' => 'sunnysideup',
              'VendorNameSpace' => 'Sunnysideup',
              'PackageName' => 'webpack_requirements_backend',
              'PackageNameSpace' => 'WebpackRequirementsBackend',
              'GitLink' => 'git@github.com:sunnysideup/silverstripe-webpack_requirements_backend.git',
              'UpgradeAsFork' => false
            ]
        ]
    )
    ->setNameOfTempBranch('4.1-TEMP-upgrade')
    //->setComposerEnvironmentVars('COMPOSER_HOME="/home/UserName"')
    ->setLocationOfUpgradeModule(__DIR__ .'/vendor/silverstripe/upgrader/bin/upgrade-code')
    ->setStartFrom('runUpgrade')
    ->setEndWith('')
    ->run();
