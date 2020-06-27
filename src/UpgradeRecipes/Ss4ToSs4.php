<?php

namespace Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes;

class Ss4ToSs4 extends BaseClass
{
    /**
     * name of the branch created to do the upgrade
     * @var string branch name
     */
    protected $nameOfTempBranch = 'upgrades/automatedbranch/linter';

    /**
     * The default namespace for all tasks
     * @var string
     */
    protected $defaultNamespaceForTasks = 'Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks';

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
        'CheckThatFoldersAreReady' => [],
        'ResetWebRootDir-1' => [],
        'AddUpgradeBranch' => [],
        'AddTempUpgradeBranch' => [],
        'ResetWebRootDir-2' => [],
        'PHPCompatabilityCheck' => [],
        'ComposerInstallProject' => [],
        'AddDotEnvFile' => [],
        'FinalDevBuild' => [],
        'ApplyPSR2' => [],
        'AddPHPDoc' => [],
        'FinaliseUpgradeWithMergeIntoMaster' => [],
    ];

    protected $frameworkComposerRestraint = '~4@stable';
}
