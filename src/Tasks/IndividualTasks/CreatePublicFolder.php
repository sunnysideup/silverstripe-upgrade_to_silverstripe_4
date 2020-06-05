<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\FileSystemFixes;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class CreatePublicFolder extends Task
{
    protected $taskStep = 's10';

    protected $publicFolderName = 'public';

    public function getTitle()
    {
        return 'Create a public folder to match SS4 folder structure';
    }

    public function getDescription()
    {
        return '
            For projects only, we create a public folder: ' . $this->mu()->getWebRootDirLocation() . '/public';
    }

    /**
     * [runActualTask description]
     * @param  array  $params not currently used for this task
     */
    public function runActualTask($params = [])
    {
        if ($this->mu()->getIsProjectUpgrade()) {
            $newFolder = $this->mu()->getWebRootDirLocation() . '/' . $this->publicFolderName;
            $fixer = new FileSystemFixes($this->mu());
            $fixer->mkDir($this->mu()->getWebRootDirLocation(), $newFolder);

            $this->mu()->execMe(
                $newFolder,
                'echo \'hello world\' >> test.html',
                'adding public test file',
                false
            );
        }
    }

    protected function hasCommitAndPush()
    {
        return $this->mu()->getIsProjectUpgrade();
    }
}
