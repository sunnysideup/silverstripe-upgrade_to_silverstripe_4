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
              'PackageName' => 'dataobject-generator',
              'PackageNamespace' => 'DataObjectGenerator',
              'GitLink' => 'git@github.com:sunnysideup/silverstripe-dataobject-generator.git',
              'UpgradeAsFork' => false
            ]
        ]
    )
    ->setNameOfTempBranch('4.1-TEMP-upgrade')
    //->setComposerEnvironmentVars('COMPOSER_HOME="/home/UserName"')
    ->setLocationOfUpgradeModule(__DIR__ .'/vendor/silverstripe/upgrader/bin/upgrade-code')
    ->setListOfTasks(
        [
            'ResetWebRootDir-1' => [],
            'AddLegacyBranch' => [],
            'ResetWebRootDir-2' => [],
            'AddUpgradeBranch' => [],
            'UpdateComposerRequirements-1' => [
                'Package' => 'silverstripe/framework',
                'NewVersion' => '~4.0'
            ],
            'Recompose' => [],
            'UpdateComposerRequirements-2' => [
                'Package' => 'silverstripe/cms',
                'ReplacementPackage' => 'silverstripe/recipe-cms',
                'NewVersion' => '1.1.2'
            ],

            'ResetWebRootDir-3' => [],
            'ComposerInstallProject' => [],
            'SearchAndReplace' => [],
            // 'ChangeEnvironment' => [],
            'UpperCaseFolderNamesForPSR4' => [],
            'AddNamespace' => [],
            'Upgrade' => [],
            'InspectAPIChanges' => [],
            'Reorganise' => [],
            // 'WebRootUpdate' => []
        ]
    )
    ->setStartFrom('')
    ->setEndWith('')
    ->run();
