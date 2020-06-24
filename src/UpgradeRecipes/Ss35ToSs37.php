<?php

namespace Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes;

class Ss35ToSs37 extends BaseClass
{
    /**
     * name of the branch created to do the upgrade
     * @var string branch name
     */
    protected $nameOfTempBranch = 'upgrades/automatedbranch/35to37';

    /**
     * The default namespace for all tasks
     * @var string
     */
    protected $defaultNamespaceForTasks = 'Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks';

    #########################################
    # TASKS
    #########################################

    /**
     * An array of all the 'taskName's of the tasks that you wish to run during the execution of this upgrader task.
     * This array can be overriden in the example-index.php file that you create.
     * You can enter a full name space if you need to.
     * The final -x will be removed.  We add -1 or -2 to run the same task multiple times.
     *
     * @var array
     */
    protected $listOfTasks = [
        'CheckThatFoldersAreReady' => [],
        'ResetWebRootDir-1' => [],

        'CheckoutDevMaster-1' => [],
        'AddLegacyBranch' => [
            'nameOfLegacyBranch' => 'pre-upgrade37',
        ],

        'ResetWebRootDir-2' => [],

        'CheckoutDevMaster-2' => [],

        'AddUpgradeBranch' => [],

        'UpdateComposerRequirements-1' => [
            'Package' => 'silverstripe/framework',
            'NewVersion' => '~3.7',
        ],
        'UpdateComposerRequirements-2' => [
            'Package' => 'silverstripe/cms',
            'NewVersion' => '~3.7',
        ],
        'UpdateComposerRequirements-3' => [
            'Package' => 'silverstripe/reports',
            'NewVersion' => '~3.7',
        ],
        'UpdateComposerRequirements-4' => [
            'Package' => 'silverstripe/siteconfig',
            'NewVersion' => '~3.7',
        ],

        'SearchAndReplace' => [
            'SourceFolders' => [
                'SS37',
            ],
        ],
        'PHPCompatabilityCheck' => [],
    ];

    protected $frameworkComposerRestraint = '~3.7@stable';
}
