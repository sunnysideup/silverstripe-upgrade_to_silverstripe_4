<?php

//requires https://github.com/squizlabs/PHP_CodeSniffer
//requires https://github.com/PHPCompatibility/PHPCompatibility

namespace Sunnysideup\UpgradeToSilverstripe4\Tasks\IndividualTasks;

use Sunnysideup\UpgradeToSilverstripe4\Tasks\Task;

/**
 * Delete the web root directory to allow for a fresh install.
 */
class PHPCompatabilityCheck extends Task
{
    protected $taskStep = 's00';

    public function getTitle()
    {
        return 'PHP Compatibility Check';
    }

    public function getDescription()
    {
        return 'Outputs a file showing errors prevent code from being compatible with php '.$this->phpVersion;
    }

    protected $phpVersion = '7.2';

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
        foreach ($this->mu()->findNameSpaceAndCodeDirs() as $baseNameSpace => $codeDir) {
            echo 'TO BE COMPLETED - SEE: https://decentproductivity.com/codesniffer-and-phpcompatibility/';
            $logFile = $this->mu()->getLogFolderDirLocation() . '/' . $baseNameSpace . '-php-compatibility-report';
            $this->mu()->execMe(
                $webRoot,
                'phpcs -p ' . $codeDir .
                ' --standard=PHPCompatibility' .
                ' --extensions=php --runtime-set testVersion ' . $this->phpVersion .
                ' --report-full=' . $logFile .
                ' -n',
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
