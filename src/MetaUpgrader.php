<?php

namespace Sunnysideup\UpgradeToSilverstripe4;
use Sunnysideup\UpgradeToSilverstripe4\Util\PHP2CommandLine;
/**
 * recompose (Mandatory, stop execution on failure)
 */

class MetaUpgrader
{
    /**
     * only instance of me
     * @var MetaUpgrader
     */
    private static $_singleton = null;

    /**
     * only instance of me
     * @return MetaUpgrader
     */
    public static function create()
    {
        if (! self::$_singleton) {
            self::$_singleton = new MetaUpgrader();
        }
        return self::$_singleton;
    }

    /**
     * set a folder location for the upgrade log.
     * if set, a log will be created.
     *
     * @var string
     */
    protected $logFolderLocation = '';

    public function setLogFolderLocation($s)
    {
        $this->logFolderLocation = $s;

        return $this;
    }


    /**
     * The folder for storing the log file in.
     * @param [type] $s [description]
     */
    public function getLogFolderLocation()
    {
        return $this->logFolderLocation;
    }

    /**
     * The file location for storing the update logs.
     * @return [type] [description]
     */
    public function getLogFileLocation(){
        return $this->logFileLocation;
    }

    /**
     * start the upgrade sequence at a particular method
     * @var string
     */
    protected $startFrom = '';

    public function setStartFrom($s)
    {
        $this->startFrom = $s;

        return $this;
    }

    /**
     * end the upgrade sequence after a particular method
     * @var string
     */
    protected $endWith = '';

    public function setEndWith($s)
    {
        $this->endWith = $s;

        return $this;
    }

    /**
     * should the script stop if any error occurs?
     * @var bool
     */
    protected $breakOnAllErrors = false;

    public function setBreakOnAllErrors($b)
    {
        $this->breakOnAllErrors = $b;

        return $this;
    }

    /**
     * name of the branch created to do the upgrade
     * @var string
     */
    protected $nameOfTempBranch = 'temp-upgradeto4-branch';

    public function setNameOfTempBranch($s)
    {
        $this->nameOfTempBranch = $s;

        return $this;
    }


    /**
     * @var string
     */
    protected $aboveWebRootDir = '/var/www';

    public function setAboveWebRootDir($s)
    {
        $this->aboveWebRootDir = $s;

        return $this;
    }

    /**
     * @var string
     */
    protected $webrootDirName = 'upgradeto4';

    public function setWebrootDirName($s)
    {
        $this->webrootDirName = $s;

        return $this;
    }

    /**
     * specified like this:
     *      [
     *          'VendorName' => 'A',
     *          'VendorNamespace' => 'A',
     *          'PackageName' => 'Package1',
     *          'PackageNamespace' => 'Package1',
     *          'GitLink' => 'git@github.com:foor/bar-1.git',
     *          'UpgradeAsFork' => false
     *      ],
     *      [
     *          'VendorName' => 'A',
     *          'VendorNamespace' => 'A',
     *          'PackageName' => 'Package2',
     *          'PackageNameSpace' => 'Package2',
     *          'GitLink' => 'git@github.com:foor/bar-2.git',
     *          'UpgradeAsFork' => false
     *      ],
     *
     * @var array
     */
    protected $arrayOfModules = [];

    public function setArrayOfModules($a)
    {
        $this->arrayOfModules = $a;

        return $this;
    }

    public function addModule($a)
    {
        $this->arrayOfModules[] = $a;

        return $this;
    }

    /**
     * @var null|bool
     */
    protected $runImmediately = null;

    public function setRunImmediately($b)
    {
        $this->runImmediately = $b;

        return $this;
    }

    /**
     *
     * e.g. COMPOSER_HOME="/home/UserName"
     *
     * @var string
     */
    protected $composerEnvironmentVars = '';

    public function setComposerEnvironmentVars($s)
    {
        $this->composerEnvironmentVars = $s;

        return $this;
    }



