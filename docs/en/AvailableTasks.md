<h3>Remove and reset Web Root</h3><p>
            Delete the web root directory to allow for a fresh install.</p><h3>Add Legacy Branch</h3><p>
            Creates a legacy branch: 3 so that you
            can keep making bugfixes to older versions.</p><h3>Remove and reset Web Root</h3><p>
            Delete the web root directory to allow for a fresh install.</p><h3>Add Upgrade Branch</h3><p>
            Adds a new branch (temp-upgradeto4-branch) to your
            repository (/)
            that is going to be used for upgrading it.</p><h3>Update composer.json requirements</h3><p>
            Change  to :
            in the composer file of your module.</p><h3>Update composer.json from 3 to 4</h3><p>
            Runs the silverstripe/upgrade task "recompose". See:
            https://github.com/silverstripe/silverstripe-runActualTask#recompose</p><h3>Update composer.json requirements</h3><p>
            Change  to :
            in the composer file of your module.</p><h3>Update composer type to silverstripe-vendormodule </h3><p>
            Replaces the composer type from silverstripe-module to silverstripe-vendormodule in line with SS4 standards.
            This means your module will be installed in the vendor folder after this upgrade.</p><h3>Remove installer-name from composer.json</h3><p>
            Remove installer folder from composer.json file so that package
            installs into vendor folder.</p><h3>Remove and reset Web Root</h3><p>
            Delete the web root directory to allow for a fresh install.</p><h3>Composer Install Silverstripe 4</h3><p>
            Install a basic / standard install of Silverstripe (^4)
            using composer</p><h3>Change Environment File</h3><p>
            Runs the silverstripe/upgrade task "environment". See:
            https://github.com/silverstripe/silverstripe-runActualTask#environment.
            You can use this command to migrate an SilverStripe 3 _ss_environment.php
            file to the .env format used by SilverStripe 4.</p><h3>Move code to src folder</h3><p>
            Move code folder to src folder to match PSR requirements.</p><h3>Search and Replace</h3><p>
            Replaces a bunch of code snippets in preparation of the upgrade.
            Controversial replacements will be replaced with a comment
            next to it so you can review replacements easily.</p><h3>Fix Folder Case</h3><p>
            Change your src/code folders from lowercase to TitleCase - e.g.
            yourmodule/src/model becomes yourmodule/src/Model to match the upgrade
            steps.</p><h3>Name Spaces</h3><p>
            Places all your code into namespaces (provided by silvertripe/runActualTask),
            using the PSR-4 approach (matching folders and namespaces)</p><h3>Update Code</h3><p>
            Runs the silverstripe/upgrade task "upgrade". See:
            Upgrade a variety of stuff (e.g. update reference with namespaces)
            https://github.com/silverstripe/silverstripe-runActualTask#upgrade</p><h3>After load fixes (inspect)</h3><p>
            Runs the silverstripe/upgrade task "inpect". See:
            https://github.com/silverstripe/silverstripe-runActualTask#inspect.
            Once a project has all class names migrated, and is brought up to a
            "loadable" state (that is, where all classes reference or extend real classes)
            then the inspect command can be run to perform additional automatic code rewrites.
            This step will also warn of any upgradable code issues that may prevent a succesful upgrade.</p><h3>move mysite/code folder to app/src</h3><p>
            Runs the silverstripe/upgrade task "reorganise". See:
            https://github.com/silverstripe/silverstripe-runActualTask#reorganise
            You can use this command to reorganise your folder structure to
            conform to the new structure introduced with SilverStripe 4.1.
            Your mysite folder will be renamed to app and your code folder will be rename to src.
            </p><h3>Run dev/build</h3><p>
            Run a dev/build as a smoke test to see if all is well.</p><h3>After load fixes (inspect)</h3><p>
            Runs the silverstripe/upgrade task "inpect". See:
            https://github.com/silverstripe/silverstripe-runActualTask#inspect.
            Once a project has all class names migrated, and is brought up to a
            "loadable" state (that is, where all classes reference or extend real classes)
            then the inspect command can be run to perform additional automatic code rewrites.
            This step will also warn of any upgradable code issues that may prevent a succesful upgrade.</p>