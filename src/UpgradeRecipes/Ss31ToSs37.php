<?php


namespace Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes;
use Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes\BaseClass;


class Ss31ToSs37 extends BaseClass
{

    /**
     * name of the branch created to do the upgrade
     * @var string branch name
     */
    protected $nameOfTempBranch = 'automatedbranch/upgrade/to37';

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
            'nameOfLegacyBranch' => '3.1'
        ],
        'AddUpgradeBranch' => [],
        'ResetWebRootDir-2' => [],

        'CheckoutUpgradeStarterBranch' => [],
        'AddTempUpgradeBranch' => [],
        'FixOutdatedPHPStyles' => [],

        'UpdateComposerRequirements-1' => [
            'Package' => 'silverstripe/framework',
            'NewVersion' => '~3.7',
        ],
        'UpdateComposerRequirements-2' => [
            'Package' => 'silverstripe/cms',
            'NewVersion' => '~3.7',
        ],
        'SearchAndReplace-1' => [
            'ToFolder' => 'SS34',
        ],
        'SearchAndReplace-2' => [
            'ToFolder' => 'SS36',
        ],
        'SearchAndReplace-3' => [
            'ToFolder' => 'SS37',
        ],
        'PHPCompatabilityCheck' => [],
    ];

    protected $frameworkComposerRestraint = '~3.7@stable';
}
