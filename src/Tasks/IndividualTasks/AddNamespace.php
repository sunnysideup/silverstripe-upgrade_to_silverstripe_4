<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\MetaUpgraderTask;

class AddNamespace extends MetaUpgraderTask
{
    public function upgrader($params = [])
    {
        if ($this->mo->getRunImmediately()) {
            $codeDir = $this->mo->findCodeDir();

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
                        $nameSpace = $this->mo->getVendorNamespace().'\\'.$this->mo->getPackageNamespace().'\\'.$nameSpaceAppendix;
                        $nameSpaceArray = explode('\\', $nameSpace);
                        $nameSpaceArrayNew = [];
                        foreach ($nameSpaceArray as $nameSpaceSnippet) {
                            if ($nameSpaceSnippet) {
                                $nameSpaceArrayNew[] = $this->mo->camelCase($nameSpaceSnippet);
                            }
                        }
                        $nameSpace = implode('\\', $nameSpaceArrayNew);
                        $this->mo->execMe(
                            $codeDir,
                            'php '.$this->mo->getLocationOfUpgradeModule().' add-namespace "'.$nameSpace.'" '.$dirName.' --root-dir='.$this->mo->getWebRootDirLocation().' --write -vvv',
                            'adding namespace: '.$nameSpace.' to '.$dirName,
                            false
                        );
                    }
                }
            }
        } else {
            //@todo: we assume 'code' for now ...
            $codeDir1 = $this->mo->getModuleDirLocation() . '/code';
            $codeDir2 = $this->mo->getModuleDirLocation() . '/src';
            foreach ([$codeDir1, $codeDir2] as $codeDir) {
                $this->mo->execMe(
                    $this->mo->getLocationOfUpgradeModule(),
                    'find '.$codeDir.' -mindepth 1 -maxdepth 2 -type d -exec '.
                        'sh -c '.
                            '\'dir=${1##*/}; '.
                            'php '.$this->mo->getLocationOfUpgradeModule().' add-namespace "'.$this->mo->getVendorNamespace().'\\'.$this->mo->getPackageNamespace().'\\$dir" "$dir" --write -r -vvv'.
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