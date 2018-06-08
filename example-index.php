<?php
require_once('./src/MetaUpgrader.php');
$obj = MetaUpgrader::create()
    //->setRunImmediately(true)
    ->setAboveWebRootDir('/var/www')
    ->setWebrootDirName('__upgradeto4__')
    ->setArrayOfModules(
        [
            1 => [
              'VendorName' => 'sunnysideup',
              'VendorNameSpace' => 'Sunnysideup',
              'PackageName' => 'webpack_requirements_backend',
              'PackageNameSpace' => 'WebpackRequirementsBackend',
              'GitLink' => 'git@github.com:sunnysideup/silverstripe-webpack_requirements_backend.git',
              'UpgradeAsFork' => false
            ]
        ]
    )
    ->setNameOfTempBranch('4.1-TEMP-upgrade')
    //->setComposerEnvironmentVars('COMPOSER_HOME="/home/UserName"')
    ->setLocationOfUpgradeModule('/var/www/silverstripe-upgrade_to_silverstripe_4/vendor/silverstripe/upgrader/bin/upgrade-code')
    ->setIncludeEnvironmentFileUpdate(false)
    ->setIncludeReorganiseTask(true)
    ->setIncludeWebrootUpdateTask(false)
    ->setStartFrom('')
    ->setEndWith('')
    ->run();