<?php

//requires https://github.com/squizlabs/PHP_CodeSniffer
//requires https://github.com/PHPCompatibility/PHPCompatibility\
//see: https://decentproductivity.com/codesniffer-and-phpcompatibility/'

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Delete the web root directory to allow for a fresh install.
 */
class PHPCompatabilityCheck extends Task
{
    protected $taskStep = 's00';

    protected $phpVersion = '5.5';

    public function getTitle()
    {
        return 'PHP Compatibility Check';
    }

    public function getDescription()
    {
        return 'Outputs a file showing errors prevent code from being compatible with php ' . $this->phpVersion;
    }

    public function setPhpVersion(string $phpVersion)
    {
        $this->phpVersion = $phpVersion;

        return $this;
    }

    public function runActualTask($params = [])
    {
        $webRoot = $this->mu()->getWebRootDirLocation();

        $this->mu()->execMe(
            $webRoot,
            'composer require --dev squizlabs/php_codesniffer',
            'Adding php code sniffers',
            false
        );
        $this->mu()->execMe(
            $webRoot,
            'composer require --dev phpcompatibility/php-compatibility',
            'Adding php compatability info',
            false
        );
        $this->mu()->execMe(
            $webRoot,
            'composer require --dev phpcompatibility/php-compatibility',
            'Adding php compatability info',
            false
        );
        $this->mu()->execMe(
            $webRoot,
            './vendor/bin/phpcs --config-set installed_paths vendor/phpcompatibility/php-compatibility',
            'Adding php compatability info',
            false
        );
        $this->mu()->execMe(
            $webRoot,
            './vendor/bin/phpcs --config-set colors 1',
            'Adding colour',
            false
        );
        $this->mu()->execMe(
            $webRoot,
            './vendor/bin/phpcs --config-set severity 1',
            'Showing all errors',
            false
        );
        $this->mu()->execMe(
            $webRoot,
            './vendor/bin/phpcs --config-show',
            'Showing all errors',
            false
        );
        foreach ($this->mu()->findNameSpaceAndCodeDirs() as $baseNameSpace => $codeDir) {
            $file = str_replace('\\', '-', $baseNameSpace);
            $this->mu()->execMe(
                $webRoot,
                './vendor/bin/phpcs'.
                ' -p ' . $codeDir .
                ' --standard=PHPCompatibility' .
                ' --extensions=php '.
                ' --runtime-set testVersion ' . $this->phpVersion,

                'Running PHP Compatibility Check in: ' . $this->mu()->getWebRootDirLocation(),
                false
            );
        }
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
