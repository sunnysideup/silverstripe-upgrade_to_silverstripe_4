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
     * if your script breaks then should
     * @var string
     */
    protected $restartFrom = '';

    public function setRestartFrom($s)
    {
        $this->restartFrom = $s;

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
     * vendor name as set in packagist
     * @var string
     */
    protected $vendorName = '';

    public function setVendorName($s)
    {
        $this->vendorName = $s;

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
     * @var array
     */
    protected $arrayOfModules = [];

    public function setArrayOfModules($a)
    {
        $this->arrayOfModules = $a;

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


    protected $webrootDir = '';

    protected $moduleDir = '';

    protected $codeDir = '';

    protected $currentDir = '';

    protected $moduleName = '';

    protected $vendorNameSpace = '';

    protected $moduleNameSpace = '';

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
        $this->vendorNameSpace = $this->camelCase($this->vendorName);
        if (! $this->vendorNameSpace) {
            user_error('ERROR IN VENDOR NAME SPACE', E_USER_ERROR);
            die('------------');
        }
        foreach ($this->arrayOfModules as $moduleDirName) {
            $this->moduleName = $moduleDirName;
            $this->moduleDir = $this->webrootDir . '/' . $moduleDirName;
            $this->moduleNameSpace = $this->camelCase($moduleDirName);
            if (! $this->moduleNameSpace) {
                user_error('ERROR IN VENDOR NAME SPACE', E_USER_ERROR);
                die('------------');
            }
            $this->execMe(
                $this->aboveWebRootDir,
                'echo "______ '.$this->moduleName.' in '.$this->moduleDir . '_____ "',
                'starting new module: '.$this->moduleName.' in '.$this->moduleDir,
                false
            );

            ######## #########
            ######## RESET
            ######## #########

            $this->runResetWebRootDir();

            $this->runAddUpgradeBranch();

            $this->runUpdateComposerRequirements('silverstripe/framework', '~4.0');

            $this->runUpdateComposerRequirements('silverstripe/cms', '~4.0');

            $this->runCommitAndPush('MAJOR: upgrading composer requirements to SS4 - STEP 1');

            $this->runRecompose('MAJOR: upgrading composer requirements to SS4');


            ######## #########
            ######## RESET
            ######## #########

            $this->runResetWebRootDir();

            ######## #########
            ######## RESET
            ######## #########

            $this->runComposerInstallProject();

            $this->runChangeEnvironment();

            $this->runAddNameSpace();

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
                'composer require '.$this->vendorName.'/'.$this->moduleName.':dev-master',
                'checkout dev-master of '.$this->vendorName.'/'.$this->moduleName,
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


    protected function runCommitAndPush($message)
    {
        if ($this->startMethod('runCommitAndPush')) {
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
                'commit changes in composer.json',
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
        }
    }


    protected function runRecompose()
    {
        if ($this->startMethod('runRecompose')) {
            $this->runSilverstripeUpgradeTask('recompose', $this->moduleDir);
            $this->runCommitAndPush('MAJOR: upgrading composer requirements to SS4 - STEP 2');
        }
    }

    protected function runComposerInstallProject()
    {
        if ($this->startMethod('runComposerInstallProject')) {
            $this->execMe(
                $this->aboveWebRootDir,
                $this->composerEnvironmentVars.' composer create-project silverstripe/installer '.$this->webrootDir.' ^4  --prefer-source',
                'set up vanilla install of 4.0+ in: '.$this->webrootDir,
                false
            );

            $this->webrootDir = $this->checkIfPathExistsAndCleanItUp($this->webrootDir);

            $this->execMe(
                $this->webrootDir,
                'composer require '.$this->vendorName.'/'.$this->moduleName.':dev-'.$this->nameOfTempBranch.' --prefer-source --reinstall', //--prefer-source --keep-vcs
                'add '.$this->vendorName.'/'.$this->moduleName.':dev-'.$this->nameOfTempBranch.' to install',
                false
            );

            $this->moduleDir = $this->checkIfPathExistsAndCleanItUp($this->moduleDir);
            $this->execMe(
                $this->webrootDir,
                'rm '.$this->moduleDir.' -rf',
                'we will remove the item again: '.$this->moduleDir.' so that we can reinstall with vcs data.',
                false
            );

            $this->execMe(
                $this->webrootDir,
                'composer update --prefer-source',
                'lets retrieve the module again to make sure we have the vcs data with it!',
                false
            );
        }
    }

    protected function runChangeEnvironment()
    {
        if ($this->startMethod('runChangeEnvironment')) {
            if ($this->includeEnvironmentFileUpdate) {
                $this->runSilverstripeUpgradeTask('environment');
                $this->runCommitAndPush('MAJOR: changing environment file(s)');
            }
        }
    }


    protected function runAddNameSpace()
    {
        if ($this->startMethod('runAddNameSpace')) {
            if ($this->runImmediately) {
                $codeDir = $this->findCodeDir();

                $directories = new RecursiveDirectoryIterator($codeDir);
                foreach (new RecursiveIteratorIterator($directories) as $file => $fileObject) {
                    if ($fileObject->getExtension() === 'php') {
                        $dirName = realpath(dirname($file));
                        $nameSpaceAppendix = str_replace($codeDir, '', $dirName);
                        $nameSpaceAppendix = trim($nameSpaceAppendix, '/');
                        $nameSpaceAppendix = str_replace('/', '\\', $nameSpaceAppendix);
                        $nameSpace = $this->vendorNameSpace.'\\'.$this->moduleNameSpace.'\\'.$nameSpaceAppendix;
                        $nameSpaceArray = explode('\\', $nameSpace);
                        $nameSpaceArrayNew = [];
                        foreach ($nameSpaceArray as $nameSpaceSnippet) {
                            if ($nameSpaceSnippet) {
                                $nameSpaceArrayNew[] = $this->camelCase($nameSpaceSnippet);
                            }
                        }
                        $nameSpace = implode('\\', $nameSpaceArrayNew);
                        $this->execMe(
                            $dirName,
                            'php '.$this->locationOfUpgradeModule.' add-namespace "'.$nameSpace.'" '.$file.'  --write -vvv',
                            'adding name space: '.$nameSpace.' to '.$file,
                            false
                        );
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
                                'php '.$this->locationOfUpgradeModule.' add-namespace "'.$this->vendorNameSpace.'\\'.$this->moduleNameSpace.'\\$dir" "$dir" --write -r -vvv'.
                            '\' _ {} '.
                        '\;',
                        'adding name spaces',
                        false
                    );
                }
            }
            $this->runCommitAndPush('MAJOR: adding namespaces');
        }
    }


    protected function runUpgrade()
    {
        if ($this->startMethod('runUpgrade')) {
            $codeDir = $this->findCodeDir();
            $this->runSilverstripeUpgradeTask('upgrade', $this->moduleDir, $codeDir);
            $this->runCommitAndPush('MAJOR: core upgrade to SS4 - STEP 1 (upgrade)');
        }
    }



    protected function runInspectAPIChanges()
    {
        if ($this->startMethod('runInspectAPIChanges')) {
            $this->runSilverstripeUpgradeTask('upgrade', $this->moduleDir, $codeDir);
            $this->runCommitAndPush('MAJOR: core upgrade to SS4 - STEP 2 (inspect)');
        }
    }

    protected function runReorganise()
    {
        if ($this->startMethod('runReorganise')) {
            if ($this->includeReorganiseTask) {
                $this->runSilverstripeUpgradeTask('reorganise');
                $this->runCommitAndPush('MAJOR: re-organising files');
            }
        }
    }

    protected function runWebrootUpdate()
    {
        if ($this->startMethod('runUpdateWebRoot')) {
            if ($this->includeWebrootUpdateTask) {
                $this->runSilverstripeUpgradeTask('webroot');
                $this->runCommitAndPush('MAJOR: adding webroot concept');
            }
        }
    }


    ##############################################################################
    ##############################################################################
    ##############################################################################

    protected function runSilverstripeUpgradeTask($task, $dir = '', $param1 = '', $param2 = '', $settings = '')
    {
        if (! $dir) {
            $dir = $this->webrootDir;
        }
        $this->execMe(
            $this->webrootDir,
            'php '.$this->locationOfUpgradeModule.' '.$task.' '.$param1.' '.$param2.' --root-dir='.$dir.' --write -vvv '.$settings,
            'running php upgrade '.$task.' see: https://github.com/silverstripe/silverstripe-upgrader',
            false
        );
    }

    protected function execMe($newDir, $command, $comment, $alwaysRun = false)
    {
        $this->currentDir = $this->checkIfPathExistsAndCleanItUp($newDir);

        //we use && here because this means that the second part only runs
        //if the CD works.
        $command = 'cd '.$this->currentDir.' && '.$command;
        $this->newLine();
        $this->newLine();
        if ($this->isHTML()) {
            echo '<strong># '.$comment .'</strong><br />';
            if ($this->runImmediately || $alwaysRun) {
                //do nothing
            } else {
                echo '<div style="color: transparent">tput setaf 33; echo " _____ : '.addslashes($comment) .'" ____ </div>';
            }
        } else {
            echo '# '.$comment;
            $this->newLine();
        }
        echo $command;
        if ($this->runImmediately || $alwaysRun) {
            $outcome = exec($command.'  2>&1 ', $error, $return);
            if ($return) {
                $this->newLine(3);
                print_r($error);
                $this->newLine(3);
                if ($this->breakOnAllErrors) {
                    $this->endOutput();
                    die('------ STOPPED -----');
                }
            } else {
                $this->newLine(3);
                print_r($outcome);
                if (is_array($error)) {
                    foreach ($error as $line) {
                        echo $line;
                        $this->newLine();
                    }
                }
                $this->newLine(3);
                echo ' <i>[DONE]</i>';
            }
        }
        if ($this->isHTML()) {
            ob_flush();
            flush();
        }
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
            echo PHP_EOL;
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
        if ($this->restartFrom) {
            if ($name === $this->restartFrom) {
                $this->restartFrom = '';
            }
        }
        $runMe = $this->restartFrom ? false : true;
        $this->newLine(3);
        echo '# --------------------';
        $this->newLine();
        echo '# '.$name;
        $this->newLine();
        echo '# --------------------';
        $this->newLine();
        if (! $runMe) {
            echo '  ... skipping ... ';
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
