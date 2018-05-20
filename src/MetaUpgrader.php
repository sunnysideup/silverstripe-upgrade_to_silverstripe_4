<?php

/**
 * recompose (Mandatory, stop execution on failure)
upgrade (Mandatory, stop execution on failure)
inspect (Mandatory, stop execution on failure)
reorganise (Optional)
webroot (Optional)
 */

class MetaUpgrader {

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
        if(! self::$_singleton) {
            self::$_singleton = new MetaUpgrader();
        }

        return self::$_singleton;
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
    protected $aboveWebrootDir = '/var/www';

    public function setAboveWebrootDir($s)
    {
        $this->aboveWebrootDir = $s;

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

    function run()
    {
        die('implement "CHANGE DIRECTORY" method');
        if($this->runImmediately === null) {
            if($this->isCommandLine()) {
                $this->runImmediately = true;
            } else {
                $this->runImmediately = false;
            }
        }
        $this->startOutput();
        $this->execMe(
            $this->aboveWebrootDir,
            'echo "===================== START ======================"',
            'show that the task is starting',
            false,
        );
        $this->webrootDir = $this->aboveWebrootDir.'/'.$this->webrootDirName;
        $this->vendorNameSpace = $this->camelCase($this->vendorName);
        if(! $this->vendorNameSpace) {
            user_error('ERROR IN VENDOR NAME SPACE', E_USER_ERROR);
            die('------------');
        }
        foreach($this->arrayOfModules as $moduleDirName) {
            $this->moduleName = $moduleDirName;
            $this->moduleDir = $this->webrootDir . '/' . $moduleDirName;
            $this->moduleNameSpace = $this->camelCase($moduleDirName);
            if(! $this->moduleNameSpace) {
                user_error('ERROR IN VENDOR NAME SPACE', E_USER_ERROR);
                die('------------');
            }
            $this->execMe(
                $this->aboveWebrootDir,
                'echo "______ '.$this->moduleName.' in '.$this->moduleDir . '_____ "',
                'starting new module: '.$this->moduleName.' in '.$this->moduleDir,
                false,
            );

            ######## #########
            ######## RESET
            ######## #########

            $this->runResetwebrootDir();

            $this->runAddUpgradeBranch();

            $this->runUpdateComposerRequirements('silverstripe/framework', '~4.0');

            $this->runUpdateComposerRequirements('silverstripe/cms', '~4.0');

            $this->runCommitAndPush('MAJOR: upgrading composer requirements to SS4');

            ######## #########
            ######## RESET
            ######## #########

            $this->runResetwebrootDir();

            ######## #########
            ######## RESET
            ######## #########

            $this->runComposerInstallProject();

            $this->runChangeEnvironmentFile();

            $this->runAddNameSpace();

        }
        $this->execMe(
            $this->aboveWebrootDir,
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
    protected function runResetwebrootDir()
    {
        $this->startSequence('runResetwebrootDir');

        $this->aboveWebrootDir = $this->checkIfPathExistsAndCleanItUp($this->aboveWebrootDir);

        $this->execMe(
            $this->aboveWebrootDir,
            'rm '.$this->webrootDir. ' -rf',
            'remove the upgrade dir: '.$this->webrootDir,
            false
        );

        $this->execMe(
            $this->aboveWebrootDir,
            'mkdir '.$this->webrootDir. '',
            'create upgrade directory: '.$this->webrootDir,
            false
        );
    }


    protected function runAddUpgradeBranch()
    {
        $this->startSequence('runAddUpgradeBranch');

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
            'git branch -d '.$this->nameOfTempBranch,
            'delete upgrade branch locally: '.$this->nameOfTempBranch,
            false
        );

        $this->execMe(
            $this->moduleDir,
            'git push origin --delete '.$this->nameOfTempBranch,
            'delete upgrade branch remotely: '.$this->nameOfTempBranch,
            false
        );

        $this->execMe(
            $this->moduleDir,
            'git checkout -b '.$this->nameOfTempBranch,
            'create and checkout new branch: '.$this->nameOfTempBranch,
            false
        );
    }


    protected function runCommitAndPush($message)
    {
        $this->startSequence('runCommitAndPush');

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


    protected function runUpdateComposerRequirements($module, $newVersion)
    {
        $this->startSequence('runUpdateComposerRequirements');

        $location = $this->moduleDir.'/composer.json';

        $this->execMe(
            $this->moduleDir,
            'php -r  \''
                .'$jsonString = file_get_contents("'.$location.'"); '
                .'$data = json_decode($jsonString, true); '
                .'if(isset($data["require"]["'.$module.'"])) { '
                .'    $data["require"]["'.$module.'"] = "'.$newVersion.'"; '
                .'}'
                .'$newJsonString = json_encode($data, JSON_PRETTY_PRINT); '
                .'file_put_contents("'.$location.'", $newJsonString); '
                .'\'',
            'replace in '.$location.' the require for '.$module.' with '.$newVersion,
            false
        );
    }

    protected function runComposerInstallProject()
    {
        $this->startSequence('runComposerInstallProject');

        $this->execMe(
            $this->aboveWebrootDir,
            $this->composerEnvironmentVars.' composer create-project silverstripe/installer '.$this->webrootDir.' ^4',
            'set up vanilla install of 4.0+ in: '.$this->webrootDir,
            false
        );

        $this->webrootDir = $this->checkIfPathExistsAndCleanItUp($this->webrootDir);

        $this->execMe(
            $this->webrootDir,
            'composer require '.$this->vendorName.'/'.$this->moduleName.':dev-'.$this->nameOfTempBranch.' ', //--prefer-source --keep-vcs
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
            'composer update',
            'lets retrieve the module again to make sure we have the vcs data with it!',
            false
        );
    }

    protected function runChangeEnvironmentFile()
    {
        if($this->includeEnvironmentFileUpdate) {

            $this->startSequence('runChangeEnvironmentFile');

            $this->execMe(
                $this->webrootDir,
                'php upgrade-code environment --root-dir='.$this->webrootDir.' --write -vvv',
                'lets retrieve the module again to make sure we have the vcs data with it!',
                false
            );
        }

    }
    protected function runAddNameSpace()
    {
        if($this->runImmediately) {
            if(file_exists($this->moduleDir . '/code')) {
                $codeDir = $this->moduleDir . '/code';
            } elseif(file_exists($codeDir = $this->moduleDir . '/src')) {
                $codeDir = $this->moduleDir . '/src';
            } else {
                user_error('Can not find code dir for '.$this->moduleDir, E_USER_NOTICE);
                return;
            }

            $directories = glob($codeDir , GLOB_ONLYDIR);
            foreach($directories as $dir) {
                $nameSpaceAppendix = str_replace($codeDir, '', $dir);
                $nameSpaceAppendix = str_replace('/', '\\', $nameSpaceAppendix);

                $nameSpace = $this->vendorNameSpace.'\\'.$this->moduleNameSpace.'\\'.$nameSpaceAppendix;
                $nameSpaceArray = explode('\\', $nameSpace);
                $nameSpaceArrayNew = [];
                foreach($nameSpaceArray as $nameSpaceSnippet) {
                    if($nameSpaceSnippet) {
                        $nameSpaceArrayNew[] = $this->camelCase($nameSpaceSnippet);
                    }
                }
                $nameSpace = implode('\\', $nameSpaceArrayNew);
                foreach($this->scanDirectory($dir, '.php') as $file) {
                    $this->execMe(
                        false,
                        'php '.$this->locationOfUpgradeModule.' add-namespace "'.$nameSpace.'" ./'.$dir.'/.'.$file.'  --write -vvv',
                        'adding name space: '.$nameSpace.' to ./'.$dir.'/.'.$file
                    );
                }
            }
        } else {
            //@todo: we assume 'code' for now ...
            $codeDir1 = $this->moduleDir . '/code';
            $codeDir2 = $this->moduleDir . '/src';
            foreach([$codeDir1, $codeDir2] as $codeDir) {
                $this->execMe(
                    false,
                    'find '.$codeDir.' -mindepth 1 -maxdepth 2 -type d -exec '.
                        'sh -c '.
                            '\'dir=${1##*/}; '.
                            'php '.$this->locationOfUpgradeModule.' add-namespace "'.$this->vendorNameSpace.'\\'.$this->moduleNameSpace.'\\$dir" "$dir" --write -r -vvv'.
                        '\' _ {} '.
                    '\;',
                    'adding name spaces'
                );
            }
        }

    }












    protected function execMe($command, $comment, $newDir = '', $alwaysRun = false)
    {
        if($newDir) {
            $this->currentDir = $newDir;
        }
        echo $this->newLine();
        echo $this->newLine();
        if ($this->isHTML()) {
            echo '<strong># '.$comment .'</strong><br />';
            if($this->runImmediately || $alwaysRun) {
                //do nothing
            } else {
                echo '<div style="color: transparent">tput setaf 33; echo " _____ : '.addslashes($comment) .'" ____ </div>';
            }
        } else {
            echo '# '.$comment;
            echo $this->newLine();
        }
        echo $command;
        if($this->runImmediately || $alwaysRun) {
            $outcome = exec($command.'  2>&1 ', $error, $return);
            if($return) {
                print_r($error);
                $this->endOutput();
                die('------ STOPPED -----');
            } else {
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
        }
    }

    protected function newLine()
    {
        if ($this->isCommandLine()) {
            return PHP_EOL;
        } else {
            return '<br />';
        }
    }

    protected function startSequence($name)
    {
        echo $this->newLine();
        echo $this->newLine();
        echo $this->newLine();
        echo '# --------------------';
        echo $this->newLine();
        echo '# '.$name;
        echo $this->newLine();
        echo '# --------------------';
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

    protected function scanDirectory($rootDir, $allowext) {
        $list = [];
        $dirContent = scandir($rootDir);
        foreach($dirContent as $key => $content) {
            $path = $rootDir.'/'.$content;
            $ext = substr($content, strrpos($content, '.') + 1);
            if(is_readable($path)) {
                if(is_file($path)) {
                    if(in_array($ext, $allowext)) {
                        $list[] = $path;
                    }
                } elseif(is_dir($path)) {
                    // recursive callback to open new directory
                    // $list = scanDirectories($path,$allowext, $allData);
                }
            }
        }
        return $list;
    }

    protected function checkIfPathExistsAndCleanItUp($path)
    {
        if($this->runImmediately) {
            $path = realpath($path);
            if(! file_exists($path)) {
                die('ERROR! Could not find: '.$path);
            }
        } else {
            $path = str_replace('//', '/', $path);
        }
        return $path;
    }



}
