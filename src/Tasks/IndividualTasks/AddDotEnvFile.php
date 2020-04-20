<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Run a dev/build as a smoke test to see if all is well
 */
class AddDotEnvFile extends Task
{
    protected $taskStep = 's60';

    public function getTitle()
    {
        return 'Adds a .env file';
    }

    protected $envContent = [
        'SS_DATABASE_CLASS="MySQLPDODatabase"',
        'SS_DATABASE_NAME="--DB-NAME--HERE--"',
        'SS_DATABASE_PASSWORD="x"',
        'SS_DATABASE_SERVER="localhost"',
        'SS_DATABASE_USERNAME="root',
        '',
        'SS_DEFAULT_ADMIN_PASSWORD="x"',
        'SS_DEFAULT_ADMIN_USERNAME="x"',
        '',
        'SS_ENVIRONMENT_TYPE="dev"',
    ];

    public function getDescription()
    {
        return 'Adds a basic .env file in case that is needed.';
    }

    public function runActualTask($params = [])
    {
        if (! file_exists($this->mu()->getWebRootDirLocation() . '/.env')) {
            foreach($this->envContent as $line) {
                $line = $this->getEnvtContentAddValues($line);
                $this->mu()->execMe(
                    $this->mu()->getWebRootDirLocation(),
                    'echo \''.\addcslashes($line, '\'').'\' >> .env',
                    'adding a line to .env: '.$line,
                    false
                );
            }
            $this->mu()->execMe(
                $this->mu()->getWebRootDirLocation(),
                'echo \'.env\' >> .gitignore',
                'Add .env to .gitignore '.$line,
                false
            );
        }

    }

    protected function getEnvtContentAddValues(string $string)
    {
        $string = str_replace(
            '--DB-NAME--HERE--',
            $this->mu->getVendorNamespace().$this->mu->getPackageNamespace(),
            $string
        );

        return $string;
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
