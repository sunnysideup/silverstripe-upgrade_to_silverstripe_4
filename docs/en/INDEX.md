This module aims to help developers upgrade SS3 modules to SS4 without doing any manual rewriting. You can provide a list of modules and run the code below.  This will create a branch in your module that is SS4 ready. After inspection you can then merge this into `dev/master` as you see fit.

# prerequisites before you start:

 - module to be upgraded needs to be listed on packagist
 - projects need to be a git repository (private is fine)
 - for modules only, composer file needs to follow this pattern (installer-name requirement may be dropped in the future)


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

It wraps around https://github.com/silverstripe/silverstripe-upgrader/
to make the upgrade more automated.

In short, it checks out dev-master of your module / project.  Adds a branch named
`3` for backwards compatability, then it ads a temporary upgrade branch.

Next, it runs a bunch of upgrades, including the ones in the original SilverStripe
upgrade module.

To see a full list of steps in this module, please visit: <a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/edit/master/docs/en/AvailableTasks.md">Available Tasks</a> list.

# installation and usage:

1.  Install this module in your web-root (or another place if needed - we use `/var/www/upgrades/` in example below):
    `composer install sunnysideup/upgrade_to_silverstripe_4 /var/www/upgrades/`

2.  Create a new php file (e.g. `index.php`) in this folder:

We have included two example files like this in the module root.


3. Run the file to upgrade your modules - e.g.

```sh
    $ php index.php
```


4. Apply any final fixes to this branch to make it SS4 ready.


5. Merge the upgrade branch into `dev-master` as you see fit.
