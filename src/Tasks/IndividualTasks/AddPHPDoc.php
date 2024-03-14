<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\FileSystemFixes;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\Composer;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Runs through the source code and adds hidden Silverstripe property and method documentation to classes
 * based on the database array and has many lists
 */
class AddPHPDoc extends Task
{
    public const REPLACER = 'REPLACE_WITH_MODULE_NAME';

    protected $taskStep = 's60';

    /**
     * @var string
     */
    protected $composerOptions = '';

    protected $configFileName = 'ideannotator.yml';

    protected $ideAnnotatorVersion = 'dev-master';

    protected $ideannotatorConfig = <<<yml
---
Name: ideannotator_REPLACE_WITH_MODULE_NAME
Only:
  environment: dev
---
# This is added automatically. Please do not edit.
# This is added automatically. Please do not edit.
# This is added automatically. Please do not edit.
SilverLeague\IDEAnnotator\DataObjectAnnotator:
  enabled: true
  use_short_name: true
  enabled_modules:
yml
. '
    - ' . self::REPLACER;

    public function getTitle()
    {
        return 'Add PHP Doc Comments to Classes';
    }

    public function getDescription()
    {
        return 'Runs through the source code and adds hidden Silverstripe property and method documentation to classes';
    }

    public function runActualTask($params = []): ?string
    {
        // $this->mu()->getWebRootDirLocation();

        Composer::inst($this->mu())
            ->Remove('phpunit/phpunit', true)
            ->RequireDev(
                'silverleague/ideannotator',
                $this->ideAnnotatorVersion,
                $this->composerOptions
            );

        foreach ($this->findModuleNames() as $moduleName) {
            $this->mu()->setBreakOnAllErrors(true);
            $this->updateModuleConfigFile($moduleName);
            $this->mu()->execMe(
                $this->mu()->getWebRootDirLocation(),
                'vendor/bin/sake dev/tasks/SilverLeague-IDEAnnotator-Tasks-DataObjectAnnotatorTask module=' . $moduleName . ' flush=1',
                'Running IDEAnnotator Task to add PHP documentation to ' . $moduleName,
                false
            );
            $this->mu()->setBreakOnAllErrors(false);
        }
        return null;
    }

    protected function updateModuleConfigFile(string $moduleName)
    {
        $moduleLocation = $this->findModuleNameLocation($moduleName);

        $fileLocation = $this->mu()->getWebRootDirLocation() . '/' . $moduleLocation . '/_config/' . $this->configFileName;
        FileSystemFixes::inst($this->mu())
            ->removeDirOrFile($fileLocation);
        $ideannotatorConfigForModule = $this->ideannotatorConfig;
        $ideannotatorConfigForModule = str_replace(self::REPLACER, $moduleName, $ideannotatorConfigForModule);
        $this->mu()->execMe(
            $this->mu()->getWebRootDirLocation(),
            'echo \'' . str_replace('\'', '"', $ideannotatorConfigForModule) . '\' > ' . $fileLocation,
            'Adding IDEAnnotator configuration',
            false
        );
        if (! file_exists($fileLocation)) {
            user_error('Could not locate ' . $fileLocation);
        }
    }

    protected function hasCommitAndPush(): bool
    {
        return true;
    }
}
