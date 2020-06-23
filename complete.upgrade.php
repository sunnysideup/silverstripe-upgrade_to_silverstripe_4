<?php
require_once __DIR__ . '/vendor/autoload.php';

use Sunnysideup\UpgradeToSilverstripe4\ModuleUpgrader;

// make sure the dirs noted below exist:
// - /var/www/upgrades,
// - /var/www/logdir
// we have used sunnysideup/cleaner_tinymce_config as an example repository below

$obj = ModuleUpgrader::create()
    ->setArrayOfModules(
        [
            1 => [
                'VendorName' => 'sunnysideup',
                'VendorNamespace' => 'Sunnysideup',
                'PackageName' => 'mysites',
                'PackageNamespace' => 'SilverstripeUpgrade',
                'PackageFolderNameForInstall' => 'upgradenow',
                'GitLink' => 'git@bitbucket.org:sunnysideupnz/upgradesilverstripe.com.git',
                'IsModuleUpgrade' => false,
                // 'OriginComposerFileLocation' => 'https://bitbucket.org/sunnysideupnz/upgradesilverstripe.com/raw/a88492066701dfa7fbb8685c4b923587de8ff0c2/composer.json'
            ],
        ]
    )
    ->setRunInteractively(false)
    ->setRunIrreversibly(true)
    ->setAboveWebRootDirLocation('/var/www/upgrades')
    ->setLogFolderDirLocation('/var/www/logdir')
    ->setOnlyRun('FinaliseUpgradeWithMergeIntoMaster')
    ->run();
