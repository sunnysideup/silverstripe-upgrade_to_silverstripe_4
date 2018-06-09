This module aims to help developers upgrade SS3 modules to SS4 without doing any manual rewriting. You can provide a list of modules and run the code below.  This will create a branch in your module that is SS4 ready. After inspection you can then merge this into `dev/master` as you see fit.


# prerequisites before you start

 - module to be upgraded needs to be listed on packagist
 - composer file needs to follow this pattern (installer-name requirement may be dropped in the future)


```json
{
    "name": "sunnysideup/my-module-name-foo-bar",
    "type": "silverstripe-module",
    ...
    ...
    ...
    "extra": {
        "installer-name": "my-module-name-foo-bar"
    },
}
```

# additional things to consider before you start
- create 3 branch of the module you are upgrading so that you can keep adding patches to your SS3 version
- create a tag of the module you are upgrading

# what does this module do?

In short: it helps you upgrade SS modules from SS3 to SS4.

In more detail, this module, for a provided list of modules from a vendor, (re)creates a branch specifically created for upgrading your module from SS3 to SS4. In this branch it:

 - updates the composer requirements: https://github.com/silverstripe/silverstripe-upgrader/blob/master/docs/en/recompose.md
 - Updates the environment file (environment): https://github.com/silverstripe/silverstripe-upgrader/blob/master/docs/en/environment.md (OPTIONAL)
 - Adds namespaces (add-namespace): https://github.com/silverstripe/silverstripe-upgrader/blob/master/docs/en/add-namespace.md
 - Refactors your existing code base (upgrade): https://github.com/silverstripe/silverstripe-upgrader/blob/master/docs/en/upgrade.md
 - Applies API changes (inspect): https://github.com/silverstripe/silverstripe-upgrader/blob/master/docs/en/inspect.md
 - Renames mysite (reorganise): https://github.com/silverstripe/silverstripe-upgrader/blob/master/docs/en/reorganise.md (OPTIONAL)
 - Switches your project to have a public web-root: https://github.com/silverstripe/silverstripe-upgrader/blob/master/docs/en/webroot.md (OPTIONAL)

It then commits and pushes the results for inspection.

# installation and usage:

1.  Install this module in your web-root (or another place if needed - we use `/var/www/silverstripe-upgrade_to_silverstripe_4/` in example below):
    `composer install sunnysideup/upgrade_to_silverstripe_4 /var/www/silverstripe-upgrade_to_silverstripe_4/`

2.  Create a new php file (e.g. `index.php`) in your root dir (or anywhere else  you can run it):

```php
<?php
require_once('silverstripe-upgrade_to_silverstripe_4/src/MetaUpgrader.php');
$obj = MetaUpgrader::create()
    ->setRunImmediately(true)
    ->setAboveWebRootDir('/var/www')
    ->setWebrootDirName('__upgradeto4__')
    ->setArrayOfModules(
        [
            [
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
    ->setComposerEnvironmentVars('COMPOSER_HOME="/home/UserName"')
    ->setLocationOfUpgradeModule('~/.composer/vendor/bin/upgrade-code')
    ->setIncludeEnvironmentFileUpdate(false)
    ->setIncludeReorganiseTask(false)
    ->setIncludeWebrootUpdateTask(false)
    ->setStartFrom('runRecompose')
    ->setEndWith('runComposerInstallProject');

$obj->run();
```

The code above is very verbose to show you all the options available. Here is a skeleton version:

```php
<?php
require_once('silverstripe-upgrade_to_silverstripe_4/src/MetaUpgrader.php');
$obj = MetaUpgrader::create()
    ->setAboveWebRootDir('/var/www')
    ->addModule(
        [
          'VendorName' => 'sunnysideup',
          'PackageName' => 'webpack_requirements_backend'
        ]
    );

$obj->run();
```
We have included an example file like this in the module root. 


3. Run the file to upgrade your modules - e.g.

```sh
    $ php index.php
```


4. Apply any final fixes to this branch to make it SS4 ready.


5. Merge the upgrade branch into `dev-master` as you see fit.


# config options:

### run immediately or create bash script?

`->setRunImmediately(false)`: When you set `runImmediately` to true, the PHP code will use the `exec` function to run commands on the `command line` immediately. **Careful! Even if you do not run the code immediately, a bunch of code will still be executed on the command line to inspect the module.**

By default running on command line, it will run immediately and when accessing it through http the script will not do any actual upgrading, but rather, it will output a bash script to your screen that you can use in the future. 

It is recommended that you use the runImmediate = true option as that is how we test it most of the time. 


### root directory

`->setRootDir('/var/www/')`: this is meant to be the directory where you do the work, where you usually save your websites that you work on locally.


### upgrade directory

`->setUpgradeDirName('upgradeto4')`: this is the name of the directory that is created in the root dir where the upgrade takes place. **Careful! Only use this directory for automated work as it will be deleted when you run the upgrade again.**



### list of modules

`->setArrayOfModules([])`: See index.php for example of format.


### temp branch

`->setNameOfTempBranch('4.1-TEMP-upgrade')`: a temporary branch will be added to your module.  
All upgrade changes will be committed to this branch. **Careful!  This branch will be deleted every time you run the update process so that you can run the update process many times.**


### composer environment vars

`->setComposerEnvironmentVars('COMPOSER_HOME="/home/UserName"')`: specific stuff for your composer.


### location for the Silverstripe Upgrade module

`->setLocationOfUpgradeModule('~/.composer/vendor/bin/upgrade-code')`: you would have installed this already.


### include upgrading `_ss_environment` file?

`->setIncludeEnvironmentFileUpdate(false|true)`: I would leave this out and do this manually.


### run reorganise task?

`->setIncludeReorganiseTask(false|true)`: do you want the folder names to be changed?


### run webroot task

`->setIncludeWebrootUpdateTask(false|true)`


### start from

`->SetStartFrom('mymethod')`: allows you to start the sequence from a particular method. See MetaUpgrader::run to see what methods are being run in what order.


### end with

`->EndWith('mymethod')`: allows you to end the sequence after a particular method.  See MetaUpgrader::run to see what methods are being run in what order.
