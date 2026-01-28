<?php

namespace Sunnysideup\UpgradeSilverstripe\UpgradeRecipes;

class Ss31ToSs37 extends BaseClass
{
    /**
     * name of the branch to be created that we use a starter branch for upgrade
     * @var string branch name
     */
    protected $nameOfUpgradeStarterBranch = 'upgrades/starting-point/ss37-upgrade';

    /**
     * name of the branch created to do the upgrade
     * @var string branch name
     */
    protected $nameOfTempBranch = 'upgrades/automatedbranch/31to37';

    /**
     * The default namespace for all tasks
     * @var string
     */
    protected $defaultNamespaceForTasks = 'Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks';

    #########################################
    # TASKS
    #########################################

    protected $listOfTasks = [
        'WebRootDirCheckFoldersReady' => [],
        'WebRootDirReset-1' => [],

        'CheckoutDefaultBranch-1' => [],
        'BranchesAddLegacyBranch' => [
            'nameOfLegacyBranch' => 'pre-upgrade3.1',
        ],

        'WebRootDirReset-2' => [],

        'CheckoutDefaultBranch-2' => [],

        'BranchAddUpgradeStarterBranch' => [],

        'ComposerUpdateRequirements-1' => [
            'Package' => 'silverstripe/framework',
            'NewVersion' => '~3.7',
        ],
        'ComposerUpdateRequirements-2' => [
            'Package' => 'silverstripe/cms',
            'NewVersion' => '~3.7',
        ],
        'SearchAndReplace' => [
            'SourceFolders' => [
                'SS34',
                'SS36',
                'SS37',
            ],
        ],
        'LintPHPCompatabilityCheck' => [],
    ];

    protected $frameworkComposerRestraint = '~3.7@stable';
}
