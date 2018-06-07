This module aims to help developers upgrade SS3 modules to SS4 without doing any manual rewriting. You can provide a list of modules and run the code below.  This will create a branch in your module that is SS4 ready. After inspection you can then merge this into `dev/master` as you see fit.


# prerequisites before you start

 - install https://github.com/silverstripe/silverstripe-upgrader#install globally: `composer global require silverstripe/upgrader`
 - module needs to be listed on packagist
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

- create 3 branch so that you can keep adding patches to your SS3 version (OPTIONAL)
- create a tag (OPTIONAL)

# what it does:

For a list of modules, it deletes (if it exists) and (re-)creates a branch for upgrading your module from SS3 to SS4.

In this branch:

 - updates the composer requirements: https://github.com/silverstripe/silverstripe-upgrader/blob/master/docs/en/recompose.md
 - Updates the environment file (environment): https://github.com/silverstripe/silverstripe-upgrader/blob/master/docs/en/environment.md (OPTIONAL)
 - Adds Namespace (add-namespace): https://github.com/silverstripe/silverstripe-upgrader/blob/master/docs/en/add-namespace.md
 - Refactors your existing code base (upgrade): https://github.com/silverstripe/silverstripe-upgrader/blob/master/docs/en/upgrade.md
 - Applies API changes (inspect): https://github.com/silverstripe/silverstripe-upgrader/blob/master/docs/en/inspect.md
 - Renames mysite (reorganise): https://github.com/silverstripe/silverstripe-upgrader/blob/master/docs/en/reorganise.md (OPTIONAL)
 - Switches to public web-root: https://github.com/silverstripe/silverstripe-upgrader/blob/master/docs/en/webroot.md (OPTIONAL)

It then commits and pushes the results for inspection.



# usage:

1.  Run in your web root (also see below):
    `git clone git@github.com:sunnysideup/silverstripe-upgrade_to_silverstripe_4.git`

2.  Create a new php file (e.g. `index.php`) in your root dir (or anywhere else  you can run it):

```php
<?php
require_once('silverstripe-upgrade_to_silverstripe_4/src/MetaUpgrader.php');
$obj = MetaUpgrader::create()
    ->setRunImmediately(true)
    ->setAboveWebRootDir('/var/www')
    ->setWebrootDirName('__upgradeto4__')
    ->setVendorName('sunnysideup')
    ->setArrayOfModules(
      [
          'my-module-name-foo-bar'
      ]
    )
    ->setNameOfTempBranch('4.1-TEMP-upgrade')
    ->setComposerEnvironmentVars('COMPOSER_HOME="/home/UserName"')
    ->setLocationOfUpgradeModule('~/.composer/vendor/bin/upgrade-code')
    ->setIncludeEnvironmentFileUpdate(false)
    ->setIncludeReorganiseTask(true)
    ->setIncludeWebrootUpdateTask(true)
    ->setRestartFrom('')
    ->run();
```
run the file to upgrade your modules - e.g.
```sh
    $ php index.php
```

3. inspect your new branch that is SS4 ready and merge it into `dev-master` as you see fit.


# options:

### run immediately or create bash script?

`->setRunImmediately(false)`: by default, the script will not do any actual upgrading, but rather, it will output a bash script that does the actual upgrading.
When you set `runImmediately` to true, the PHP code will use the `exec` function to output commands on the `command line` immediately. **Careful! Even if you do not run the code immediately, a bunch of code will still be executed on the command line to inspect the module.**

By default running on command line, it will run immediately and when accessing it through http, you will get the script to run in the future.


### root directory

`->setRootDir('/var/www/')`: this is meant to be the directory where you do the work, where you usually save your websites that you work on locally.


### upgrade directory

`->setUpgradeDirName('upgradeto4')`: this is the name of the directory that is created in the root dir where the upgrade takes place. **Careful! Only use this directory for automated work as it will be deleted when you run the upgrade again.**

### vendor name

`->setVendorName('SunnySideUp')`: you can only upgrade modules for one vendor at the time.

### list of modules

`->setArrayOfModules([])`: this is the name as listed on packagist e.g. `sunnysideup/metatags` should be listed as `metatags`.

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

### restart from

`->setRestartFrom('mymethod')`: allows you to start the sequence from a particular point.
