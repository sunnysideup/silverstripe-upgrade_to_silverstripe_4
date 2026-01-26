<?php

namespace Sunnysideup\UpgradeSilverstripe\Api;

use Sunnysideup\UpgradeSilverstripe\Interfaces\SessionManagementInterface;
use Sunnysideup\UpgradeSilverstripe\Traits\Creator;

class SessionManagement implements SessionManagementInterface
{
    use Creator;

    protected $sessionFileLocation = '';

    public static function initSession(string $sessionFileLocation): SessionManagementInterface
    {
        $obj = self::create();
        $obj->setSessionFileLocation($sessionFileLocation);

        return $obj;
    }

    public function setSessionFileLocation($sessionFileLocation): SessionManagementInterface
    {
        $this->sessionFileLocation = $sessionFileLocation;
        $link = $sessionFileLocation;
        $dir = dirname($link);
        if (! file_exists($dir)) {
            user_error('Could not find: ' . $dir);
        }

        return $this;
    }

    public function getSessionFileLocation(): string
    {
        return $this->sessionFileLocation;
    }

    public function deleteSession()
    {
        unlink($this->getSessionFileLocation());
    }

    public function getSessionValue(string $key): string
    {
        $session = $this->getSessionData();
        if (isset($session[$key])) {
            return $session[$key];
        }
        return '';
    }

    public function getSessionData(): array
    {
        if (file_exists($this->getSessionFileLocation())) {
            $data = file_get_contents($this->getSessionFileLocation()) ?? '{}';
            if (! $data) {
                user_error('Could not read from: ' . $this->getSessionFileLocation());
            }

            return json_decode($data, true);
        }
        $this->setSessionData([]);

        return $this->getSessionData();
    }

    public function setSessionData(array $session): SessionManagementInterface
    {
        if (! file_exists($this->getSessionFileLocation())) {
            $session['Started'] = date('Y-m-d h:i ');
        }
        $data = json_encode($session, JSON_PRETTY_PRINT);
        try {
            $file = fopen($this->getSessionFileLocation(), 'w');
            if ($file === false) {
                throw new \RuntimeException('Failed to open file: ' . $this->getSessionFileLocation());
            }
            $writeOutcome = fwrite($file, (string) $data);
            if ($writeOutcome === false) {
                throw new \RuntimeException('Failed to write file: ' . $this->getSessionFileLocation());
            }
            $closeOutcome = fclose($file);
            if ($closeOutcome === false) {
                throw new \RuntimeException('Failed to close file: ' . $this->getSessionFileLocation());
            }
        } catch (\Exception $e) {
            // send error message if you can
            echo 'Caught exception: ' . $e->getMessage() . ' location of file: ' . $this->getSessionFileLocation();
        }

        return $this;
    }

    public function setSessionValue(string $key, string $value): SessionManagementInterface
    {
        $session = $this->getSessionData();
        $session[$key] = trim($value);
        $this->setSessionData($session);

        return $this;
    }
}
