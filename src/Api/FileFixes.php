<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Api;

use Sunnysideup\UpgradeToSilverstripe4\Traits\HelperInst;

class FileFixes
{
    use HelperInst;

    public function addLineToFileIfItDoesNotExist(string $fileFromRoot, string $line): FileFixes
    {
        $file = $this->mu()->getWebRootDirLocation() . '/' . $fileFromRoot;
        if (! file_exists($file)) {
            user_error('Can not find ' . $file);
        }
        $line = addslashes($line);
        $this->mu()->execMe(
            dirname($fileFromRoot),
            'grep -qxF \'' . $line . '\' ' . $file . ' || echo \'' . $line . '\' >>' . $file,
            'Add line ' . $line . ' to ' . $file,
            false
        );

        return $this;
    }

}
