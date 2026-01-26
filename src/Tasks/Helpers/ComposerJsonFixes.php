<?php

namespace Sunnysideup\UpgradeSilverstripe\Tasks\Helpers;

use Sunnysideup\UpgradeSilverstripe\Traits\HelperInst;

class ComposerJsonFixes
{
    use HelperInst;

    public function getJSON(string $dir): array
    {
        $location = $dir . '/composer.json';
        $jsonString = file_get_contents($location);

        return json_decode($jsonString, true);
    }

    public function setJSON(string $dir, array $data)
    {
        $location = $dir . '/composer.json';
        $newJsonString = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($location, $newJsonString);
    }

    public function UpdateJSONViaCommandLine(string $dir, string $code, string $comment)
    {
        $location = $dir . '/composer.json';
        $this->mu()->execMe(
            $dir,
            'php -r  \''
                . '$jsonString = file_get_contents("' . $location . '"); '
                . '$data = json_decode($jsonString, true); '
                . $code
                . '$newJsonString = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); '
                . 'file_put_contents("' . $location . '", $newJsonString); '
                . '\'',
            $comment . ' --- in ' . $location,
            false
        );
    }
}
