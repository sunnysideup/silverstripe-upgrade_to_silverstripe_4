<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Interfaces;

interface SessionManagementInterface
{
    public static function initSession(string $sessionFileLocation);

    public function getSessionFileLocation(): string;

    public function deleteSession();

    public function getSessionValue(string $key): string;

    public function getSessionData(): array;

    public function setSessionData(array $session): self;

    public function setSessionValue(string $key, string $value): self;
}
