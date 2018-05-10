This module aims to help developers upgrade SS3 modules to SS4 without doing any manual rewriting. You can provide a list of modules and run the code below.  This will create a branch in your module that is SS4 ready. After inspection you can then merge this into `dev/master` as you see fit.


# prerequisites before you start

 - create 3.6 branch so that you can keep adding PATCHes to your SS3 version.
 - create a tag (OPTIONAL)
 - module needs to be listed on packagist
 - composer file needs to follow this pattern (installer-name requirement may be dropped in the future)

```json
{
    "name": "sunnysideup/google-calendar-interface",
    "type": "silverstripe-module",
    ...
    ...
    ...
    "extra": {
        "installer-name": "google-calendar-interface"
    },
}
```

# usage:
1. Run in your web root (also see below):
`git clone git@github.com:sunnysideup/silverstripe-upgrade_to_silverstripe_4.git`

2. Create a new php file (e.g. `index.php`) in your root dir (or anywhere else you can run it):

```php
<?php
require_once('silverstripe-upgrade_to_silverstripe_4/src/MetaUpgrader.php');
$obj = MetaUpgrader::create()
    ->setRootDir('/var/www')
    ->setUpgradeDirName('upgradeto4')
    ->setVendorName('sunnysideup')
    ->setArrayOfModules(
      [
          'share_this_simple'
      ]
    )
    ->setNameOfTempBranch('4.1-TEMP-upgrade')
    ->setRunImmediately(false)
    ->setComposerEnvironmentVars('COMPOSER_HOME="/home/UserName"')
    ->setLocationOfUpgradeModule('~/.composer/vendor/bin/upgrade-code')
    ->run();
```
run the file to upgrade your modules.

3. inspect your new branch that is SS4 ready and merge it into `dev-master` as you see fit.


# options:

### temp branch

`->setNameOfTempBranch('4.1-TEMP-upgrade')` : a temporary branch will be added to your module.  
All upgrade changes will be committed to this branch. **Careful!  This branch will be deleted every time you run the update process so that you can run the update process many times.**

### vendor name

`->setVendorName('SunnySideUp')` : you can only upgrade modules for one vendor at the time.

### root directory

`->setRootDir('/var/www/')`  This is meant to be the directory where you do the work, where you usually save your websites that you work on locally.

### upgrade directory

`->setUpgradeDirName('upgradeto4')`. This is the name of the directory that is created in the root dir where the upgrade takes place. **Careful! Only use this directory for automated work as it will be deleted when you run the upgrade again.**

### list of modules

`->setArrayOfModules([])`. This is the name as listed on packagist e.g. `sunnysideup/metatags` should be listed as `metatags`.

### run immediately or create bash script?

`->setRunImmediately(false)`. By default, the script will not do any actual upgrading, but rather, it will output a bash script that does the actual upgrading.
When you set `runImmediately` to true, the PHP code will use the `exec` function to output commands on the `command line` immediately. **Careful! Even if you do not run the code immediately, a bunch of code will still be executed on the command line to inspect the module.**
