<?php

namespace Sunnysideup\UpgradeToSilverstripe4\UpgradeRecipes;

class Ss3ToSs4 extends BaseClass
{
    /**
     * name of the branch to be created that we use a starter branch for upgrade
     * @var string branch name
     */
    protected $nameOfUpgradeStarterBranch = 'upgrades/starting-point/ss4-upgrade';

    /**
     * name of the branch created to do the upgrade
     * @var string branch name
     */
    protected $nameOfTempBranch = 'upgrades/automatedbranch/3to4';

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
        'SwitchPhpVersion-1' => [
            'version' => '7.2', //huringa requires it.
        ],
        'CheckoutDevMaster-1' => [],
        'AddLegacyBranch' => [
            'nameOfLegacyBranch' => '3',
        ],
        'AddUpgradeStarterBranch' => [],
        'CheckoutUpgradeStarterBranch-1' => [],
        'MakeRequirementsMoreFlexible' => [],
        'PHPCompatabilityCheck' => [],
        'ResetWebRootDir-2' => [],

        'CheckoutUpgradeStarterBranch-2' => [],
        'AddTempUpgradeBranch' => [],
        'ComposerCompatibilityCheckerStep1' => [],
        'FixOutdatedPHPStyles' => [],
        'FindFilesWithMoreThanOneClass' => [],

        'CreatePublicFolder' => [],
        'AddTableName' => [],
        'ChangeControllerInitToProtected' => [],
        // 'AddTableNamePrivateStatic' => [],
        'RemoveComposerRequirements' => [],
        'RecomposeHomeBrew' => [],
        'UpdateComposerRequirements' => [],
        'RemoveInstallerFolder' => [],
        'UpgradeDevBuild' => [],
        'SwitchPhpVersion-2' => [
            'version' => '7.4',
        ],
        'ResetWebRootDir-3' => [],

        //Step2: MoveToNewVersion
        'ComposerInstallProject' => [],
        'ComposerCompatibilityCheckerStep2' => [],
        'Recompose' => [],

        //Step3: FixBeforeStart
        'ChangeEnvironment' => [],
        'MoveCodeToSRC' => [],
        'MoveMysiteToApp' => [],
        'MoveTemplates' => [],
        'CreateClientFolder' => [],
        'SearchAndReplace' => [],
        'FixTemplateIncludeStatements' => [],
        'FixRequirements' => [],
        'UpperCaseFolderNamesForPSR4' => [],

        //Step4: CoreUpgrade
        'AddNamespace' => [],
        'Upgrade' => [],
        'AddPSR4Autoloading' => [],

        //Step5: FixUpgrade
        'FixBadUseStatements-1' => [],
        'FixBadUseStatements-2' => [],
        'InspectAPIChanges-1' => [],
        'DatabaseMigrationLegacyYML' => [],
        'Reorganise' => [],
        'UpdateComposerModuleType' => [],
        'AddVendorExposeDataToComposer' => [],
        'InspectAPIChanges-2' => [],
        'FixClassNamesWithUnderscores' => [],
        // 'WebRootUpdate' => [],
        //step6: Check
        'ApplyPSR2' => [],
        'AddDotEnvFile' => [],
        'FinalDevBuild' => [],
        'RunImageTask' => [],
        'DoMigrateSiteTreeLinkingTask' => [],
        'FindFilesWithSimpleUseStatements' => [],
        //step7: Lock-in
        'FinaliseUpgradeWithMergeIntoMaster' => [],
    ];

    protected $frameworkComposerRestraint = '~4@stable';
}
