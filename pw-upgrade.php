<?php
require_once __DIR__ . '/vendor/autoload.php';

use Sunnysideup\UpgradeToSilverstripe4\ModuleUpgrader;

$obj = ModuleUpgrader::create()
    ->setRecipe('SS4')

    ->setAboveWebRootDirLocation('/var/www/upgrades')
    ->setLogFolderDirLocation('/var/www/upgrades/upgrades-logs')

    ->setWebRootName('upgradeto4')

    ->setArrayOfModules(
        [
            1 => [
                //see run.me.index.full.php.EXAMPLE for more details1
                'VendorName' => 'photowarehouse',
                'VendorNamespace' => 'PhotoWarehouse',
                'PackageName' => 'app',
                'PackageNamespace' => 'App',
                'PackageFolderNameForInstall' => 'photowarehouse',
                'GitLink' => 'git@bitbucket.org:sunnysideupnz/photowarehouse_live.git',
                'IsModuleUpgrade' => false,
            ]
        ]
    )->setModuleDirLocations(
        [
            'app',
            'migration'
        ]
    )

    ->setFrameworkComposerRestraint('^4.5')

    ->run();
