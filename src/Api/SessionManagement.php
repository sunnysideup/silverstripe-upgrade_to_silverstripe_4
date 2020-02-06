<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Api;

use Sunnysideup\UpgradeToSilverstripe4\Interfaces\SessionManagementInterface;

class SessionManagement implements SessionManagementInterface
{
    /**
     * @var string
     */
    protected $sessionFileName = 'Session_For';

    public function initSession()
    {
        if (! file_exists($this->getSessionFileLocation())) {
            $this->setSessionData(['Started' => date('Y-m-d h:i ')]);
        }

        return $this;
    }

    public function getSessionFileLocation(): string
    {
        return trim(
            $this->getAboveWebRootDirLocation() .
            '/' .
            $this->sessionFileName .
            '_' .
            $this->getVendorNamespace() .
            '_' .
            $this->getPackageNamespace() .
            '.json'
        );
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
        $this->initSession();
        $data = file_get_contents($this->getSessionFileLocation());
        if (! $data) {
            user_error('Could not read from: ' . $this->getSessionFileLocation());
        }
        return json_decode($data, true);
    }

    /**
     * @param array $session
     */
    public function setSessionData(array $session): SessionManagementInterface
    {
        $data = json_encode($session, JSON_PRETTY_PRINT);
        try {
            $file = fopen($this->getSessionFileLocation(), 'w');
            if ($file === false) {
                throw new \RuntimeException('Failed to open file: ' . $this->getSessionFileLocation());
            }
            $writeOutcome = fwrite($file, $data);
            if ($writeOutcome === false) {
                throw new \RuntimeException('Failed to write file: ' . $this->getSessionFileLocation());
            }
            $closeOutcome = fclose($file);
            if ($closeOutcome === false) {
                throw new \RuntimeException('Failed to close file: ' . $this->getSessionFileLocation());
            }
        } catch (\Exception $e) {
            // send error message if you can
            echo 'Caught exception: ' . $e->getMessage();
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
