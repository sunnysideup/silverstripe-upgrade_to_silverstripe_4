<?php

//requires https://github.com/squizlabs/PHP_CodeSniffer
//requires https://github.com/PHPCompatibility/PHPCompatibility\
//see: https://decentproductivity.com/codesniffer-and-phpcompatibility/'

namespace Sunnysideup\UpgradeSilverstripe\Tasks\IndividualTasks;

use Sunnysideup\PHP2CommandLine\PHP2CommandLineSingleton;
use Sunnysideup\UpgradeSilverstripe\Tasks\Helpers\Composer;
use Sunnysideup\UpgradeSilverstripe\Tasks\Task;

/**
 * Delete the web root directory to allow for a fresh install.
 */
class LintPHPCompatabilityCheck extends Task
{
    protected $taskStep = 'ANY';

    protected $composerOptions = '';

    protected $phpVersion = '8.4';

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

    public function runActualTask($params = []): ?string
    {
        $webRoot = $this->mu()->getWebRootDirLocation();
        if (PHP2CommandLineSingleton::commandExists('sslint-compat')) {
            foreach ($this->mu()->findNameSpaceAndCodeDirs() as $codeDir) {
                // $file = str_replace('\\', '-', $baseNameSpace);
                $this->mu()->execMe(
                    $webRoot,
                    'sslint-compat ' . $codeDir,
                    'Running PHP Compatibility Check in: ' . $codeDir,
                    false
                );
            }
        } else {
            Composer::inst($this->mu())
                ->RequireDev(
                    'squizlabs/php_codesniffer',
                    '',
                    $this->composerOptions
                )
                ->RequireDev(
                    'phpcompatibility/php-compatibility',
                    '',
                    $this->composerOptions
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
            foreach ($this->mu()->findNameSpaceAndCodeDirs() as $codeDir) {
                // $file = str_replace('\\', '-', $baseNameSpace);
                $this->mu()->execMe(
                    $webRoot,
                    './vendor/bin/phpcs' .
                        ' -p ' . $codeDir .
                        ' --standard=PHPCompatibility' .
                        ' --extensions=php ' .
                        ' --runtime-set testVersion ' . $this->phpVersion,
                    'Running PHP Compatibility Check in: ' . $codeDir,
                    false
                );
            }
            Composer::inst($this->mu())
                ->Remove('squizlabs/php_codesniffer', true)
                ->Remove('phpcompatibility/php-compatibility', true);
        }
        return null;
    }

    protected function hasCommitAndPush()
    {
        return false;
    }
}