    /**
     * //e.g. 'upgrade-code'
     * //e.g. '~/.composer/vendor/bin/upgrade-code'
     * //e.g. '/var/www/silverstripe-upgrade_to_silverstripe_4/vendor/silverstripe/upgrader/bin/upgrade-code'
     * @var string
     */
    protected $locationOfUpgradeModule = 'upgrade-code';

    public function setLocationOfUpgradeModule($s)
    {
        $this->locationOfUpgradeModule = $s;

        return $this;
    }

    /**
     * @var bool
     */
    protected $includeEnvironmentFileUpdate = false;

    public function setIncludeEnvironmentFileUpdate($b)
    {
        $this->includeEnvironmentFileUpdate = $b;

        return $this;
    }

    /**
     * @var bool
     */
    protected $includeReorganiseTask = false;

    public function setIncludeReorganiseTask($b)
    {
        $this->includeReorganiseTask = $b;

        return $this;
    }

    /**
     * @var bool
     */
    protected $includeWebrootUpdateTask = false;

    public function setIncludeWebrootUpdateTask($b)
    {
        $this->includeWebrootUpdateTask = $b;

        return $this;
    }


    protected $logFileLocation = '';

    protected $lastMethod = false;

    protected $webrootDir = '';

    protected $moduleDir = '';

    protected $vendorName = '';

    protected $vendorNamespace = '';

    protected $packageName = '';

    protected $packageNamespace = '';

    protected $gitLink = '';

    protected $upgradeAsFork = '';

    //The import the utils
    protected $colourPrinter = null;

    public function run()
    {
        //Init UTIL and helper objects

        $this->startColourPrinter();

        if ($this->runImmediately === null) {
            if ($this->isCommandLine()) {
                $this->runImmediately = true;
            } else {
                $this->runImmediately = false;
            }
        }
        $this->startOutput();
        $this->execMe(
            $this->aboveWebRootDir,
            'echo "===================== START ======================"',
            'show that the task is starting',
            false
        );
        $this->webrootDir = $this->aboveWebRootDir.'/'.$this->webrootDirName;
        foreach ($this->arrayOfModules as $counter => $moduleDetails) {

            $this->loadVarsForModule($moduleDetails);

            ######## #########
            ######## RESET
            ######## #########

            $this->runResetWebRootDir();

            ######## #########
            ######## CHANGE COMPOSER FILE
            ######## #########

            $this->runAddUpgradeBranch();

            $this->runUpdateComposerRequirements('silverstripe/framework', '~4.0');

            $this->runUpdateComposerRequirements('silverstripe/cms', '~4.0');

            $this->runRecompose('MAJOR: upgrading composer requirements to SS4');


            ######## #########
            ######## RESET
            ######## #########

            $this->runResetWebRootDir();

            ######## #########
            ######## UPGRADE
            ######## #########

            $this->runComposerInstallProject();

            $this->runChangeEnvironment();

            $this->runUpperCaseFolderNamesForPSR4();

            $this->runAddNamespace();

            $this->runUpgrade();

            $this->runInspectAPIChanges();

            $this->runReorganise();

            $this->runWebrootUpdate();
        }
        $this->execMe(
            $this->aboveWebRootDir,
            'echo "===================== END ======================"',
            'show that we are finished',
            false
        );
        $this->endOutput();
    }

    /**
     * Starts the logger. Extra checking may be put in here to see if you
     * want to start the logger or not in different scenarios.
     *
     * For now it defaults to always existing
     * @return [type] [description]
     */
    protected function startColourPrinter(){
        $this->commandLineExec = new PHP2CommandLine(
            $this->logFileLocation
        );
    }


