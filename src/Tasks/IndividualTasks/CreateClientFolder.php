<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class CreateClientFolder extends Task
{
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
        $newClientFolder = $this->mu()->getModuleDirLocation().'/client/ ';
        $this->mu()->execMe(
            $this->mu()->getModuleDirLocation(),
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
        foreach($foldersToMoveName as $folderToMoveName) {
            $folderToMove = $this->mu()->getModuleDirLocation().'/'.$folderToMoveName.'/ ';
            $this->mu()->execMe(
                $this->mu()->getModuleDirLocation(),
                'mv -vn '.$folderToMove.' '.$newClientFolder.'',
                'moving '.$folderToMove.' to '.$newClientFolder.' -v is verbose, -n is only if does not exists already.',
                false
            );
        }
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
