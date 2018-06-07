<?php

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
     * start the upgrade sequence at a particular method
     * @var string
     */
    protected $startFrom = '';

    public function SetStartFrom($s)
    {
        $this->startFrom = $s;

        return $this;
    }

    /**
     * end the upgrade sequence after a particular method
     * @var string
     */
    protected $endWith = '';

    public function SetEndWith($s)
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


    protected $lastMethod = false;

    protected $webrootDir = '';

    protected $moduleDir = '';

    protected $vendorName = '';

    protected $vendorNamespace = '';

    protected $packageName = '';

    protected $packageNamespace = '';

    protected $gitLink = '';

    protected $upgradeAsFork = '';

    public function run()
    {
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
            $this->vendorName = $moduleDetails['VendorName'];
            if(isset($moduleDetails['VendorNamespace'])) {
                $this->vendorNamespace = $moduleDetails['VendorNamespace'];
            } else {
                $this->vendorNamespace = $this->camelCase($this->vendorName);
            }
            $this->packageName = $moduleDetails['PackageName'];
            if(isset($moduleDetails['PackageNamespace'])) {
                $this->packageNamespace = $moduleDetails['PackageNamespace'];
            } else {
                $this->packageNamespace = $this->camelCase($this->packageName);
            }
            $this->moduleDir = $this->webrootDir . '/' . $this->packageName;
            if(isset($moduleDetails['GitLink'])) {
                $this->gitLink = $moduleDetails['GitLink'];
            } else {
                $this->gitLink = 'git@github.com:'.$this->vendorName.'/silverstripe-'.$this->packageName;
            }
            $this->upgradeAsFork = empty($moduleDetails['UpgradeAsFork']) ? false : true;
            $this->colourPrint('---------------------', 'light_cyan');
            $this->colourPrint('UPGRADE DETAILS', 'light_cyan');
            $this->colourPrint('---------------------', 'light_cyan');
            $this->colourPrint('Vendor Name: '.$this->vendorName, 'light_cyan');
            $this->colourPrint('Vendor Namespace: '.$this->vendorNamespace, 'light_cyan');
            $this->colourPrint('Package Name: '.$this->packageName, 'light_cyan');
            $this->colourPrint('Package Namespace: '.$this->packageNamespace, 'light_cyan');
            $this->colourPrint('Module Dir: '.$this->moduleDir, 'light_cyan');
            $this->colourPrint('Git Repository Link: '.$this->gitLink, 'light_cyan');
            $this->colourPrint('Upgrade as Fork: '.($this->upgradeAsFork ? 'yes' : 'no'), 'light_cyan');
            $this->colourPrint('---------------------', 'light_cyan');

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

                foreach($di as $name => $fio) {
                    if($fio->isDir()) {
                        $newName = $fio->getPath() . DIRECTORY_SEPARATOR . $this->camelCase($fio->getFilename() );
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
                        if(! isset($dirsDone[$dirName])) {
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

    protected function execMe($newDir, $command, $comment, $alwaysRun = false)
    {
        $currentDir = $this->checkIfPathExistsAndCleanItUp($newDir);

        //we use && here because this means that the second part only runs
        //if the CD works.
        $command = 'cd '.$currentDir.' && '.$command;
        if ($this->isHTML()) {
            $this->newLine();
            echo '<strong># '.$comment .'</strong><br />';
            if ($this->runImmediately || $alwaysRun) {
                //do nothing
            } else {
                echo '<div style="color: transparent">tput setaf 33; echo " _____ : '.addslashes($comment) .'" ____ </div>';
            }
        } else {
            $this->colourPrint('# '.$comment, 'dark_gray');
        }
        $commandsExploded = explode('&&', $command);
        foreach($commandsExploded as $commandInner) {
            $commandsExplodedInner = explode(';', $commandInner);
            foreach($commandsExplodedInner as $commandInnerInner) {
                $this->colourPrint(trim($commandInnerInner), 'white');
            }
        }
        if ($this->runImmediately || $alwaysRun) {
            $outcome = exec($command.'  2>&1 ', $error, $return);
            if ($return) {
                $this->colourPrint($error, 'red');
                if ($this->breakOnAllErrors) {
                    $this->endOutput();
                    $this->newLine(10);
                    die('------ STOPPED -----');
                    $this->newLine(10);
                }
            } else {
                if($outcome) {
                    $this->colourPrint($outcome, 'green');
                }
                if (is_array($error)) {
                    foreach ($error as $line) {
                        $this->colourPrint($line, 'blue');
                    }
                } else {
                    $this->colourPrint($error, 'blue');
                }
                if($this->isHTML()) {
                    echo ' <i>✔</i>';
                } else {
                    $this->colourPrint(' ✔', 'green', false);
                }
                $this->newLine(2);
            }
        }
        if ($this->isHTML()) {
            ob_flush();
            flush();
        }
    }

    protected function colourPrint($mixedVar, $colour, $newLine = true)
    {
        switch ($colour) {
            case 'black':
                $colour = '0;30';
                break;
            case 'dark_gray':
                $colour = '1;30';
                break;
            case 'blue':
                $colour = '0;34';
                break;
            case 'light_blue':
                $colour = '1;34';
                break;
            case 'green':
                $colour = '0;32';
                break;
            case 'light_green':
                $colour = '1;32';
                break;
            case 'cyan':
                $colour = '0;36';
                break;
            case 'light_cyan':
                $colour = '1;36';
                break;
            case 'red':
                $colour = '0;31';
                break;
            case 'light_red':
                $colour = '1;31';
                break;
            case 'purple':
                $colour = '0;35';
                break;
            case 'light_purple':
                $colour = '1;35';
                break;
            case 'brown':
                $colour = '0;33';
                break;
            case 'yellow':
                $colour = '1;33';
                break;
            case 'light_gray':
                $colour = '0;37';
                break;
            case 'white':
            default:
                $colour = '1;37';
                break;
        }
        $outputString = "\033[" . $colour . "m".print_r($mixedVar, 1)."\033[0m";
        if($newLine) {
            $this->newLine();
        }
        echo $outputString;
    }

    protected function isCommandLine() : bool
    {
        if (php_sapi_name() == "cli") {
            return true;
        } else {
            return false;
        }
    }

    protected function isHTML() : bool
    {
        return $this->isCommandLine() ? false : true;
    }

    protected function startOutput()
    {
        if ($this->isHTML()) {
            // Turn off output buffering
            // ini_set('output_buffering', 'off');
            // // Turn off PHP output compression
            // ini_set('zlib.output_compression', false);
            //
            // //Flush (send) the output buffer and turn off output buffering
            // //ob_end_flush();
            // while (@ob_end_flush());
            //
            // // Implicitly flush the buffer(s)
            // ini_set('implicit_flush', true);
            // ob_implicit_flush(true);
            //
            // //prevent apache from buffering it for deflate/gzip
            // header("Content-type: text/plain");
            // header('Cache-Control: no-cache'); // recommended to prevent caching of event data.

            echo '
            <!DOCTYPE html>
            <html lang="en-US">
            <head>
            <meta charset="UTF-8">
            <title>Title of the document</title>
            </head>

            <body>
                <pre><code class="sh">#!/bin/bash<br />';
            ob_flush();
            flush();
        }
    }



    protected function endOutput()
    {
        if ($this->isHTML()) {
            $dir = dirname(dirname(__FILE__));
            // $css = file_get_contents($dir.'/javascript/styles/default.css');
            // $js = file_get_contents($dir.'/javascript/highlight.pack.js');
            // echo '</code></pre>
            // <script>
            //     '.$js.'
            //     hljs.initHighlightingOnLoad();
            // </script>
            echo '
            <style>
                html, body {padding: 0; margin: 0; min-height: 100%; height: 100%; background-color: #300a24;color: #fff;}
                pre {
                    font-family: Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New, monospace;
                }
                strong {display: block; color: teal;}
                i {color: green; font-style: normal;}
                .hljs-string {color: yellow;}
                .hljs-built_in {color: #ccc;}
            </style>
            </body>
            </html>

            ';
            ob_flush();
            flush();
        } else {
            $this->newLine(3);
        }
    }

    protected function newLine($numberOfLines = 1)
    {
        for ($i = 0; $i < $numberOfLines; $i++) {
            if ($this->isCommandLine()) {
                echo PHP_EOL;
            } else {
                echo '<br />';
            }
        }
    }

    protected function startMethod($name)
    {
        if($this->lastMethod) {
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
        $this->newLine(3);
        $this->colourPrint('# --------------------', 'yellow');
        $this->colourPrint('# '.$name, 'yellow');
        $this->colourPrint('# --------------------', 'yellow');
        if (! $runMe) {
            $this->colourPrint('# skipped', 'light_green');
        }
        return $runMe;
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

    protected function checkIfPathExistsAndCleanItUp($path)
    {
        if ($this->runImmediately) {
            $path = realpath($path);
            if (! file_exists($path)) {
                die('ERROR! Could not find: '.$path);
            }
        } else {
            $path = str_replace('//', '/', $path);
        }
        return $path;
    }
}
