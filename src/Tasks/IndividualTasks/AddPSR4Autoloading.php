<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Fixes the folder name cases in to make them PSR4 compatible
 * e.g.
 * yourmodule/src/model becomes yourmodule/src/Model
 */
class AddPSR4Autoloading extends Task
{
    protected $taskStep = 's50';

    public function getTitle()
    {
        return 'Add PSR-4 Autoloading to the composer file.';
    }

    public function getDescription()
    {
        return '
            Goes through all the folders in the code or src dir and adds them to the composer.json file as autoloader.
            This must run after the folder names have been changed to CamelCase (see: UpperCaseFolderNamesForPSR4).
        ';
    }

    public function runActualTask($params = [])
    {
        //project composer.json
        // - app/src/...
        // - app2/src/...
        // DO FOR BOTH
        //module composor.json
        //  ONLY FOR module
        $baseCommands = '
            if(! isset($data["autoload"])) {
                $data["autoload"] = [];
            }
            if(! isset($data["autoload"]["psr-4"])) {
                $data["autoload"]["psr-4"] = [];
            }
        ';
        $addPage = '';
        if ($this->mu()->getIsModuleUpgrade()) {
            $addPage = '
            if(! isset($data["autoload"]["files"])) {
                $data["autoload"]["files"] = [
                    "app/src/Page.php",
                    "app/src/PageController.php"
                ];
            }';
        }

        $codeDirs = $this->mu()->findNameSpaceAndCodeDirs();
        $webRootLocation = $this->mu()->getWebRootDirLocation();
        $command = $baseCommands . $addPage;
        $comment = 'Adding autoload Page and Page controller details in ' . $webRootLocation . '/composer.json';
        $this->updateJSONViaCommandLine(
            $webRootLocation,
            $command,
            $comment
        );
        foreach ($codeDirs as $baseNameSpace => $codeDir) {
            $location = trim(str_replace($webRootLocation, '', $codeDir), '/') . '/';
            //update webroot composer file
            //location:
            $command = $baseCommands . '
            $data["autoload"]["psr-4"]["' . $this->doubleSlash($baseNameSpace) . '"] = "' . $location . '";';
            $comment = 'Adding autoload psr-4 details in ' .
                $webRootLocation . '/composer.json: ' .
                $baseNameSpace . ' => ' . $location;
            $this->updateJSONViaCommandLine(
                $webRootLocation,
                $command,
                $comment
            );
            if ($this->mu()->getIsModuleUpgrade()) {
                $moduleLocation = dirname($codeDir);
                $location = trim(basename($codeDir), '/') . '/';
                $command = $baseCommands . '
                $data["autoload"]["psr-4"]["' .
                $this->doubleSlash($baseNameSpace) . '"] = "' .
                ltrim($location, '/') . '";';
                $comment = 'Adding autoload psr-4 details in ' .
                    $moduleLocation . '/composer.json: ' .
                    $baseNameSpace . ' => ' . $location;
                $this->updateJSONViaCommandLine(
                    $moduleLocation,
                    $command,
                    $comment
                );
            }
        }
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'composer dumpautoload',
            'run composer dumpautoload',
            false
        );
    }

    protected function hasCommitAndPush()
    {
        return true;
    }

    protected function doubleSlash($str)
    {
        return str_replace('\\', '\\\\', $str);
    }
}
