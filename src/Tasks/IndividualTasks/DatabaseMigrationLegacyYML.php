<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\UpgradeSilverstripe\Api\FileSystemFixes;
use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class DatabaseMigrationLegacyYML extends Task
{
    protected $taskStep = 's50';

    public function getTitle()
    {
        return 'Copy legacy data to database migration file';
    }

    public function getDescription()
    {
        return '
            Take the data from .upgrade.yml and move it to _config/legay.yml with a header.';
    }

    /**
     * [runActualTask description]
     * @param  array  $params not currently used for this task
     */
    public function runActualTask($params = []): ?string
    {
        foreach ($this->mu()->getExistingModuleDirLocations() as $moduleDir) {
            $oldFile = $moduleDir . '/.upgrade.yml';
            $newFile = $moduleDir . '/_config/database.legacy.yml';
            $tmpFile = $moduleDir . '/_config/database.legacy.yml.tmp';
            $mvStatement = $newFile . ' > ' . $tmpFile . ' && mv ' . $tmpFile . ' ' . $newFile;
            if (! file_exists($oldFile)) {
                return $oldFile . ' NOT FOUND!!!';
            }
            $dir = dirname($newFile);
            FileSystemFixes::inst($this->mu())
                ->mkDir($dir)
                ->copyFolderOrFile($oldFile, $newFile);

            $this->mu()->execMe(
                $moduleDir,
                'sed \'1d\' ' . $mvStatement,
                'removing the first line and placing into temp file',
                false
            );
            $this->mu()->execMe(
                $moduleDir,
                "sed -i -e 's/^/  /' " . $newFile,
                'adding two additional spaces to the start of each line',
                false
            );
            $this->mu()->execMe(
                $moduleDir,
                'echo \'  classname_value_remapping:\' | cat - ' . $mvStatement,
                'adding `  classname_value_remapping:` to the start of ' . $newFile,
                false
            );
            $this->mu()->execMe(
                $moduleDir,
                'echo \'SilverStripe\ORM\DatabaseAdmin:\' | cat - ' . $mvStatement,
                'adding `SilverStripe\ORM\DatabaseAdmin:` to the start of ' . $newFile,
                false
            );
        }
        return null;
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
