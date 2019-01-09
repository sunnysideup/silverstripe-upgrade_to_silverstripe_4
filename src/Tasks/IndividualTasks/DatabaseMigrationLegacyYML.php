<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class DatabaseMigrationLegacyYML extends Task
{
    public function getTitle()
    {
        return 'Copy legacy data to database migration file';
    }

    public function getDescription()
    {
        return '
            Take the data from .upgrade.yml and move it to _config/legay.yml with a header.' ;
    }

    /**
     * [runActualTask description]
     * @param  array  $params not currently used for this task
     * @return [type]         [description]
     */
    public function runActualTask($params = [])
    {
        $oldFile = $this->mu()->getModuleDirLocation().'/.upgrade.yml ';
        $newFile = $this->mu()->getModuleDirLocation().'/_config/legacy.yml';
        $tmpFile = $this->mu()->getModuleDirLocation().'/_config/legacy.yml.tmp';
        $this->mu()->execMe(
            $this->mu()->getModuleDirLocation(),
            'if test -f '.$oldFile.'; then cp -vn '.$oldFile.' '.$newFile.'; fi;',
            'moving '.$oldFile.' to '.$newFile.' -v is verbose, -n is only if destination does not exists',
            false
        );
        if(! file_exists($newFile)) {
            die('error!');
        }
        $this->mu()->execMe(
            $this->mu()->getModuleDirLocation(),
            'sed \'1d\' '.$newFile.' > '.$tmpFile,
            'removing the first line and placing into temp file',
            false
        );
        $this->mu()->execMe(
            $this->mu()->getModuleDirLocation(),
            'mv '.$tmpFile.' '.$newFile,
            'moving temp file back to original file',
            false
        );
        $this->mu()->execMe(
            $this->mu()->getModuleDirLocation(),
            'sed -i -e \'s/^/  /\' '.$newFile,
            'adding two additional spaces to the start of each line',
            false
        );
        $this->mu()->execMe(
            $this->mu()->getModuleDirLocation(),
            'echo \'  classname_value_remapping:\' | cat - '.$newFile.' > '.$tmpFile.' && mv '.$tmpFile.' '.$newFile,
            'adding `  classname_value_remapping:` to the start of '.$newFile,
            false
        );
        $this->mu()->execMe(
            $this->mu()->getModuleDirLocation(),
            'echo \'SilverStripe\ORM\DatabaseAdmin:\' | cat - '.$newFile.' > '.$tmpFile.' && mv '.$tmpFile.' '.$newFile,
            'adding `SilverStripe\ORM\DatabaseAdmin:` to the start of '.$newFile,
            false
        );
    }

    protected function hasCommitAndPush()
    {
        return true;
    }
}
