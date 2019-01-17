<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class CreateClientFolder extends Task
{
    protected $taskStep = 's30';

    public function getTitle()
    {
        return 'Move front-end stuff to a client folder';
    }

    public function getDescription()
    {
        return '
            Takes the javascript, css, and images folders and puts them in a newly created client folder.' ;
    }

    /**
     * [runActualTask description]
     * @param  array  $params not currently used for this task
     * @return [type]         [description]
     */
    public function runActualTask($params = [])
    {
        foreach($this->mu()->getExistingModuleDirLocations() as $moduleDir) {
            $newClientFolder = $moduleDir.'/client/ ';
            $this->mu()->execMe(
                $moduleDir,
                'mkdir -v '.$newClientFolder,
                'creating new client folder '.$newClientFolder,
                false
            );
            $foldersToMoveName = [
                'javascript',
                'js',
                'images',
                'img',
                'css'
            ];
            foreach ($foldersToMoveName as $folderToMoveName) {
                $folderToMove = $moduleDir.'/'.$folderToMoveName.'/ ';
                $this->mu()->execMe(
                    $$moduleDir,
                    'if test -d '.$folderToMove.'; then mv -vn '.$folderToMove.' '.$newClientFolder.'; fi;',
                    'moving '.$folderToMove.' to '.$newClientFolder.' -v is verbose, -n is only if does not exists already.',
                    false
                );
            }
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
