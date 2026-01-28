<?php

namespace Sunnysideup\UpgradeSilverstripe\UpgradeRecipes;

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
        'WebRootDirCheckFoldersReady' => [],
        'WebRootDirReset-1' => [],
        'SwitchPhpVersion-1' => [
            'version' => '7.2', //huringa requires it.
        ],
        'CheckoutDefaultBranch-1' => [],
        'BranchesAddLegacyBranch' => [
            'nameOfLegacyBranch' => '3',
        ],
        'BranchAddUpgradeStarterBranch' => [],
        'CheckoutUpgradeStarterBranch-1' => [],
        'ComposerMakeRequirementsMoreLenient' => [],
        'LintPHPCompatabilityCheck' => [],
        'WebRootDirReset-2' => [],

        'CheckoutUpgradeStarterBranch-2' => [],
        'BranchesAddTempUpgradeBranch' => [],
        'ComposerBackupOriginalRequirements' => [],
        'LintOutdatedPHPStyles' => [],
        'FindFilesWithMoreThanOneClass' => [],

        'CreatePublicFolder' => [],
        'AddTableName' => [],
        'ChangeControllerInitToProtected' => [],
        // 'AddTableNamePrivateStatic' => [],
        'ComposerRemoveRequirements' => [], // remove framework, etc...
        'RecomposeHomeBrew' => [], // set all requoirements to *, except for recipe-cms
        'ComposerUpdateRequirements' => [], // run find and replace for specific packages.
        'RemoveInstallerFolder' => [], // remove installer folder from composer.json
        'UpgradeDevBuild' => [], // fix dev/build in composer.json
        'SwitchPhpVersion-2' => [
            'version' => '7.4',
        ],
        'WebRootDirReset-3' => [],

        //Step2: MoveToNewVersion
        'ComposerInstallProject' => [],
        'ComposerAddOneByOne' => [],
        'ComposerCompatibilityCheckerStep3' => [],
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
        'SwitchPhpVersion-3' => [
            'version' => '8.1',
        ],
        'InspectAPIChanges-1' => [],
        'DatabaseMigrationLegacyYML' => [],
        'Reorganise' => [],
        'ComposerUpdateModuleType' => [],
        'AddVendorExposeDataToComposer' => [],
        'InspectAPIChanges-2' => [],
        'FixClassNamesWithUnderscores' => [],
        // 'WebRootUpdate' => [],
        //step6: Check
        'SwitchPhpVersion-4' => [
            'version' => '8.3',
        ],
        'LintPSR2' => [],
        'AddDotEnvFile' => [],
        'FinalDevBuild' => [],
        'RunImageTask' => [],
        'DoMigrateSiteTreeLinkingTask' => [],
        'FindFilesWithSimpleUseStatements' => [],
        //step7: Lock-in
        'FinaliseUpgradeWithMergeIntoDefaultBranch' => [],
    ];

    protected $frameworkComposerRestraint = '~4@stable';
}
