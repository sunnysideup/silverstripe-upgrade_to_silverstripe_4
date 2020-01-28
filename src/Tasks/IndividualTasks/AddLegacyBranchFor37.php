<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * This task adds a legacy branch to the git repo of the original to act as a backup/legacy version for
 * holding a version of the module before it was changed
 */
class AddLegacyBranchFor37 extends AddLegacyBranch
{
    protected $taskStep = 's10';

    /**
     * @var string what should the legacy branch be called
     */
    protected $nameOfLegacyBranch = 'pre3-7';

}
