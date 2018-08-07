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
              'PackageName' => 'cleaner_tinymce_config',
              'PackageNamespace' => 'CleanerTinyMCEConfig',
              'GitLink' => 'git@github.com:sunnysideup/silverstripe-cleaner_tinymce_config.git'
            ]
        ]
    )
    ->setAboveWebRootDirLocation('/var/www/upgrades')
    ->setLogFolderDirLocation('/var/www/logdir')
    ->run();
