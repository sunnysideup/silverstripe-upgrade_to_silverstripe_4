<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\Composer;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Runs through the source code and adds hidden Silverstripe property and method documentation to classes
 * based on the database array and has many lists
 */
class AddPHPDoc extends Task
{
    protected $taskStep = 's60';

    /**
     * @var string
     */
    protected $composerOptions = '';

    public function getTitle()
    {
        return 'Add PHP Doc Comments to Classes';
    }

    public function getDescription()
    {
        return 'Runs through the source code and adds hidden Silverstripe property and method documentation to classes';
    }

    public function runActualTask($params = [])
    {
        $ideannotatorConfig = "
---
Only:
  environment: dev
---
SilverLeague\IDEAnnotator\DataObjectAnnotator:
    enabled: true
    use_short_name: true
    enabled_modules:
        - app
        ";
        $webRoot = $this->mu()->getWebRootDirLocation();

        Composer::inst($this->mu())->Require(
            'silverleague/ideannotator',
            '3.0.0',
            true,
            $this->composerOptions
        );

        $this->mu()->execMe(
            $webRoot,
            'rm app/_config/ideannotator.yml',
            'Remove existing configuration',
            false
        );

        $this->mu()->execMe(
            $webRoot,
            'echo \'' . $ideannotatorConfig . '\' >> app/_config/ideannotator.yml',
            'Adding IDEAnnotator configuration',
            false
        );

        $this->mu()->execMe(
            $webRoot,
            'vendor/bin/sake dev/tasks/SilverLeague-IDEAnnotator-Tasks-DataObjectAnnotatorTask module=app flush=1',
            'Running IDEAnnotator Task to add PHP documentation',
            false
        );
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
