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
        $listOfAutoLoads = [];
        $codeDirs = $this->mu()->findNameSpaceAndCodeDirs();
        foreach($codeDirs as $baseNameSpace => $codeDir) {
            if (file_exists($codeDir)) {
                // $moduleDir = dirname($codeDir);
                $di = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($codeDir, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );

                //For all directories
                foreach ($di as $name => $fio) {
                    if ($fio->isDir()) {
                        //If its a directory then
                        $fullLocation = $fio->getPathname();
                        $shortLocation = str_replace(
                            $codeDir,
                            '',
                            $fullLocation
                        );
                        $this->mu()->colourPrint('found dir: '.$name);
                        $this->mu()->colourPrint('full location: '.$fullLocation);
                        $this->mu()->colourPrint('short location location: '.$shortLocation);
                        if (! in_array($shortLocation, $listOfAutoLoads)) {
                            $nameSpace = rtrim($shortLocation, '.php');
                            $nameSpace = rtrim($nameSpace, '/');
                            $nameSpace = ltrim($nameSpace, '/');
                            $nameSpace = str_replace('/', "\\\\", $nameSpace);
                            $nameSpace .= "\\\\";
                            $nameSpace =
                                str_replace('\\', '\\\\', $baseNameSpace).
                                '\\\\'.
                                $nameSpace;
                            $this->mu()->colourPrint('Adding to Autoload PSR-4: ' . $shortLocation, 'green');
                            $listOfAutoLoads[$nameSpace] = $shortLocation;
                        }
                    }
                }
            } else {
                $this->mu()->colourPrint('Code Folder can not be found: '.$codeDir, 'red');
            }
            if (count($listOfAutoLoads)) {
                $command =
                '
    if(! isset($data["autoload"])) {
        $data["autoload"] = [];
    }
    if(! isset($data["autoload"]["psr-4"])) {
        $data["autoload"]["psr-4"] = [];
    }
                ';
                foreach ($listOfAutoLoads as $key => $value) {
                    $command .= '
    $data["autoload"]["psr-4"]["'.$key.'"] = "'.$value.'";';
                }
                $comment = '
    Adding autoload psr-4 details:
    '.json_encode($listOfAutoLoads, true);

                $this->updateJSONViaCommandLine(
                    $this->mu()->getGitRootDir(),
                    $command,
                    $comment
                );
            } else {
                $this->mu()->colourPrint('No namespaces could be located in: '.$codeDir, 'red');
            }
        }
        $this->setCommitMessage('MAJOR: adding psr-4 autoload');
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
