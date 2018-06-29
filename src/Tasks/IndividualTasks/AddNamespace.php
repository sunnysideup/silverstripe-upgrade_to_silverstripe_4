<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

class AddNamespace extends Task
{

    public function Description()
    {
        return '
            Places all your code into namespaces (provided by silvertripe/upgrader),
            using the PSR-4 approach (matching folders and namespaces)';
    }


    public function upgrader($params = [])
    {
        if ($this->mu->getRunImmediately()) {
            $codeDir = $this->mu->findCodeDir();
            $baseNameSpace = $this->mu->getVendorNamespace().'\\'.$this->mu->getPackageNamespace();
            $dirsDone = [];
            $directories = new \RecursiveDirectoryIterator($codeDir);
            foreach (new \RecursiveIteratorIterator($directories) as $file => $fileObject) {
                if ($fileObject->getExtension() === 'php') {
                    $dirName = realpath(dirname($file));
                    if (! isset($dirsDone[$dirName])) {
                        $dirsDone[$dirName] = true;
                        $nameSpaceAppendix = str_replace($codeDir, '', $dirName);
                        $nameSpaceAppendix = trim($nameSpaceAppendix, '/');
                        $nameSpaceAppendix = str_replace('/', '\\', $nameSpaceAppendix);
                        $nameSpace = $baseNameSpace.'\\'.$nameSpaceAppendix;
                        $nameSpaceArray = explode('\\', $nameSpace);
                        $nameSpaceArrayNew = [];
                        foreach ($nameSpaceArray as $nameSpaceSnippet) {
                            if ($nameSpaceSnippet) {
                                $nameSpaceArrayNew[] = $this->mu->camelCase($nameSpaceSnippet);
                            }
                        }
                        $nameSpace = implode('\\', $nameSpaceArrayNew);
                        $this->mu->execMe(
                            $codeDir,
                            'php '.$this->mu->getLocationOfUpgradeModule().' add-namespace "'.$nameSpace.'" '.$dirName.' --root-dir='.$this->mu->getWebRootDirLocation().' --write --psr4 -vvv',
                            'adding namespace: '.$nameSpace.' to '.$dirName,
                            false
                        );
                    }
                }
            }
            $this->mu->execMe(
                $codeDir,
                'php '.$this->mu->getLocationOfUpgradeModule().' add-namespace "'.$baseNameSpace.'" '.$this->mu->getModuleDirLocation().' --root-dir='.$this->mu->getWebRootDirLocation().' --write --psr4 -vvv',
                'adding namespace: '.$nameSpace.' to '.$dirName,
                false
            );
        } else {
            //@todo: we assume 'code' for now ...
            $codeDir1 = $this->mu->getModuleDirLocation() . '/code';
            $codeDir2 = $this->mu->getModuleDirLocation() . '/src';
            foreach ([$codeDir1, $codeDir2] as $codeDir) {
                $this->mu->execMe(
                    $this->mu->getLocationOfUpgradeModule(),
                    'find '.$codeDir.' -mindepth 1 -maxdepth 2 -type d -exec '.
                        'sh -c '.
                            '\'dir=${1##*/}; '.
                            'php '.$this->mu->getLocationOfUpgradeModule().' add-namespace "'.$this->mu->getVendorNamespace().'\\'.$this->mu->getPackageNamespace().'\\$dir" "$dir" --write -r -vvv'.
                        '\' _ {} '.
                    '\;',
                    'adding name spaces',
                    false
                );
            }
        }
        $this->setCommitMessage('MAJOR: adding namespaces');
    }
}
