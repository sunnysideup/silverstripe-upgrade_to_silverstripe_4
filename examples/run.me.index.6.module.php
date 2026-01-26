<?php

declare(strict_types=1);

$cwd = getcwd();
if ($cwd === false) {
    throw new RuntimeException('Cannot determine current working directory.');
}

require_once $cwd . '/vendor/autoload.php';

use Sunnysideup\UpgradeSilverstripe\ModuleUpgrader;

$obj = ModuleUpgrader::create()
    ->setRecipe('SS6-MODULES') // see: ModuleUpgraderBaseWithVariables::availableRecipes

    ->setAboveWebRootDirLocation('/var/www/upgrades')
    ->setLogFolderDirLocation('/var/www/upgrades-logs')

    ->setWebRootName('upgradeto6')

    ->setArrayOfModules(
        [
            1 => [
                //see run.me.index.full.php.EXAMPLE for more details1
                'VendorName' => 'sunnysideup',
                'VendorNamespace' => 'Sunnysideup',
                'PackageName' => 'glossary',
                'PackageNamespace' => 'Glossary',
                'PackageFolderNameForInstall' => 'glossary',
                'GitLink' => 'git@github.com:sunnysideup/silverstripe-glossary.git',
                'IsModuleUpgrade' => true,
                'IsOnPackagist' => true,
            ]
        ]
    )

    ->setNameOfTempBranch('SS6-Automated-Upgrade-Branch')

    ->setFrameworkComposerRestraint('^6.0')

    ->run();
