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
        $codeDir = $this->mu()->findCodeDir();
        if ($this->mu()->getRunImmediately() && file_exists($codeDir)) {
            $di = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($codeDir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            //For all directories
            foreach ($di as $name => $fio) {
                if ($fio->isDir()) {
                    //If its a directory then
                    $fullLocation = $fio->getPath();
                    $shortLocation = str_replace(
                        $this->mu()->getModuleDirLocation(),
                        '',
                        $fullLocation
                    );
                    if(! in_array($shortLocation, $listOfAutoLoads)) {
                        $nameSpace = rtrim($nameSpace ,'.php');
                        $nameSpace = rtrim($nameSpace ,'/');
                        $nameSpace = ltrim($nameSpace ,'/');
                        $nameSpace = ltrim($nameSpace ,'code/');
                        $nameSpace = ltrim($nameSpace ,'src/');
                        $nameSpace = ltrim($nameSpace ,'/');
                        $nameSpace = str_replace('/', "\\\\", $nameSpace);
                        $nameSpace .= "\\\\";
                        $this->mu()->colourPrint('Adding to Autoload PSR-4: ' . $shortLocation, 'green');
                        $listOfAutoLoads[$nameSpace] = $shortLocation;
                    }
                }
            }
        }
        if(count($listOfAutoLoads)) {
            $command =
            '
if(! isset($data["autoload"])) {
    $data["autoload"] = [];
}
if(! isset($data["autoload"]["psr-4"])) {
    $data["autoload"]["psr-4"] = [];
}
            ';
            foreach($listOfAutoLoads as $key => $value) {
                $command .= '
$data["autoload"]["psr-4"]["'.$key.'"] = "'.$value.'";';
            }
            $comment = '
Adding autoload psr-4 details:
'.json_encode($listOfAutoLoads, true);

            $this->updateJSONViaCommandLine(
                $this->mu()->getModuleDirLocation(),
                $command,
                $comment
            );
        }
        $this->setCommitMessage('MAJOR: remove composer requirements to SS4 - removing requirements for: '.$this->package);

    }

    protected function hasCommitAndPush()
    {
        return true;
    }

}
