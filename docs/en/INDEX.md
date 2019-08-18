# Upgrade your module/project to Silverstripe 4

This module helps you upgrade Silverstripe 3 modules AND Projects to SS4 with the least amount of effort.

After inspection you can then merge your upgraded project / module into `dev/master` as you see fit.

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
 * add your module / project again (upgrade branch)
 * do more upgrade stuff

Once that has completed you can MANUALLY:
 * review and fix any outstanding issues (many of them clearly marked) OR rerun full process (it is repeatable).
 * merge your upgrade branch into your master (and delete the upgrade branch)
 * you are now SS4 ready

# prerequisites before you start:

 
 - projects need to be a git repository (private is fine)
 - for modules only:
     - module to be upgraded needs to be listed on packagist.   
     - composer file needs to follow this pattern (installer-name requirement may be dropped in the future)

- **IMPORTANT** The module's / project's PHP classes are organised in meaningfull folders so that they are PSR-4 ready. This means that you create folders, similar to **silverstripe/framework**, where classes are put in semantic folder names.  
You do not need to use title case for the folder names as this will be fixed by the upgrade tool.

- Separate MyPage and MyPageController into separate classes and move them into Pages and Control folder (moving both into a PageTypes folder is fine also). 
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

OR:
```
/code/PageTypes/MyPage1.php (contains class MyPage1)
/code/PageTypes/MyPage2.php (contains class MyPage2)
/code/PageTypes/MyPage1Controller.php (contains class MyPage1Controller)
/code/PageTypes/MyPage2Controller.php (contains class MyPage2Controller)
```


# additional things to consider before you start
- It is recommended that your composer file follows this pattern (module only):
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

- create a tag of the module/project you are upgrading


# what does this module do?

To see a list of default upgrade tasks that will run, visit the auto-generated list of [default tasks](/docs/en/AvailableTasks.md).

To customise your list of tasks, please see config options below.


It wraps around https://github.com/silverstripe/silverstripe-upgrader/
to make the upgrade more automated.

In short, it checks out dev-master of your module / project.  Adds a branch named
`3` for backwards compatability, then it ads a temporary upgrade branch.

Next, it runs a bunch of upgrades, including the ones in the original SilverStripe
upgrade module.


# installation and usage:

1.  Install this module in your web-root (or another place if needed - we use `/var/www/silverstripe-upgrade_to_silverstripe_4/` in example below) as follows:

```sh
    $ mkdir /var/www/silverstripe-upgrade_to_silverstripe_4/
    $ cd /var/www/silverstripe-upgrade_to_silverstripe_4/
    $ git clone git@github.com:sunnysideup/silverstripe-upgrade_to_silverstripe_4.git .
    $ composer update
```

2.  Create a php file (e.g. `/index.php`) in your root dir, i.e. `/var/www/silverstripe-upgrade_to_silverstripe_4/` (or anywhere else where you can run it) - using the examples provided:

  - [full](/run.me.index.full.php.EXAMPLE) - overview of all settings available
  - [short](/run.me.index.short.php.EXAMPLE) - least amount of settings required


3. Run the file to upgrade your module / project - e.g.

```sh
    $ php index.php
```

Note that you can run this step by step and that you can also use the following commands:

- restart
- again
- more options become available all the time!

e.g. 
```sh
    $ php index.php again
```
OR 

```sh
    $ php index.php restart
```

**NB: You can run (3) as many times as you see fit**.


4. Apply any manual final fixes to the upgrade branch of your module / project to make it SS4 ready. E.g.
  
  a. Add $private static $table_name for every class that extends DataObject, including pages (e.g private static $table_name = 'WebPortfolioPage';).
  
  b. move template files (in the future this module may do this for you).

  c. Check for use statements WITHOUT name spacing - as these may need attention.
  
  d. etc...

5. Merge the upgrade branch into `dev-master` as you see fit.


# main config options:

### root directory

`->setRootDir('/var/www')`:
This is meant to be the directory where you do the work.
This should be a folder where you usually save your websites locally so that you can test your upgraded module.


### upgrade directory

`->setWebRootName('upgradeto4')`:
This is the name of the directory that is created in the root dir where the upgrade takes place.
That is, your actual module will be cloned in the `[rootdir]/[upgrade directory]` and when completed, this directory will be deleted.
**Careful! Only use this directory for automated work as it will be deleted when you run the upgrade again.**


### list of modules / projects

`->setArrayOfModules([])`:
This contains a list of modules you intend to update.
We recommend updating one module / project at a time. For details see: [example index file](/example-index.full.php).

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


### run immediately or create bash script?

`->setRunImmediately(false)`:
When you set `runImmediately` to true, the PHP code will use the PHP `exec` function to run commands.
If you set this option to false then you will be provided with a sample bash script.  It is recommended, however, that you use the
`->setRunImmediately(true)` to run this tool rather than using the provided bash script.

Bash script option is currently NOT working. You should run it immediately.


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

