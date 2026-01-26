<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Api\FindFiles;
use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * Run a dev/build as a smoke test to see if all is well
 */
class FindYmlExamplesFilesAndChangeExtension extends Task
{
    protected $taskStep = 's30';

    protected $extensions = [
        'example',
        'Example',
        'EXAMPLE',
    ];

    public function getTitle()
    {
        return 'Find YML extension files and change extension temporarily.';
    }

    public function getDescription()
    {
        return 'In our modules we include a lot of .yml.example files - we want to update those as well.';
    }

    public function runActualTask($params = []): ?string
    {
        $files = FindFiles::inst()
            ->setSearchPath($this->mu()->getGitRootDir())
            ->setExtensions($this->extensions)
            ->getFlatFileArray();
        print_r($files);
        return null;
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
