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
    ->setAboveWebRootDirLocation('/var/www/upgrades')
    ->setWebRootName('ecommerce_product_variation')
    ->setRunInteractively(true)
    // ->setRunIrreversibly(true)
    ->setArrayOfModules(
        [
            1 => [
              'VendorName' => 'sunnysideup',
              'VendorNamespace' => 'Sunnysideup',
              'PackageName' => 'ecommerce_product_variation',
              'PackageNamespace' => 'EcommerceProductVariation',
              'IsModuleUpgrade' => true,
            ]
        ]
    )
    ->setVariableForTask(
        'ComposerInstallProject',
        'alsoRequire',
        [
            'sunnysideup/ecommerce' => 'master',
            // 'sunnysideup/ecommerce_product_variation' => 'master',
        ]
    )
    ->setVariableForTask(
        'SearchAndReplace',
        'sourceFolders',
        [
            'Ecommerce',
            'SS4',
        ]
    )
    ->setRunInteractively(false)
    ->run();