    protected function loadVarsForModule($moduleDetails)
    {
        $this->vendorName = $moduleDetails['VendorName'];
        if (isset($moduleDetails['VendorNamespace'])) {
            $this->vendorNamespace = $moduleDetails['VendorNamespace'];
        } else {
            $this->vendorNamespace = $this->camelCase($this->vendorName);
        }
        $this->packageName = $moduleDetails['PackageName'];
        if (isset($moduleDetails['PackageNamespace'])) {
            $this->packageNamespace = $moduleDetails['PackageNamespace'];
        } else {
            $this->packageNamespace = $this->camelCase($this->packageName);
        }
        $this->moduleDir = $this->webrootDir . '/' . $this->packageName;
        if (isset($moduleDetails['GitLink'])) {
            $this->gitLink = $moduleDetails['GitLink'];
        } else {
            $this->gitLink = 'git@github.com:'.$this->vendorName.'/silverstripe-'.$this->packageName;
        }
        if ($this->logFolderLocation) {
            $this->logFileLocation = $this->logFolderLocation.'/'.$this->packageName.'-upgrade-log.'.time().'.txt';
        }
        $this->upgradeAsFork = empty($moduleDetails['UpgradeAsFork']) ? false : true;

        //output the confirmation.
        $this->$colourPrinter->colourPrint('---------------------', 'light_cyan');
        $this->$colourPrinter->colourPrint('UPGRADE DETAILS', 'light_cyan');
        $this->$colourPrinter->colourPrint('---------------------', 'light_cyan');
        $this->$colourPrinter->colourPrint('Vendor Name: '.$this->vendorName, 'light_cyan');
        $this->$colourPrinter->colourPrint('Vendor Namespace: '.$this->vendorNamespace, 'light_cyan');
        $this->$colourPrinter->colourPrint('Package Name: '.$this->packageName, 'light_cyan');
        $this->$colourPrinter->colourPrint('Package Namespace: '.$this->packageNamespace, 'light_cyan');
        $this->$colourPrinter->colourPrint('Module Dir: '.$this->moduleDir, 'light_cyan');
        $this->$colourPrinter->colourPrint('Git Repository Link: '.$this->gitLink, 'light_cyan');
        $this->$colourPrinter->colourPrint('Upgrade as Fork: '.($this->upgradeAsFork ? 'yes' : 'no'), 'light_cyan');
        $this->$colourPrinter->colourPrint('Log File Location: '.($this->logFileLocation ? $this->logFileLocation : 'not logged'), 'light_cyan');
        $this->$colourPrinter->colourPrint('---------------------', 'light_cyan');
    }

    /**
     * resets the upgrade dir
     * the upgrade dir is NOT the module dir
     * it is the parent dir in which everything takes place
     */
    protected function runResetWebRootDir()
    {
        if ($this->startMethod('runResetWebRootDir')) {
            $this->aboveWebRootDir = $this->checkIfPathExistsAndCleanItUp($this->aboveWebRootDir);

            $this->execMe(
                $this->aboveWebRootDir,
                'rm '.$this->webrootDir. ' -rf',
                'remove the upgrade dir: '.$this->webrootDir,
                false
            );

            $this->execMe(
                $this->aboveWebRootDir,
                'mkdir '.$this->webrootDir. '',
                'create upgrade directory: '.$this->webrootDir,
                false
            );
        }
    }


    protected function runAddUpgradeBranch()
    {
        if ($this->startMethod('runAddUpgradeBranch')) {
            $this->webrootDir = $this->checkIfPathExistsAndCleanItUp($this->webrootDir);

            $this->execMe(
                $this->webrootDir,
                'composer require '.$this->vendorName.'/'.$this->packageName.':dev-master',
                'checkout dev-master of '.$this->vendorName.'/'.$this->packageName,
                false
            );

            $this->moduleDir = $this->checkIfPathExistsAndCleanItUp($this->moduleDir);

            $this->execMe(
                $this->moduleDir,
                'if git show-ref --quiet refs/heads/'.$this->nameOfTempBranch.'; then git branch -d '.$this->nameOfTempBranch.'; git push origin --delete '.$this->nameOfTempBranch.'; fi',
                'delete upgrade branch ('.$this->nameOfTempBranch.') locally',
                false
            );

            $this->execMe(
                $this->moduleDir,
                'git push origin --delete '.$this->nameOfTempBranch,
                'delete upgrade branch ('.$this->nameOfTempBranch.') remotely',
                false
            );

            $this->execMe(
                $this->moduleDir,
                'git checkout -b '.$this->nameOfTempBranch,
                'create and checkout new branch: '.$this->nameOfTempBranch,
                false
            );
        }
    }

