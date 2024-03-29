<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Api\FileFixes;
use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Run a dev/build as a smoke test to see if all is well
 */
class AddDotEnvFile extends Task
{
    protected $taskStep = 's60';

    protected $envContent = [
        'SS_DATABASE_CLASS="MySQLDatabase"',
        'SS_DATABASE_NAME="--DB-NAME--HERE--"',
        'SS_DATABASE_PASSWORD="x"',
        'SS_DATABASE_SERVER="localhost"',
        'SS_DATABASE_USERNAME="root"',
        '',
        'SS_DEFAULT_ADMIN_PASSWORD="x"',
        'SS_DEFAULT_ADMIN_USERNAME="x"',
        '',
        'SS_ENVIRONMENT_TYPE="dev"',
    ];

    public function getTitle()
    {
        return 'Adds a .env file';
    }

    public function getDescription()
    {
        return 'Adds a basic .env file in case that is needed.';
    }

    public function runActualTask($params = []): ?string
    {
        if (! file_exists($this->mu()->getWebRootDirLocation() . '/.env')) {
            foreach ($this->envContent as $line) {
                $line = $this->getEnvtContentAddValues($line);
                $this->mu()->execMe(
                    $this->mu()->getWebRootDirLocation(),
                    'echo \'' . \addcslashes($line, '\'') . '\' >> .env',
                    'adding a line to .env: ' . $line,
                    false
                );
            }
            FileFixes::inst($this->mu())
                ->addLineToFileIfItDoesNotExist(
                    '.gitignore',
                    '.env'
                );
        }
        return null;
    }

    protected function getEnvtContentAddValues(string $string)
    {
        return str_replace(
            '--DB-NAME--HERE--',
            'upgrader' . $this->mu()->getVendorNamespace() . $this->mu()->getPackageNamespace(),
            $string
        );
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
