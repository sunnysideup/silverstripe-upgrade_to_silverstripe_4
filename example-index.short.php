<?php
require_once __DIR__ . '/vendor/autoload.php';

use Sunnysideup\UpgradeToSilverstripe4\ModuleUpgrader;

$obj = ModuleUpgrader::create()
    ->setArrayOfModules(
        [
            1 => [
              'VendorName' => 'sunnysideup',
              'PackageName' => 'dataobject-generator',
              'PackageNamespace' => 'DataObjectGenerator'
            ]
        ]
    )
    ->run();