    protected function runUpdateComposerRequirements($module, $newVersion)
    {
        if ($this->startMethod('runUpdateComposerRequirements')) {
            $location = $this->moduleDir.'/composer.json';

            $this->execMe(
                $this->moduleDir,
                'php -r  \''
                    .'$jsonString = file_get_contents("'.$location.'"); '
                    .'$data = json_decode($jsonString, true); '
                    .'if(isset($data["require"]["'.$module.'"])) { '
                    .'    $data["require"]["'.$module.'"] = "'.$newVersion.'"; '
                    .'}'
                    .'$newJsonString = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); '
                    .'file_put_contents("'.$location.'", $newJsonString); '
                    .'\'',
                'replace in '.$location.' the require for '.$module.' with '.$newVersion,
                false
            );
            $this->commitAndPush('MAJOR: upgrading composer requirements to SS4 - updating core requirements');
        }
    }


    protected function runRecompose()
    {
        if ($this->startMethod('runRecompose')) {
            $this->runSilverstripeUpgradeTask('recompose', $this->moduleDir);
            $this->commitAndPush('MAJOR: upgrading composer requirements to SS4 - STEP 2');
        }
    }

    protected function runComposerInstallProject()
    {
        if ($this->startMethod('runComposerInstallProject')) {
            $this->execMe(
                $this->aboveWebRootDir,
                $this->composerEnvironmentVars.' composer create-project silverstripe/installer '.$this->webrootDir.' ^4',
                'set up vanilla SS4 install',
                false
            );

            $this->webrootDir = $this->checkIfPathExistsAndCleanItUp($this->webrootDir);

            $this->execMe(
                $this->webrootDir,
                'git clone '.$this->gitLink.' '.$this->moduleDir,
                'cloning module - we clone to keep all vcs data (composer does not allow this for branch)',
                false
            );

            $this->execMe(
                $this->moduleDir,
                ' git branch -a ',
                'check branch exists',
                false
            );

            $this->execMe(
                $this->moduleDir,
                'git checkout '.$this->nameOfTempBranch,
                'switch branch',
                false
            );

            $this->execMe(
                $this->moduleDir,
                'git branch ',
                'confirm branch',
                false
            );

            //
            // $this->execMe(
            //     $this->webrootDir,
            //     'composer require '.$this->vendorName.'/'.$this->packageName.':dev-'.$this->nameOfTempBranch.' --prefer-source', //--prefer-source --keep-vcs
            //     'add '.$this->vendorName.'/'.$this->packageName.':dev-'.$this->nameOfTempBranch.' to install',
            //     false
            // );
            //
            // $this->moduleDir = $this->checkIfPathExistsAndCleanItUp($this->moduleDir);
            // $this->execMe(
            //     $this->webrootDir,
            //     'rm '.$this->moduleDir.' -rf',
            //     'we will remove the item again: '.$this->moduleDir.' so that we can reinstall with vcs data.',
            //     false
            // );
            //
            // $this->execMe(
            //     $this->webrootDir,
            //     'composer update --prefer-source',
            //     'lets retrieve the module again to make sure we have the vcs data with it!',
            //     false
            // );
        }
    }

    protected function runChangeEnvironment()
    {
        if ($this->startMethod('runChangeEnvironment')) {
            if ($this->includeEnvironmentFileUpdate) {
                $this->runSilverstripeUpgradeTask('environment');
                $this->commitAndPush('MAJOR: changing environment file(s)');
            }
        }
    }


