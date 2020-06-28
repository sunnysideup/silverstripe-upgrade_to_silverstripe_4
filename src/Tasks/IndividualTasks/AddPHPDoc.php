<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\Composer;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Helpers\ComposerJsonFixes;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;
use Sunnysideup\UpgradeToSilverstripe4\Api\FileSystemFixes;

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
    - REPLACE_WITH_MODULE_NAME

yml;

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
        $this->mu()->getWebRootDirLocation();

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
        Composer::inst($this->mu())
            ->Remove('silverleague/ideannotator', true);
    }

    protected function updateComposerFile(string $moduleName)
    {
        if ($this->mu()->getIsModuleUpgrade()) {
            $moduleLocation = $this->findModuleNameLocation($moduleName);
            $json = ComposerJsonFixes::inst($this->mu())
                ->getJSON($moduleLocation);
            if (! isset($json['require-dev'])) {
                $json['require-dev'] = [];
            }
            $json['require-dev']['silverleague/ideannotator'] = $this->ideAnnotatorVersion;
            $json = ComposerJsonFixes::inst($this->mu())
                ->setJSON($moduleLocation, $json);
        }
    }

    protected function updateModuleConfigFile(string $moduleName)
    {
        $moduleLocation = $this->findModuleNameLocation($moduleName);

        $fileLocation = $this->mu()->getWebRootDirLocation() . '/' . $moduleLocation . '/_config/' . $this->configFileName;
        FileSystemFixes::inst($this->mu())
            ->removeDirOrFile($fileLocation);
        $ideannotatorConfigForModule = $this->ideannotatorConfig;
        $ideannotatorConfigForModule = str_replace('REPLACE_WITH_MODULE_NAME', $moduleName, $ideannotatorConfigForModule);
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

    protected function findModuleNames(): array
    {
        $moduleNames = [];
        if ($this->mu()->getIsModuleUpgrade()) {
            $moduleNames = [
                $this->mu()->getVendorName() . '/' . $this->mu()->getPackageName(),
            ];
        } else {
            foreach ($this->mu()->getExistingModuleDirLocations() as $location) {
                $moduleNames[] = $location;
            }
        }
        return $moduleNames;
    }

    protected function findModuleNameLocation(string $moduleName): string
    {
        if (strpos($moduleName, '/')) {
            $moduleNameLocation = 'vendor/' . $moduleName;
        } else {
            $moduleNameLocation = $moduleName;
        }

        return $moduleNameLocation;
    }

    protected function hasCommitAndPush(): bool
    {
        return true;
    }
}
