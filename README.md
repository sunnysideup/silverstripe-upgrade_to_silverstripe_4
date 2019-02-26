# Upgrade your module to Silverstripe 4

This module helps you upgrade Silverstripe 3 modules to SS4 with the least amount of effort.
You can provide this tool with a list of modules and the tool will create an upgraded branch in each of your modules.
After inspection you can then merge this into `dev/master` as you see fit.

This tool is highly customisable so that you can define your own upgrade path.

# tl;dr

Here is what this module does, AUTOMAGICALLY:

 * clear workbench
 * checkout master of your module
 * create legacy branch
 * create upgrade branch
 * clear workbench
 * do upgrade stuff
 * set up SS4 vanilla install
 * add your module again (upgrade branch)
 * do more upgrade stuff

Once that has completed you an MANUALLY:
 * review and fix any outstanding issues (many of them clearly marked) OR rerun full process (it is repeatable).
 * merge your upgrade branch into your master (and delete it)
 * you are now SS4 ready

# prerequisites before you start:


- Module to be upgraded needs to be listed on packagist.

- **IMPORTANT** The module's PHP classes are organised in meaningfull folders so that they are PSR-4 ready. This means that you create folders, similar to **silverstripe/framework**, where classes are put in semantic folder names.  
You do not need to use title case for the folder names as this will be fixed by the upgrade tool.

- Separate MyPage and MyPageController into separate classes and move them into Pages and Control folder
```
/code/MyPage1.php (contains class MyPage1 AND MyPage1_Controller)
/code/MyPage2.php (contains class MyPage2 AND MyPage2_Controller)
```
becomes:
```
/code/Pages/MyPage1.php (contains class MyPage1)
/code/Pages/MyPage2.php (contains class MyPage2)
/code/Control/MyPage1Controller.php (contains class MyPage1Controller)
/code/Control/MyPage2Controller.php (contains class MyPage2Controller)
```


# additional things to consider before you start
- It is recommended that your composer file follows this pattern:
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
- create a tag of your current state of the module you are upgrading


# what does this module do?

To see a list of default upgrade tasks that will run, visit the auto-generated list of [default tasks](/docs/en/AvailableTasks.md).

To customise your list of tasks, please see config options below.

# installation and usage:

1.  Install this module in your web-root (or another place if needed - we use `/var/www/silverstripe-upgrade_to_silverstripe_4/` in example below) as follows:

```sh
    $ mkdir /var/www/silverstripe-upgrade_to_silverstripe_4/
    $ cd /var/www/silverstripe-upgrade_to_silverstripe_4/
    $ git clone git@github.com:sunnysideup/silverstripe-upgrade_to_silverstripe_4.git .
    $ composer update
```

2.  Create a php file (e.g. `index.php`) in your root dir, i.e. `/var/www/silverstripe-upgrade_to_silverstripe_4/` (or anywhere else where you can run it) - using the examples provided:

  - [full](/example-index.full.php) - overview of all settings available
  - [short](example-index.short.php) - least amount of settings required


3. Run the file to upgrade your modules - e.g.

```sh
    $ php index.php
```

4. Apply any manual final fixes to the upgrade branch of your module(s) to make it/them SS4 ready.

**NB: You can run (3) as many times as you see fit**.

# once completed:

1. Merge upgrade branch into master.

2. Add $private static $table_name for every class that extends DataObject, including pages (e.g private static $table_name = 'WebPortfolioPage';).

3. Check for use statements WITHOUT name spacing - as these may need attention.


# main config options:

### run immediately or create bash script?

`->setRunImmediately(false)`:
When you set `runImmediately` to true, the PHP code will use the PHP `exec` function to run commands.
If you set this option to false then you will be provided with a sample bash script.  It is recommended, however, that you use the
`->setRunImmediately(true)` to run this tool rather than using the provided bash script.


### root directory

`->setRootDir('/var/www')`:
This is meant to be the directory where you do the work.
This should be a folder where you usually save your websites locally so that you can test your upgraded module.


### upgrade directory

`->setWebRootName('upgradeto4')`:
This is the name of the directory that is created in the root dir where the upgrade takes place.
That is, your actual module will be cloned in the `[rootdir]/[upgrade directory]` and when completed, this directory will be deleted.
**Careful! Only use this directory for automated work as it will be deleted when you run the upgrade again.**


### list of modules

`->setArrayOfModules([])`:
This contains a list of modules you intend to update.
We recommend updating one module at a time. For details see: [example index file](/example-index.full.php).

### temp branch

`->setNameOfTempBranch('4.1-TEMP-upgrade')`:
This is the name of the (temporary OR upgrade) branch added to your module.
All upgrade changes will be committed to this branch.
**Careful!  This branch will be deleted every time you run the update process to allow you to rerun the upgrade process.**


### start from

`->setStartFrom('mymethod')`:
Allows you to start the sequence from a particular method.
See [default tasks](/docs/en/AvailableTasks.md) for a list of tasks available.
Use the **Code** for any step to set this particular step as the first step
being executed in your upgrade sequence.

### end with

`->setEndWith('mymethod')`:
Allows you to end the sequence after a particular method.  
See [default tasks](/docs/en/AvailableTasks.md) for a list of tasks available.
Use the **Code** for any step to set this particular step as the last step
being executed in your upgrade sequence.

### log dir

`->setLogFolderDirLocation('/var/www/logs')`:
If set, a log of your upgrade will be saved in this folder.



# advanced config options:

### set list of tasks

`->setListOfTasks()`:
Customise your list of tasks to run on your module during the upgrade.

```php
->setListOfTasks(
    [
        'ResetWebRootDir-1' => [],
        'AddLegacyBranch' => [],
        'ResetWebRootDir-2' => [],
        'UpdateComposerRequirements-2' => [
            'Package' => 'silverstripe/cms',
            'ReplacementPackage' => 'silverstripe/recipe-cms',
            'NewVersion' => '1.1.2'
        ],
        'Reorganise' => []
    ]
)
```
See [available tasks](/docs/en/AvailableTasks.md) and also the current [default tasks](https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/blob/master/src/ModuleUpgrader.php#L105-L139).

### remove from list of tasks

`->removeFromListOfTasks('FooBar')`:
Remove an item from your list of tasks.

### add to list of tasks

`->addToListOfTasks('FooBarTask', $insertBeforeOrAfter = 'FooBarAnotherTask', $isBefore = false)`:
Add an task to your list of tasks.
See [default tasks](/docs/en/AvailableTasks.md) for a list of tasks available.

### composer environment vars

`->setComposerEnvironmentVars('COMPOSER_HOME="/home/UserName"')`:
specific stuff for your composer.


### location for the Silverstripe Upgrade module

`->setLocationOfSSUpgradeModule('~/.composer/vendor/bin/upgrade-code')`:
this module is also installed with this tool (via composer requirements)
so, in general, there is no need to set this.


### include upgrading `_ss_environment` file?

`->setIncludeEnvironmentFileUpdate(false|true)`:
we recommend to set this to false
as it is easier to do this manually.
See https://github.com/silverstripe/silverstripe-upgrader/blob/master/README.md#environment
for details.


### run reorganise task?

`->setIncludeReorganiseTask(false|true)`:
do you want the folder names to be changed?
See https://github.com/silverstripe/silverstripe-upgrader/blob/master/README.md#reorganise
for details.


### run webroot task

`->setIncludeWebRootUpdateTask(false|true)`:
See https://github.com/silverstripe/silverstripe-upgrader/blob/master/README.md#webroot
for details


# Important references:

* https://github.com/silverstripe/silverstripe-upgrader/issues/71#issuecomment-395244428