    protected function runUpperCaseFolderNamesForPSR4()
    {
        if ($this->startMethod('runUpperCaseFolderNamesForPSR4')) {
            if ($this->runImmediately) {
                $codeDir = $this->findCodeDir();
                $di = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($codeDir, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::CHILD_FIRST
                );

                foreach ($di as $name => $fio) {
                    if ($fio->isDir()) {
                        $newName = $fio->getPath() . DIRECTORY_SEPARATOR . $this->camelCase($fio->getFilename());
                        $this->execMe(
                            $this->webrootDir,
                            'mv '.$name.' '.$newName,
                            'renaming code dir form '.str_replace($codeDir, '', $name).' to '.str_replace($codeDir, '', $newName),
                            false
                        );
                        //rename($name, $newname); - first check the output, then remove the comment...
                    }
                }
            }
        }
    }

    protected function runAddNamespace()
    {
        if ($this->startMethod('runAddNamespace')) {
            if ($this->runImmediately) {
                $codeDir = $this->findCodeDir();

                $dirsDone = [];
                $directories = new RecursiveDirectoryIterator($codeDir);
                foreach (new RecursiveIteratorIterator($directories) as $file => $fileObject) {
                    if ($fileObject->getExtension() === 'php') {
                        $dirName = realpath(dirname($file));
                        if (! isset($dirsDone[$dirName])) {
                            $dirsDone[$dirName] = true;
                            $nameSpaceAppendix = str_replace($codeDir, '', $dirName);
                            $nameSpaceAppendix = trim($nameSpaceAppendix, '/');
                            $nameSpaceAppendix = str_replace('/', '\\', $nameSpaceAppendix);
                            $nameSpace = $this->vendorNamespace.'\\'.$this->packageNamespace.'\\'.$nameSpaceAppendix;
                            $nameSpaceArray = explode('\\', $nameSpace);
                            $nameSpaceArrayNew = [];
                            foreach ($nameSpaceArray as $nameSpaceSnippet) {
                                if ($nameSpaceSnippet) {
                                    $nameSpaceArrayNew[] = $this->camelCase($nameSpaceSnippet);
                                }
                            }
                            $nameSpace = implode('\\', $nameSpaceArrayNew);
                            $this->execMe(
                                $codeDir,
                                'php '.$this->locationOfUpgradeModule.' add-namespace "'.$nameSpace.'" '.$dirName.' --root-dir='.$this->webrootDir.' --write -vvv',
                                'adding namespace: '.$nameSpace.' to '.$dirName,
                                false
                            );
                        }
                    }
                }
            } else {
                //@todo: we assume 'code' for now ...
                $codeDir1 = $this->moduleDir . '/code';
                $codeDir2 = $this->moduleDir . '/src';
                foreach ([$codeDir1, $codeDir2] as $codeDir) {
                    $this->execMe(
                        $this->locationOfUpgradeModule,
                        'find '.$codeDir.' -mindepth 1 -maxdepth 2 -type d -exec '.
                            'sh -c '.
                                '\'dir=${1##*/}; '.
                                'php '.$this->locationOfUpgradeModule.' add-namespace "'.$this->vendorNamespace.'\\'.$this->packageNamespace.'\\$dir" "$dir" --write -r -vvv'.
                            '\' _ {} '.
                        '\;',
                        'adding name spaces',
                        false
                    );
                }
            }
            $this->commitAndPush('MAJOR: adding namespaces');
        }
    }


    protected function runUpgrade()
    {
        if ($this->startMethod('runUpgrade')) {
            $codeDir = $this->findCodeDir();
            $this->runSilverstripeUpgradeTask('upgrade', $this->webrootDir, $codeDir);
            $this->commitAndPush('MAJOR: core upgrade to SS4 - STEP 1 (upgrade)');
        }
    }



    protected function runInspectAPIChanges()
    {
        if ($this->startMethod('runInspectAPIChanges')) {
            $codeDir = $this->findCodeDir();
            $this->runSilverstripeUpgradeTask('inspect', $this->webrootDir, $codeDir);
            $this->commitAndPush('MAJOR: core upgrade to SS4 - STEP 2 (inspect)');
        }
    }

