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
    ->setRecipe('SS4-LINT')
    ->setAboveWebRootDirLocation('/var/www/upgrades')
    ->setWebRootName('ecommerce_tax')
    ->setRunInteractively(true)
    // ->setRunIrreversibly(true)
    ->setArrayOfModules(
        [
            1 => [
              'VendorName' => 'sunnysideup',
              'VendorNamespace' => 'Sunnysideup',
              'PackageName' => 'ecommerce_tax',
              'PackageNamespace' => 'EcommerceTax',
              'IsModuleUpgrade' => true,
            ]
        ]
    )
    // ->setVariableForTask(
    //     'ComposerInstallProject',
    //     'installModuleAsVendorModule',
    //     true
    // )
    ->run();
