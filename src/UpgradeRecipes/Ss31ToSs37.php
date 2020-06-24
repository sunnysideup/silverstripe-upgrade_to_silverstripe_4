<?php

namespace Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes;

class Ss31ToSs37 extends BaseClass
{
    /**
     * name of the branch created to do the upgrade
     * @var string branch name
     */
    protected $nameOfTempBranch = 'upgrades/automatedbranch/31to37';

    /**
     * The default namespace for all tasks
     * @var string
     */
    protected $defaultNamespaceForTasks = 'Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks';

    #########################################
    # TASKS
    #########################################

    protected $listOfTasks = [
        'CheckThatFoldersAreReady' => [],
        'ResetWebRootDir-1' => [],

        'CheckoutDevMaster-1' => [],
        'AddLegacyBranch' => [
            'nameOfLegacyBranch' => 'pre-upgrade3.1',
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
        'SearchAndReplace' => [
            'SourceFolders' => [
                'SS34',
                'SS36',
                'SS37',
            ],
        ],
        'PHPCompatabilityCheck' => [],
    ];

    protected $frameworkComposerRestraint = '~3.7@stable';
}
