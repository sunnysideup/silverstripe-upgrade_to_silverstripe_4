<?php

/**
 * prerequisites:
 * create 3.9 branch
 * update composer requirements to ~4.1
 *
 * usage:
 *
 *    $obj = MetaUpgrader();
 *    $obj->setVendorName('SunnySideUp');
 *    $obj->setRootDir('/var/www/');
 *    $obj->setUpgradeDirName('upgradeto4');
 *    $obj->setArrayOfModules(
 *          [
 *              'my_first_module',
 *              'my_second_module'
 *          ]
 *    );
 */

class MetaUpgrader {

    protected $vendorName = '';

    public function setVendorName($v)
    {
        $this->vendorName = $v;
    }

    protected $rootDir = '/var/www';

    public function setRootDir($v)
    {
        $this->rootDir = $v;
    }

    protected $upgradeDirName = 'upgradeto4';

    public function setUpgradeDirName($v)
    {
        $this->upgradeDirName = $v;
    }

    protected $arrayOfModules = [];

    public function setArrayOfModules($a)
    {
        $this->arrayOfModules = $a;
    }

    $upgradeDir = $this->rootDir.'/'.$this->upgradeDirName;
    foreach($this->arrayOfModules as $moduleFolderName) {
        $moduleName = $this->camelCase($moduleFolderName);
        $moduleFolder = $upgradeDir . '/' . $moduleFolderName;
        $this->execMe('cd '.$this->rootDir);
        $this->execMe('rm '.$upgradeDir. ' -rf');
        $this->execMe('composer create-project silverstripe/installer '.$this->rootDir.'/upgradeto4 ^4');
        $this->execMe('cd '.$upgradeDir);
        $this->execMe('composer require '.$this->vendorName.'/'.$moduleName.':dev-master');
        if(file_exists($moduleFolder . '/code')) {
            $codeDir = $moduleFolder . '/code'
        } elseif(file_exists($codeDir = $moduleFolder . '/src')) {
            $codeDir = $moduleFolder . '/src';
        } else {
            user_error('Can not find code dir for '.$moduleFolder, E_USER_NOTICE);
            continue;
        }
        $directories = glob($codeDir , GLOB_ONLYDIR);
        foreach($directories as $dir) {
            $nameSpaceAppendix = str_replace($codeDir, '', $dir);
            $nameSpaceAppendix = str_replace('/', '\\', $nameSpaceAppendix);

            $nameSpace = $this->vendorName.'\\'.$moduleName.'\\'.$nameSpaceAppendix;
            foreach($this->::scan($dir, '.php', true);)
            $this->execMe('php ~/.composer/vendor/bin/upgrade-code add-namespace "'.$nameSpace.'" ./'.$dir.'/  --write -vvv');
        }

    }

    protected function execMe($line)
    {
        exec($line);
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

    protected function scanDirectories($rootDir, $allowext, $allData=array()) {
        $dirContent = scandir($rootDir);
        foreach($dirContent as $key => $content) {
            $path = $rootDir.'/'.$content;
            $ext = substr($content, strrpos($content, '.') + 1);

            if(in_array($ext, $allowext)) {
                if(is_file($path) && is_readable($path)) {
                    $allData[] = $path;
                }elseif(is_dir($path) && is_readable($path)) {
                    // recursive callback to open new directory
                    //$allData = scanDirectories($path, $allData);
                }
            }
        }
        return $allData;
    }

}
