<?php

namespace Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes;

use Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes\BaseClass;

class Ss3ToSs4 extends BaseClass
{

    /**
     * name of the branch created to do the upgrade
     * @var string branch name
     */
    protected $nameOfTempBranch = 'automatedbranch/upgrade/3to4';

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
        //Step1: Prepare
        'CheckThatFoldersAreReady' => [],
        'ResetWebRootDir-1' => [],

        'CheckoutDevMaster-1' => [],
        'AddLegacyBranch' => [
            'nameOfLegacyBranch' => '3'
        ],
        'AddUpgradeBranch' => [],
        'PHPCompatabilityCheck' => [],
        'ResetWebRootDir-2' => [],

        'CheckoutUpgradeStarterBranch' => [],
        'AddTempUpgradeBranch' => [],
        'ComposerCompatibilityCheckerStep1' => [],
        'FixOutdatedPHPStyles' => [],
        'FindFilesWithMoreThanOneClass' => [],

        'CreatePublicFolder-1' => [],
        'AddTableName' => [],
        'ChangeControllerInitToProtected' => [],
        // 'AddTableNamePrivateStatic' => [],
        'RemoveComposerRequirements' => [
            'package' => 'silverstripe/framework',
        ],
        'RecomposeHomeBrew' => [],
        'UpdateComposerRequirements' => [],
        'RemoveInstallerFolder' => [],
        'ResetWebRootDir-3' => [],

        //Step2: MoveToNewVersion
        'ComposerInstallProject' => [],
        'ComposerCompatibilityCheckerStep2' => [],
        'CreatePublicFolder-2' => [],
        'Recompose' => [],

        //Step3: FixBeforeStart
        'ChangeEnvironment' => [],
        'MoveCodeToSRC' => [],
        'CreateClientFolder' => [],
        'SearchAndReplace' => [],
        'FixRequirements' => [],
        'UpperCaseFolderNamesForPSR4' => [],

        //Step4: CoreUpgrade
        'AddNamespace' => [],
        'Upgrade' => [],
        'AddPSR4Autoloading' => [],

        //Step5: FixUpgrade
        'FixBadUseStatements' => [],
        'InspectAPIChanges-1' => [],
        'DatabaseMigrationLegacyYML' => [],
        'Reorganise' => [],
        'UpdateComposerModuleType' => [],
        'AddVendorExposeDataToComposer' => [],
        'InspectAPIChanges-2' => [],
        // 'WebRootUpdate' => [],
        //step6: Check
        'ApplyPSR2' => [],
        'FinalDevBuild' => [],
        'RunImageTask' => [],
        'DoMigrateSiteTreeLinkingTask' => [],
        'FindFilesWithSimpleUseStatements' => [],
        //step7: Lock-in
        'FinaliseUpgradeWithMergeIntoMaster' => [],
    ];

    protected $frameworkComposerRestraint = '~4@stable';
}
