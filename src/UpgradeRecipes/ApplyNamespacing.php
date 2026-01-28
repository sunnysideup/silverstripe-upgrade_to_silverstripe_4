<?php

namespace Sunnysideup\UpgradeSilverstripe\UpgradeRecipes;

class ApplyNamespacing extends BaseClass
{
    /**
     * name of the branch to be created that we use a starter branch for upgrade
     * @var string branch name
     */
    protected $nameOfUpgradeStarterBranch = 'upgrades/starting-point/apply-namespacing-start';

    /**
     * name of the branch created to do the upgrade
     * @var string branch name
     */
    protected $nameOfTempBranch = 'upgrades/automatedbranch/apply-namespacing-do';

    /**
     * The default namespace for all tasks
     * @var string
     */
    protected $defaultNamespaceForTasks = 'Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks';

    #########################################
    # TASKS
    #########################################

    /**
     * An array of all the 'taskNames of the tasks that you wish to run during the execution of this upgrader task.
     * This array can be overriden in the example-index.php file that you create.
     * You can enter a full name space if you need to.
     * The final -x will be removed.  We add -1 or -2 to run the same task multiple times.
     *
     * @var array
     */
    protected $listOfTasks = [
        //Step1: Prepare
        //Step4: CoreUpgrade
        'CheckThatFoldersAreReady' => [],
        'ResetWebRootDir' => [],
        'CheckoutDefaultBranch' => [],
        'CheckoutUpgradeStarterBranch' => [],
        'AddUpgradeStarterBranch' => [],
        'ComposerInstallProject' => [],
        'AddNamespace' => [],
        'Upgrade' => [],
        'DatabaseMigrationLegacyYML' => [],
    ];

    protected $frameworkComposerRestraint = '~4@stable';
}
