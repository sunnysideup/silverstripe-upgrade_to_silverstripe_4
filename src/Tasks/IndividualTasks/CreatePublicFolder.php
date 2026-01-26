<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Api\FileSystemFixes;
use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

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
    public function runActualTask($params = []): ?string
    {
        if ($this->mu()->getIsProjectUpgrade()) {
            $newFolder = $this->mu()->getWebRootDirLocation() . '/' . $this->publicFolderName;
            FileSystemFixes::inst($this->mu())
                ->mkDir($newFolder);

            $this->mu()->execMe(
                $newFolder,
                'echo \'hello world\' >> hello-world.html',
                'adding public test file',
                false
            );
        }
        return null;
    }

    protected function hasCommitAndPush()
    {
        return $this->mu()->getIsProjectUpgrade();
    }
}
