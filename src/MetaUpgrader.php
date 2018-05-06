<?php


class MetaUpgrader {

    private static $_singleton

    public static function create()
    {
        if(! self::$_singleton) {
            self::$_singleton = new MetaUpgrader();
        }

        return self::$_singleton();
    }

    protected $nameOfTempBranch = 'temp-upgradeto4-branch';

    public function setNameOfTempBranch($s)
    {
        $this->nameOfTempBranch = $s;

        return $this;
    }


    protected $vendorName = '';

    public function setVendorName($s)
    {
        $this->vendorName = $s;

        return $this;
    }

    protected $rootDir = '/var/www';

    public function setRootDir($s)
    {
        $this->rootDir = $s;

        return $this;
    }

    protected $upgradeDirName = 'upgradeto4';

    public function setUpgradeDirName($s)
    {
        $this->upgradeDirName = $s;

        return $this;

    }

    protected $arrayOfModules = [];

    public function setArrayOfModules($a)
    {
        $this->arrayOfModules = $a;

        return $this;
    }

    protected $runImmediately = false;

    public function setRunImmediately($b)
    {
        $this->runImmediately = $b;

        return $this;
    }

    function run()
    {
        $this->execMe(
            true,
            'echo "===================== START ======================"'
            ' ###########################'
        );
        $upgradeDir = $this->rootDir.'/'.$this->upgradeDirName;
        foreach($this->arrayOfModules as $moduleFolderName) {
            $moduleName = $this->camelCase($moduleFolderName);
            $moduleFolder = $upgradeDir . '/' . $moduleFolderName;
            $this->execMe(
                true,
                'cd '.$this->rootDir,
                'change to root dir: '.$this->rootDir
            );
            $this->execMe(
                true,
                'rm '.$upgradeDir. ' -rf',
                'remove the temp upgrade dir: '.$upgradeDir
            );
            $this->execMe(
                true,
                'composer create-project silverstripe/installer '.$upgradeDir.' ^4'
                'set up vanilla install of 4.0+ in: '.$upgradeDir
            );
            $this->execMe(
                true,
                'cd '.$upgradeDir,
                'change dir to temp upgrade dir: '.$upgradeDir
            );
            $this->execMe(
                true,
                'composer require '.$this->vendorName.'/'.$moduleName.':dev-master',
                'checkout dev master '
            );
            $this->execMe(
                true,
                'cd '.$moduleFolder,
                'change dir to module folder: '.$moduleFolder
            );
            $this->execMe(
                true,
                'git branch -d '.$this->nameOfTempBranch,
                'delete upgrade branch locally: '.$this->nameOfTempBranch
            );
            $this->execMe(
                true,
                'git push origin --delete '.$this->nameOfTempBranch,
                'delete upgrade branch remotely: '.$this->nameOfTempBranch
            );
            if(file_exists($moduleFolder . '/code')) {
                $codeDir = $moduleFolder . '/code'
            } elseif(file_exists($codeDir = $moduleFolder . '/src')) {
                $codeDir = $moduleFolder . '/src';
            } else {
                user_error('Can not find code dir for '.$moduleFolder, E_USER_NOTICE);
                continue;
            }
            $this->execMe(
                true,
                'git checkout -b '.$this->nameOfTempBranch,
                '(re)create the upgrade branch ...'
            );
            $directories = glob($codeDir , GLOB_ONLYDIR);
            foreach($directories as $dir) {
                $nameSpaceAppendix = str_replace($codeDir, '', $dir);
                $nameSpaceAppendix = str_replace('/', '\\', $nameSpaceAppendix);

                $nameSpace = $this->vendorName.'\\'.$moduleName.'\\'.$nameSpaceAppendix;
                $nameSpaceArray = explode('\\', $nameSpace);
                $nameSpaceArrayNew = [];
                foreach($nameSpaceArray as $nameSpaceSnippet) {
                    if($nameSpaceSnippet) {
                        $nameSpaceArrayNew[] = $this->camelCase($nameSpaceSnippet);
                    }
                }
                $nameSpace = implode('\\', $nameSpaceArrayNew)
                foreach($this->scanDirectory($dir, '.php') as $file) {
                    $this->execMe(
                        true,
                        'php ~/.composer/vendor/bin/upgrade-code add-namespace "'.$nameSpace.'" ./'.$dir.'/.'.$file.'  --write -vvv',
                        'adding name spaces'
                    );
                }
            }
        }
        $this->execMe('echo "===================== END ======================"');
    }

    protected function execMe($alwaysRun, $line, $comment)
    {
        echo $this->newLine();
        echo $this->newLine();
        echo '# '.$comment;
        echo $line;
        if($this->runImmediately || $alwaysRun) {
            exec($line);
        }
    }

    protected function newLine()
    {
        if (php_sapi_name() == "cli") {
            return PHP_EOL;
        } else {
            return '<br />';
        }
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
            if(is_readable($path) {
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

}
