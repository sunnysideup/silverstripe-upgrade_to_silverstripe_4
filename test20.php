<?php
require_once __DIR__ . '/vendor/autoload.php';

use Sunnysideup\UpgradeToSilverstripe4\ModuleUpgrader;


/**
 * below are examples
 * of most of the config options
 *
 * N.B. the individual tasks also have config options!
 */
$obj = ModuleUpgrader::create()
    ->setRecipe('SS4')
    ->setAboveWebRootDirLocation('/ss3/upgrades')
    ->setWebRootName('vimeoembed')
    ->setRunInteractively(true)
    // ->setRunIrreversibly(true)
    ->setArrayOfModules(
        [
            1 => [
                'VendorName' => 'sunnysideup',
                'VendorNamespace' => 'Sunnysideup',
                'PackageName' => 'vimeoembed',
                'PackageNamespace' => 'Vimeoembed',
                'IsModuleUpgrade' => true,
            ]
        ]
    )
    ->setNameOfUpgradeStarterBranch('upgrades/starting-point')
    ->setRunInteractively(false)
    ->run();
