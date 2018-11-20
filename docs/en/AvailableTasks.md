<h1>List of Tasks in run order</h1><h3>Step 1 / 25: Check Folders Are Ready</h3><p>
            Checks that all the directories needed to run this tool exist and are writable.
            <br /><strong>Code: </strong>CheckThatFoldersAreReady<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/CheckThatFoldersAreReady.php">CheckThatFoldersAreReady</a></p><h3>Step 2 / 25: Remove and reset Web Root</h3><p>
            Delete the web root directory to allow for a fresh install.<br /><strong>Code: </strong>ResetWebRootDir-1<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/ResetWebRootDir.php">ResetWebRootDir</a></p><h3>Step 3 / 25: Add Legacy Branch</h3><p>
            Creates a legacy branch: "3" of your module so that you
            can keep making bugfixes to older versions.<br /><strong>Code: </strong>AddLegacyBranch<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/AddLegacyBranch.php">AddLegacyBranch</a></p><h3>Step 4 / 25: Remove and reset Web Root</h3><p>
            Delete the web root directory to allow for a fresh install.<br /><strong>Code: </strong>ResetWebRootDir-2<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/ResetWebRootDir.php">ResetWebRootDir</a></p><h3>Step 5 / 25: Add Upgrade Branch</h3><p>
            Adds a new branch (temp-upgradeto4-branch) to your
            repository (Vendor Name/Package Name)
            that is going to be used for upgrading it.<br /><strong>Code: </strong>AddUpgradeBranch<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/AddUpgradeBranch.php">AddUpgradeBranch</a></p><h3>Step 6 / 25: Update composer.json requirements</h3><p>
            Change requirements in composer.json file from
            an Old Package to a New Package: (and New Version)
            For example, we upgrade silverstripe/framework requirement from 3 to 4.<br /><strong>Code: </strong>UpdateComposerRequirements-1<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/UpdateComposerRequirements.php">UpdateComposerRequirements</a></p><h3>Step 7 / 25: Update composer.json from 3 to 4</h3><p>
            Runs the silverstripe/upgrade task "recompose". See:
            https://github.com/silverstripe/silverstripe-runActualTask#recompose<br /><strong>Code: </strong>Recompose<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/Recompose.php">Recompose</a></p><h3>Step 8 / 25: Update composer.json requirements</h3><p>
            Change requirements in composer.json file from
            an Old Package to a New Package: (and New Version)
            For example, we upgrade silverstripe/framework requirement from 3 to 4.<br /><strong>Code: </strong>UpdateComposerRequirements-2<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/UpdateComposerRequirements.php">UpdateComposerRequirements</a></p><h3>Step 9 / 25: Remove installer-name from composer.json</h3><p>
            Remove installer folder from composer.json file so that package
            installs into vendor folder.<br /><strong>Code: </strong>RemoveInstallerFolder<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/RemoveInstallerFolder.php">RemoveInstallerFolder</a></p><h3>Step 10 / 25: Remove and reset Web Root</h3><p>
            Delete the web root directory to allow for a fresh install.<br /><strong>Code: </strong>ResetWebRootDir-3<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/ResetWebRootDir.php">ResetWebRootDir</a></p><h3>Step 11 / 25: Composer Install Silverstripe 4</h3><p>
            Install a basic / standard install of Silverstripe (^4)
            using composer<br /><strong>Code: </strong>ComposerInstallProject<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/ComposerInstallProject.php">ComposerInstallProject</a></p><h3>Step 12 / 25: Change Environment File</h3><p>
            Runs the silverstripe/upgrade task "environment". See:
            https://github.com/silverstripe/silverstripe-runActualTask#environment.
            You can use this command to migrate a SilverStripe 3 _ss_environment.php
            file to the Silverstripe 4 .env format.<br /><strong>Code: </strong>ChangeEnvironment<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/ChangeEnvironment.php">ChangeEnvironment</a></p><h3>Step 13 / 25: Move code to src folder</h3><p>
            Move the code folder to the src folder to match PSR requirements.<br /><strong>Code: </strong>MoveCodeToSRC<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/MoveCodeToSRC.php">MoveCodeToSRC</a></p><h3>Step 14 / 25: Move front-end stuff to a client folder</h3><p>
            Takes the javascript, css, and images folders and puts them in a newly created client folder.<br /><strong>Code: </strong>CreateClientFolder<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/CreateClientFolder.php">CreateClientFolder</a></p><h3>Step 15 / 25: Search and Replace</h3><p>
            Replaces a bunch of code snippets in preparation of the upgrade.
            Controversial replacements will be replaced with a comment
            next to it so you can review replacements easily.<br /><strong>Code: </strong>SearchAndReplace<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/SearchAndReplace.php">SearchAndReplace</a></p><h3>Step 16 / 25: Finds requirements (Requirements::) and fixes them to be exposed properly</h3><p>
            Finds Requirements:: instances and fixes them to be used properly for modules - e.g. [vendorname] / [modulename] : location/for/my/script.js<br /><strong>Code: </strong>FixRequirements<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/FixRequirements.php">FixRequirements</a></p><h3>Step 17 / 25: Fix Folder Case</h3><p>
            Change your src/code folders from lowercase to TitleCase - e.g.
            yourmodule/src/model becomes yourmodule/src/Model in accordance with PSR-4 autoloading<br /><strong>Code: </strong>UpperCaseFolderNamesForPSR4<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/UpperCaseFolderNamesForPSR4.php">UpperCaseFolderNamesForPSR4</a></p><h3>Step 18 / 25: Name Spaces</h3><p>
            Places all your code into namespaces (provided by silvertripe/runActualTask),
            using the PSR-4 approach (matching folders and namespaces).<br /><strong>Code: </strong>AddNamespace<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/AddNamespace.php">AddNamespace</a></p><h3>Step 19 / 25: Update Code</h3><p>
            Runs the silverstripe/upgrade task "upgrade". See:
            Upgrade a variety of stuff (e.g. update reference with namespaces)
            https://github.com/silverstripe/silverstripe-runActualTask#upgrade<br /><strong>Code: </strong>Upgrade<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/Upgrade.php">Upgrade</a></p><h3>Step 20 / 25: After load fixes (inspect)</h3><p>
            Runs the silverstripe/upgrade task "inpect". See:
            https://github.com/silverstripe/silverstripe-runActualTask#inspect.
            Once a project has all class names migrated, and is brought up to a
            "loadable" state (that is, where all classes reference or extend real classes)
            then the inspect command can be run to perform additional automatic code rewrites.
            This step will also warn of any upgradable code issues that may prevent a succesful upgrade.<br /><strong>Code: </strong>InspectAPIChanges-1<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/InspectAPIChanges.php">InspectAPIChanges</a></p><h3>Step 21 / 25: move mysite/code folder to app/src</h3><p>
            Runs the silverstripe/upgrade task "reorganise". See:
            https://github.com/silverstripe/silverstripe-runActualTask#reorganise
            You can use this command to reorganise your folder structure to
            conform to the new structure introduced with SilverStripe 4.1.
            Your mysite folder will be renamed to app and your code folder will be renamed to src.
            <br /><strong>Code: </strong>Reorganise<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/Reorganise.php">Reorganise</a></p><h3>Step 22 / 25: Update composer type to silverstripe-vendormodule </h3><p>
            Replaces the composer type from silverstripe-module to silverstripe-vendormodule in line with SS4 standards.
            This means your module will be installed in the vendor folder after this upgrade.<br /><strong>Code: </strong>UpdateComposerModuleType<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/UpdateComposerModuleType.php">UpdateComposerModuleType</a></p><h3>Step 23 / 25: Adds vendor expose data to composer</h3><p>
            By default we expose all the client related files (images, css and javascript)<br /><strong>Code: </strong>AddVendorExposeDataToComposer<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/AddVendorExposeDataToComposer.php">AddVendorExposeDataToComposer</a></p><h3>Step 24 / 25: Run dev/build</h3><p>
            Run a dev/build as a smoke test to see if all is well.<br /><strong>Code: </strong>FinalDevBuild<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/FinalDevBuild.php">FinalDevBuild</a></p><h3>Step 25 / 25: After load fixes (inspect)</h3><p>
            Runs the silverstripe/upgrade task "inpect". See:
            https://github.com/silverstripe/silverstripe-runActualTask#inspect.
            Once a project has all class names migrated, and is brought up to a
            "loadable" state (that is, where all classes reference or extend real classes)
            then the inspect command can be run to perform additional automatic code rewrites.
            This step will also warn of any upgradable code issues that may prevent a succesful upgrade.<br /><strong>Code: </strong>InspectAPIChanges-2<br /><strong>Class Name: </strong><a href="https://github.com/sunnysideup/silverstripe-upgrade_to_silverstripe_4/tree/master/src/Tasks/IndividualTasks/InspectAPIChanges.php">InspectAPIChanges</a></p>
