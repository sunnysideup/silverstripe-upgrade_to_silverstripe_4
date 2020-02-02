<?php

namespace Sunnysideup\UpgradeToSilverstripe4\Interfaces;


interface SessionManagementInterface
{

    public function getSessionFileLocation() : string;

    public function initSession() : SessionManagementInterface;

    public function deleteSession();

    public function getSessionValue(string $key) : string;

    public function getSessionData() : array;

    public function setSessionData(array $session) : SessionManagementInterface;

    public function setSessionValue(string $key, string $value): SessionManagementInterface;
}
