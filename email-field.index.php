<?php
require_once __DIR__ . '/vendor/autoload.php';

use Sunnysideup\UpgradeToSilverstripe4\ModuleUpgrader;

$obj = ModuleUpgrader::create()
    ->setRecipe('SS4')

    ->setRunImmediately(true)
    ->setRunInteractively(true)
    ->setRunIrreversibly(false)

    ->setLogFolderDirLocation('/var/www/ss3/upgrades-logs')
    ->setAboveWebRootDirLocation('/var/www/ss3/upgrades')
    ->setNameOfTempBranch('4.1-TEMP-upgrade')

    ->setArrayOfModules(
        [
            1 => [
                'VendorName' => 'sunnysideup',  //e.g. as listed in composer.json file, not so relevant for projects
                'VendorNamespace' => 'Sunnysideup', //namespace to be used for project
                'PackageName' => 'email_address_database_field', //e.g. as listed in composer.json file, not so relevant for projects
                'PackageNamespace' => 'EmailAddressDatabaseField', //second part of the namespace i.e. VendorNamespace/PackageNamespace
                'IsModuleUpgrade' => true, //is it a module or a website project?
            ],
        ]
    )
    ->setFrameworkComposerRestraint('^4.4')
    //->setComposerEnvironmentVars('COMPOSER_HOME="/home/UserName"')
    // ->setLocationOfSSUpgradeModule(__DIR__ .'/vendor/silverstripe/upgrader/bin/upgrade-code')
    // ->setListOfTasks(
    //     [
    //         'ResetWebRootDir-1' => [],
    //         'AddLegacyBranch' => [],
    //         'ResetWebRootDir-2' => [],
    //         'AddUpgradeBranch' => [],
    //         'UpdateComposerRequirements-1' => [
    //             'Package' => 'silverstripe/framework',
    //             'NewVersion' => '~4.0'
    //         ],
    //         'RecomposeHomeBrew' => [],
    //         'UpdateComposerRequirements-2' => [
    //             'Package' => 'silverstripe/cms',
    //             'ReplacementPackage' => 'silverstripe/recipe-cms',
    //             'NewVersion' => '1.1.2'
    //         ],
    //
    //         'ResetWebRootDir-3' => [],
    //         'ComposerInstallProject' => [],
    //         'SearchAndReplace' => [],
    //         // 'ChangeEnvironment' => [],
    //         'UpperCaseFolderNamesForPSR4' => [],
    //         'AddNamespace' => [],
    //         'Upgrade' => [],
    //         'InspectAPIChanges' => [],
    //         'Reorganise' => [],
    //         // 'WebRootUpdate' => []
    //     ]
    // )
    // ->setVariableForTask($taskName, $variableName, $variableValue);
    // ->setStartFrom('RecomposeHomeBrew')
    // ->setEndWith('SearchAndReplace')
    // ->setOnlyRun('SearchAndReplace')
    // ->removeFromListOfTasks('SearchAndReplace')
    // ->addToListOfTasks(
    //      ['MyCustomTasks\\CustomNameSpace\\SearchAndReplace2'],
    //      $beforeOrAfter = 'SearchAndReplace',
    //      $isBefore = false
    // )
    // ->defaultNamespaceForTask('MyCustomisation\\CustomNameSpace\\MyTasks')
    // ->setVerbose(false)
    ->run();
