This module aims to help developers upgrade SS3 modules to SS4 without doing any manual rewriting. You can provide a list of modules and


# prerequisites before you start

 - create 3.6 branch so that you can keep adding PATCHes to your SS3 version.
 - create a tag (OPTIONAL)
 - module needs to be listed on packagist
 - composer file needs to follow this pattern (MAY CHANGE IN THE FUTURE):

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
git clone git@github.com:sunnysideup/silverstripe-upgrade_to_silverstripe_4.git in your web root (also see below):

create a new php file in your root dir (or anywhere else you can run it):

```php
<?php
require_once('silverstripe-upgrade_to_silverstripe_4/src/MetaUpgrader.php');
$obj = MetaUpgrader::create()
    ->setNameOfTempBranch('4.1-TEMP-upgrade')
    ->setVendorName('SunnySideUp')
    ->setRootDir('/var/www/')
    ->setUpgradeDirName('upgradeto4')
    ->setArrayOfModules(
      [
        'my_first_module',
        'my_second_module'
      ]
    );
    ->setRunImmediately(false)
    ->run();
```
run the file to upgrade your modules.
  
  
# options:

### temp branch

`->setNameOfTempBranch('4.1-TEMP-upgrade')` : a temporary branch will be added to your module.  
All upgrade changes will be committed to this branch.

### vendor name

`->setVendorName('SunnySideUp')` : you can only upgrade modules for one vendor at the time.

### root directory

`->setRootDir('/var/www/')`  This is meant to be the directory where you do the work, where you usually save your websites that you work on locally.

### upgrade directory

`->setUpgradeDirName('upgradeto4')`. This is the name of the directory that is created in the root dir where the upgrade takes place.

### list of modules

`->setArrayOfModules([])`. This is the name as listed on packagist e.g. `sunnysideup/metatags` should be listed as `metatags`.

### run immediately or create bash script?

`->setRunImmediately(false)`. By default, the script will not do any actual upgrading, but rather, it will output a bash script that does the actual upgrading. 
When you set `runImmediately` to true, the PHP code will use the `exec` function to output commands on the `command line` immediately.
NB. even if you do not run the code immediately, a bunch of code will still be executed on the command line to inspect the module.









