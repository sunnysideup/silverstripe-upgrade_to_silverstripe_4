<h3>Step 1/21. Remove and reset Web Root</h3><p>
            Delete the web root directory to allow for a fresh install.</p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\ResetWebRootDir.phpLocation <a href = "Place/holder/location"> Open Class</a></p><h3>Step 2/21. Add Legacy Branch</h3><p>
            Creates a legacy branch: 3 so that you
            can keep making bugfixes to older versions.</p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\AddLegacyBranch.phpLocation <a href = "Place/holder/location"> Open Class</a></p><h3>Step 3/21. Remove and reset Web Root</h3><p>
            Delete the web root directory to allow for a fresh install.</p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\ResetWebRootDir.phpLocation <a href = "Place/holder/location"> Open Class</a></p><h3>Step 4/21. Add Upgrade Branch</h3><p>
            Adds a new branch (temp-upgradeto4-branch) to your
            repository (/)
            that is going to be used for upgrading it.</p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\AddUpgradeBranch.phpLocation <a href = "Place/holder/location"> Open Class</a></p><h3>Step 5/21. Update composer.json requirements</h3><p>
            Change  to :
            in the composer file of your module.</p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\UpdateComposerRequirements.phpLocation <a href = "Place/holder/location"> Open Class</a></p><h3>Step 6/21. Update composer.json from 3 to 4</h3><p>
            Runs the silverstripe/upgrade task "recompose". See:
            https://github.com/silverstripe/silverstripe-runActualTask#recompose</p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\Recompose.phpLocation <a href = "Place/holder/location"> Open Class</a></p><h3>Step 7/21. Update composer.json requirements</h3><p>
            Change  to :
            in the composer file of your module.</p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\UpdateComposerRequirements.phpLocation <a href = "Place/holder/location"> Open Class</a></p><h3>Step 8/21. Update composer type to silverstripe-vendormodule </h3><p>
            Replaces the composer type from silverstripe-module to silverstripe-vendormodule in line with SS4 standards.
            This means your module will be installed in the vendor folder after this upgrade.</p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\UpdateComposerModuleType.phpLocation <a href = "Place/holder/location"> Open Class</a></p><h3>Step 9/21. Remove installer-name from composer.json</h3><p>
            Remove installer folder from composer.json file so that package
            installs into vendor folder.</p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\RemoveInstallerFolder.phpLocation <a href = "Place/holder/location"> Open Class</a></p><h3>Step 10/21. Remove and reset Web Root</h3><p>
            Delete the web root directory to allow for a fresh install.</p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\ResetWebRootDir.phpLocation <a href = "Place/holder/location"> Open Class</a></p><h3>Step 11/21. Composer Install Silverstripe 4</h3><p>
            Install a basic / standard install of Silverstripe (^4)
            using composer</p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\ComposerInstallProject.phpLocation <a href = "Place/holder/location"> Open Class</a></p><h3>Step 12/21. Change Environment File</h3><p>
            Runs the silverstripe/upgrade task "environment". See:
            https://github.com/silverstripe/silverstripe-runActualTask#environment.
            You can use this command to migrate an SilverStripe 3 _ss_environment.php
            file to the .env format used by SilverStripe 4.</p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\ChangeEnvironment.phpLocation <a href = "Place/holder/location"> Open Class</a></p><h3>Step 13/21. Move code to src folder</h3><p>
            Move code folder to src folder to match PSR requirements.</p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\MoveCodeToSRC.phpLocation <a href = "Place/holder/location"> Open Class</a></p><h3>Step 14/21. Search and Replace</h3><p>
            Replaces a bunch of code snippets in preparation of the upgrade.
            Controversial replacements will be replaced with a comment
            next to it so you can review replacements easily.</p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\SearchAndReplace.phpLocation <a href = "Place/holder/location"> Open Class</a></p><h3>Step 15/21. Fix Folder Case</h3><p>
            Change your src/code folders from lowercase to TitleCase - e.g.
            yourmodule/src/model becomes yourmodule/src/Model to match the upgrade
            steps.</p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\UpperCaseFolderNamesForPSR4.phpLocation <a href = "Place/holder/location"> Open Class</a></p><h3>Step 16/21. Name Spaces</h3><p>
            Places all your code into namespaces (provided by silvertripe/runActualTask),
            using the PSR-4 approach (matching folders and namespaces)</p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\AddNamespace.phpLocation <a href = "Place/holder/location"> Open Class</a></p><h3>Step 17/21. Update Code</h3><p>
            Runs the silverstripe/upgrade task "upgrade". See:
            Upgrade a variety of stuff (e.g. update reference with namespaces)
            https://github.com/silverstripe/silverstripe-runActualTask#upgrade</p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\Upgrade.phpLocation <a href = "Place/holder/location"> Open Class</a></p><h3>Step 18/21. After load fixes (inspect)</h3><p>
            Runs the silverstripe/upgrade task "inpect". See:
            https://github.com/silverstripe/silverstripe-runActualTask#inspect.
            Once a project has all class names migrated, and is brought up to a
            "loadable" state (that is, where all classes reference or extend real classes)
            then the inspect command can be run to perform additional automatic code rewrites.
            This step will also warn of any upgradable code issues that may prevent a succesful upgrade.</p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\InspectAPIChanges.phpLocation <a href = "Place/holder/location"> Open Class</a></p><h3>Step 19/21. move mysite/code folder to app/src</h3><p>
            Runs the silverstripe/upgrade task "reorganise". See:
            https://github.com/silverstripe/silverstripe-runActualTask#reorganise
            You can use this command to reorganise your folder structure to
            conform to the new structure introduced with SilverStripe 4.1.
            Your mysite folder will be renamed to app and your code folder will be rename to src.
            </p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\Reorganise.phpLocation <a href = "Place/holder/location"> Open Class</a></p><h3>Step 20/21. Run dev/build</h3><p>
            Run a dev/build as a smoke test to see if all is well.</p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\FinalDevBuild.phpLocation <a href = "Place/holder/location"> Open Class</a></p><h3>Step 21/21. After load fixes (inspect)</h3><p>
            Runs the silverstripe/upgrade task "inpect". See:
            https://github.com/silverstripe/silverstripe-runActualTask#inspect.
            Once a project has all class names migrated, and is brought up to a
            "loadable" state (that is, where all classes reference or extend real classes)
            then the inspect command can be run to perform additional automatic code rewrites.
            This step will also warn of any upgradable code issues that may prevent a succesful upgrade.</p><p>Classname:Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\InspectAPIChanges.phpLocation <a href = "Place/holder/location"> Open Class</a></p>