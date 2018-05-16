<?php

require_once('../silverstripe-upgrade_to_silverstripe_4/src/MetaUpgrader.php');
$obj = MetaUpgrader::create()
    ->setRootDir('/var/www')
    ->setUpgradeDirName('__upgradeto4__')
    ->setVendorName('sunnysideup')
    ->setArrayOfModules(
        [
            'share_this_simple'
        ]
    )
    ->setNameOfTempBranch('4.1-temp-upgrade')
    // ->setRunImmediately(true)
    // ->setComposerEnvironmentVars('COMPOSER_HOME="/home/thiemen"')
    ->setLocationOfUpgradeModule('~/.composer/vendor/bin/upgrade-code')
    ->setIncludeEnvironmentFileUpdate(false)
    ->run();