    protected function runReorganise()
    {
        if ($this->startMethod('runReorganise')) {
            if ($this->includeReorganiseTask) {
                $this->runSilverstripeUpgradeTask('reorganise');
                $this->commitAndPush('MAJOR: re-organising files');
            }
        }
    }

    protected function runWebrootUpdate()
    {
        if ($this->startMethod('runUpdateWebRoot')) {
            if ($this->includeWebrootUpdateTask) {
                $this->runSilverstripeUpgradeTask('webroot');
                $this->commitAndPush('MAJOR: adding webroot concept');
            }
        }
    }

    ##############################################################################
    ##############################################################################
    ##############################################################################

    protected function commitAndPush($message)
    {
        if ($this->startMethod('commitAndPush')) {
            $this->moduleDir = $this->checkIfPathExistsAndCleanItUp($this->moduleDir);

            $this->execMe(
                $this->moduleDir,
                'git add . -A',
                'git add all',
                false
            );

            $this->execMe(
                $this->moduleDir,
                'git commit . -m "'.$message.'"',
                'commit changes: '.$message,
                false
            );

            $this->execMe(
                $this->moduleDir,
                'git push origin '.$this->nameOfTempBranch,
                'pushing changes to origin on the '.$this->nameOfTempBranch.' branch',
                false
            );
        }
    }

    protected function runSilverstripeUpgradeTask($task, $rootDir = '', $param1 = '', $param2 = '', $settings = '')
    {
        if (! $rootDir) {
            $rootDir = $this->webrootDir;
        }
        $this->execMe(
            $this->webrootDir,
            'php '.$this->locationOfUpgradeModule.' '.$task.' '.$param1.' '.$param2.' --root-dir='.$rootDir.' --write -vvv '.$settings,
            'running php upgrade '.$task.' see: https://github.com/silverstripe/silverstripe-upgrader',
            false
        );
    }



    protected function camelCase($str, array $noStrip = [])
    {
        $str = str_replace('-', ' ', $str);
        $str = str_replace('_', ' ', $str);
        // non-alpha and non-numeric characters become spaces
        $str = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $str);
        $str = trim($str);
        // uppercase the first character of each word
        $str = ucwords($str);
        $str = str_replace(" ", "", $str);

        return $str;
    }

    protected function findCodeDir()
    {
        $codeDir = '';
        if (file_exists($this->moduleDir . '/code')) {
            $codeDir = $this->moduleDir . '/code';
        } elseif (file_exists($this->moduleDir . '/src')) {
            $codeDir = $this->moduleDir . '/src';
        } else {
            user_error('Can not find code dir for '.$this->moduleDir, E_USER_NOTICE);
            return;
        }

        return $codeDir;
    }


    protected function startMethod($name)
    {
        if ($this->lastMethod) {
            $runMe = false;
        } else {
            if ($this->startFrom) {
                if ($name === $this->startFrom) {
                    $this->startFrom = '';
                }
            }
            if ($this->endWith) {
                if ($name === $this->endWith) {
                    $this->lastMethod = true;
                }
            }
            $runMe = $this->startFrom ? false : true;
        }
        $this->colourPrint('# --------------------', 'yellow', 3);
        $this->colourPrint('# '.$name, 'yellow');
        $this->colourPrint('# --------------------', 'yellow');
        if (! $runMe) {
            $this->colourPrint('# skipped', 'light_green');
        }

        //here we call the PHP2CommandLine

        return $runMe;
    }

    protected function execMe($newDir, $command, $comment, $alwaysRun = false)
    {
        return $this->commandLineExec->execMe($newDir, $command, $comment, $alwaysRun);
    }

    protected function colourPrint($mixedVar, $colour, $newLineCount)
    {
        return $this->commandLineExec->colourPrint($mixedVar, $colour, $newLineCount);
    }

}
