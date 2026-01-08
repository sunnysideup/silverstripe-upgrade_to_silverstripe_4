<?php
require_once __DIR__ . '/vendor/autoload.php';

use Sunnysideup\UpgradeToSilverstripe4\ModuleUpgrader;

$obj = ModuleUpgrader::create()
    ->setRecipe('SS4') // see: ModuleUpgraderBaseWithVariables::availableRecipes

    ->setAboveWebRootDirLocation('/var/www/upgrades')
    ->setLogFolderDirLocation('/var/www/upgrades-logs')

    ->setWebRootName('upgradeto4')

    ->setArrayOfModules(
        [
            1 => [
                //see run.me.index.full.php.EXAMPLE for more details1
                'VendorName' => 'sunnysideup',
                'VendorNamespace' => 'Sunnysideup',
                'PackageName' => 'ecommerce_delivery_custom',
                'PackageNamespace' => 'EcommerceDeliveryCustom',
                'PackageFolderNameForInstall' => 'ecommerce_delivery_custom',
                'GitLink' => 'git@github.com:sunnysideup/silverstripe-ecommerce_delivery_custom.git',
                'IsModuleUpgrade' => true,
                'IsOnPackagist' => true,
            ]
        ]
    )

    ->setVariableForTask(
        'ComposerInstallProject',
        'alsoRequire',
        [
            'sunnysideup/ecommerce' => 'dev-master'
        ]
    )

    ->setNameOfTempBranch('4.1-TEMP-upgrade')

    ->setFrameworkComposerRestraint('^4.5')

    ->run();
