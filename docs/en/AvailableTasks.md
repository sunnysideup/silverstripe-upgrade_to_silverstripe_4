<h3>Step 1/21. Remove and reset Web Root</h3><p><br /><strong>Description: </strong>
            Delete the web root directory to allow for a fresh install.<br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\ResetWebRootDir<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p><h3>Step 2/21. Add Legacy Branch</h3><p><br /><strong>Description: </strong>
            Creates a legacy branch: 3 so that you
            can keep making bugfixes to older versions.<br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\AddLegacyBranch<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p><h3>Step 3/21. Remove and reset Web Root</h3><p><br /><strong>Description: </strong>
            Delete the web root directory to allow for a fresh install.<br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\ResetWebRootDir<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p><h3>Step 4/21. Add Upgrade Branch</h3><p><br /><strong>Description: </strong>
            Adds a new branch (temp-upgradeto4-branch) to your
            repository (/)
            that is going to be used for upgrading it.<br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\AddUpgradeBranch<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p><h3>Step 5/21. Update composer.json requirements</h3><p><br /><strong>Description: </strong>
            Change  to :
            in the composer file of your module.<br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\UpdateComposerRequirements<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p><h3>Step 6/21. Update composer.json from 3 to 4</h3><p><br /><strong>Description: </strong>
            Runs the silverstripe/upgrade task "recompose". See:
            https://github.com/silverstripe/silverstripe-runActualTask#recompose<br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\Recompose<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p><h3>Step 7/21. Update composer.json requirements</h3><p><br /><strong>Description: </strong>
            Change  to :
            in the composer file of your module.<br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\UpdateComposerRequirements<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p><h3>Step 8/21. Update composer type to silverstripe-vendormodule </h3><p><br /><strong>Description: </strong>
            Replaces the composer type from silverstripe-module to silverstripe-vendormodule in line with SS4 standards.
            This means your module will be installed in the vendor folder after this upgrade.<br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\UpdateComposerModuleType<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p><h3>Step 9/21. Remove installer-name from composer.json</h3><p><br /><strong>Description: </strong>
            Remove installer folder from composer.json file so that package
            installs into vendor folder.<br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\RemoveInstallerFolder<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p><h3>Step 10/21. Remove and reset Web Root</h3><p><br /><strong>Description: </strong>
            Delete the web root directory to allow for a fresh install.<br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\ResetWebRootDir<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p><h3>Step 11/21. Composer Install Silverstripe 4</h3><p><br /><strong>Description: </strong>
            Install a basic / standard install of Silverstripe (^4)
            using composer<br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\ComposerInstallProject<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p><h3>Step 12/21. Change Environment File</h3><p><br /><strong>Description: </strong>
            Runs the silverstripe/upgrade task "environment". See:
            https://github.com/silverstripe/silverstripe-runActualTask#environment.
            You can use this command to migrate an SilverStripe 3 _ss_environment.php
            file to the .env format used by SilverStripe 4.<br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\ChangeEnvironment<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p><h3>Step 13/21. Move code to src folder</h3><p><br /><strong>Description: </strong>
            Move code folder to src folder to match PSR requirements.<br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\MoveCodeToSRC<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p><h3>Step 14/21. Search and Replace</h3><p><br /><strong>Description: </strong>
            Replaces a bunch of code snippets in preparation of the upgrade.
            Controversial replacements will be replaced with a comment
            next to it so you can review replacements easily.<br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\SearchAndReplace<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p><h3>Step 15/21. Fix Folder Case</h3><p><br /><strong>Description: </strong>
            Change your src/code folders from lowercase to TitleCase - e.g.
            yourmodule/src/model becomes yourmodule/src/Model to match the upgrade
            steps.<br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\UpperCaseFolderNamesForPSR4<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p><h3>Step 16/21. Name Spaces</h3><p><br /><strong>Description: </strong>
            Places all your code into namespaces (provided by silvertripe/runActualTask),
            using the PSR-4 approach (matching folders and namespaces)<br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\AddNamespace<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p><h3>Step 17/21. Update Code</h3><p><br /><strong>Description: </strong>
            Runs the silverstripe/upgrade task "upgrade". See:
            Upgrade a variety of stuff (e.g. update reference with namespaces)
            https://github.com/silverstripe/silverstripe-runActualTask#upgrade<br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\Upgrade<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p><h3>Step 18/21. After load fixes (inspect)</h3><p><br /><strong>Description: </strong>
            Runs the silverstripe/upgrade task "inpect". See:
            https://github.com/silverstripe/silverstripe-runActualTask#inspect.
            Once a project has all class names migrated, and is brought up to a
            "loadable" state (that is, where all classes reference or extend real classes)
            then the inspect command can be run to perform additional automatic code rewrites.
            This step will also warn of any upgradable code issues that may prevent a succesful upgrade.<br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\InspectAPIChanges<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p><h3>Step 19/21. move mysite/code folder to app/src</h3><p><br /><strong>Description: </strong>
            Runs the silverstripe/upgrade task "reorganise". See:
            https://github.com/silverstripe/silverstripe-runActualTask#reorganise
            You can use this command to reorganise your folder structure to
            conform to the new structure introduced with SilverStripe 4.1.
            Your mysite folder will be renamed to app and your code folder will be rename to src.
            <br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\Reorganise<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p><h3>Step 20/21. Run dev/build</h3><p><br /><strong>Description: </strong>
            Run a dev/build as a smoke test to see if all is well.<br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\FinalDevBuild<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p><h3>Step 21/21. After load fixes (inspect)</h3><p><br /><strong>Description: </strong>
            Runs the silverstripe/upgrade task "inpect". See:
            https://github.com/silverstripe/silverstripe-runActualTask#inspect.
            Once a project has all class names migrated, and is brought up to a
            "loadable" state (that is, where all classes reference or extend real classes)
            then the inspect command can be run to perform additional automatic code rewrites.
            This step will also warn of any upgradable code issues that may prevent a succesful upgrade.<br /><strong>Class Name: </strong>Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks\InspectAPIChanges<br /><strong>See:<a href="/src/Tasks/IndividualTasks/AddLegacyBranch.php">Open Class</a></p></p>