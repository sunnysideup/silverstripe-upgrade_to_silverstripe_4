<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Places all your code into namespaces (provided by silvertripe/runActualTask),
 * using the PSR-4 approach (matching folders and namespaces)
 */
class AddNamespace extends Task
{
    protected $taskStep = 's40';

    public function getTitle()
    {
        return 'Name Spaces';
    }

    public function getDescription()
    {
        return '
            Places all your code into namespaces (provided by silvertripe/runActualTask),
            using the PSR-4 approach (matching folders and namespaces).';
    }


    public function runActualTask($params = [])
    {
        $codeDirs = $this->mu()->findNameSpaceAndCodeDirs();
        $dirsDone = [];
        foreach($codeDirs as $baseNameSpace => $codeDir){
            $directories = new \RecursiveDirectoryIterator($codeDir);
            foreach (new \RecursiveIteratorIterator($directories) as $file => $fileObject) {
                if ($fileObject->getExtension() === 'php') {
                    $dirName = realpath(dirname($file));
                    if (! isset($dirsDone[$dirName])) {
                        $dirsDone[$dirName] = true;
                        $nameSpaceAppendix = str_replace($codeDir, '', $dirName);
                        $nameSpaceAppendix = trim($nameSpaceAppendix, '/');
                        $nameSpaceAppendix = str_replace('/', '\\', $nameSpaceAppendix);
                        //prepend $baseNameSpace
                        $nameSpace = $baseNameSpace.'\\'.$nameSpaceAppendix;
                        //turn into array
                        $nameSpaceArray = explode('\\', $nameSpace);
                        $nameSpaceArrayNew = [];
                        foreach ($nameSpaceArray as $nameSpaceSnippet) {
                            if ($nameSpaceSnippet) {
                                $nameSpaceArrayNew[] = $this->mu()->camelCase($nameSpaceSnippet);
                            }
                        }
                        $nameSpace = implode('\\', $nameSpaceArrayNew);
                        $this->mu()->execMe(
                            $codeDir,
                            'php '.$this->mu()->getLocationOfSSUpgradeModule().' add-namespace "'.$nameSpace.'" '.$dirName.' --root-dir='.$this->mu()->getWebRootDirLocation().' --write --psr4 -vvv',
                            'adding namespace: '.$nameSpace.' to '.$dirName,
                            false
                        );
                    }
                }
            }
            // } else {
            //     //@todo: we assume 'code' for now ...
            //     $codeDirs = $this->mu()->findNameSpaceAndCodeDirs();
            //     foreach ($codeDirs as $codeDir) {
            //         $this->mu()->execMe(
            //             $this->mu()->getLocationOfSSUpgradeModule(),
            //             'find '.$codeDir.' -mindepth 1 -maxdepth 2 -type d -exec '.
            //                 'sh -c '.
            //                     '\'dir=${1##*/}; '.
            //                     'php '.$this->mu()->getLocationOfSSUpgradeModule().' add-namespace "'.$this->mu()->getVendorNamespace().'\\'.$this->mu()->getPackageNamespace().'\\$dir" "$dir" --write --psr4 -vvv'.
            //                 '\' _ {} '.
            //             '\;',
            //             'adding name spaces',
            //             false
            //         );
            //     }
            // }
            $this->mu()->execMe(
                $codeDir,
                'php '.$this->mu()->getLocationOfSSUpgradeModule().' add-namespace "'.$baseNameSpace.'\" '.$codeDir.' --root-dir='.$this->mu()->getWebRootDirLocation().' --write --psr4 -vvv',
                'adding namespace: '.$baseNameSpace.' to '.$codeDir,
                false
            );
            $this->setCommitMessage('MAJOR: adding namespaces');
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
