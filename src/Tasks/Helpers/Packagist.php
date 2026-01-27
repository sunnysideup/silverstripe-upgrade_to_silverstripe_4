<?php

declare(strict_types=1);

use Composer\Semver\Semver;

final class SilverstripeSixComposerUpdater
{

    protected array $alignForPackages = [
        'silverstripe/framework',
        'silverstripe/cms',
        'silverstripe/admin',
        'silverstripe/asset-admin',
        'silverstripe/versioned',
        'silverstripe/config',
        'silverstripe/recipe-core',
        'silverstripe/recipe-cms',
    ];

    protected int $versionForPackagesToAlign = 6;

    /**
     * Packagist “p2” endpoint (tagged releases only).
     */
    private const PACKAGIST_P2_URL_TEMPLATE = 'https://repo.packagist.org/p2/%s.json';

    /**
     * Cache by package name.
     *
     * @var array<string, array<string, mixed>>
     */
    private array $packagistCache = [];

    /**
     * Update require + require-dev constraints to the latest compatible release
     * (only for packages that explicitly depend on Silverstripe).
     *
     * @param array<string, mixed> $composerJson
     * @return array<string, mixed>
     */
    public function updateComposerJsonToAlignWithNewVersion(array $composerJson, array $alignForPackages, int $newVersion): array
    {
        $this->alignForPackages = $alignForPackages;
        $this->versionForPackagesToAlign = (int) $newVersion;
        $composerJson = $this->updateRequirementsSection($composerJson, 'require');
        $composerJson = $this->updateRequirementsSection($composerJson, 'require-dev');

        return $composerJson;
    }

    /**
     * @param array<string, mixed> $composerJson
     * @return array<string, mixed>
     */
    private function updateRequirementsSection(array $composerJson, string $sectionKey): array
    {
        $requirements = $composerJson[$sectionKey] ?? null;
        if (!is_array($requirements)) {
            return $composerJson;
        }

        foreach ($requirements as $packageName => $currentConstraint) {
            $requirements[$packageName] = $this->updateRequirementConstraint($packageName, (string) $currentConstraint);
        }

        $composerJson[$sectionKey] = $requirements;

        return $composerJson;
    }

    public function updateRequirementConstraint(string $packageName, string $currentConstraint): ?string
    {
        if (!is_string($packageName) || !is_string($currentConstraint)) {
            return null;
        }

        if ($this->isPlatformPackage($packageName)) {
            return null;
        }

        $bestVersion = $this->findLatestCompatibleVersion($packageName);
        if ($bestVersion === null) {
            return null;
        }

        return $this->buildCaretMinorConstraint($bestVersion);
    }

    public function isPlatformPackage(string $packageName): bool
    {
        return $packageName === 'php'
            || str_starts_with($packageName, 'ext-')
            || str_starts_with($packageName, 'lib-')
            || $packageName === 'composer-plugin-api';
    }

    public function findLatestCompatibleVersion(string $packageName): ?string
    {
        $packageData = $this->fetchPackagistP2($packageName);
        $versions = $packageData['packages'][$packageName] ?? null;

        if (!is_array($versions) || $versions === []) {
            return null;
        }

        $candidates = [];

        foreach ($versions as $versionMeta) {
            if (!is_array($versionMeta)) {
                continue;
            }

            $version = $versionMeta['version'] ?? null;
            if (!is_string($version) || $version === '') {
                continue;
            }

            if ($this->isDevOrPreRelease($version)) {
                continue;
            }

            $requires = $versionMeta['require'] ?? [];
            if (!is_array($requires)) {
                $requires = [];
            }

            if (!$this->requiresAllowVersion($requires)) {
                continue;
            }

            $candidates[] = ltrim($version, 'v');
        }

        if ($candidates === []) {
            return null;
        }

        $sorted = Semver::rsort($candidates);

        return $sorted[0] ?? null;
    }

    public function isDevOrPreRelease(string $version): bool
    {
        $lower = strtolower($version);

        if (str_contains($lower, 'dev')) {
            return true;
        }

        // crude but effective: 1.2.3-beta1 / 1.2.3-rc.1 etc
        return str_contains($version, '-');
    }

    /**
     * Only update packages that declare a Silverstripe dependency.
     * For those, require constraints must allow SS 6.0.0.
     *
     * @param array<string, mixed> $requires
     */
    public function requiresAllowVersion(array $requires): bool
    {

        $foundAnySilverstripeDependency = false;

        foreach ($this->alignForPackages as $key) {
            if (!array_key_exists($key, $requires)) {
                continue;
            }

            $foundAnySilverstripeDependency = true;

            $constraint = $requires[$key];
            if (!is_string($constraint) || $constraint === '') {
                return false;
            }

            if (!$this->constraintAllowsVersion($constraint, $this->versionForPackagesToAlign . '.0.0')) {
                return false;
            }
        }

        return $foundAnySilverstripeDependency;
    }

    public function constraintAllowsVersion(string $constraint, string $version): bool
    {
        $v = $this->versionForPackagesToAlign;
        try {
            return Semver::satisfies($version, $constraint);
        } catch (Throwable) {
            // fallback for odd constraints
            $c = strtolower($constraint);
            //todo: also allow for ^1,^2,^3,^4,^5, etc..
            return str_contains($c, '^' . $v)
                || str_contains($c, '~' . $v)
                || str_contains($c, $v . '.*')
                || str_contains($c, $v . '.x')
                || str_contains($c, '>=' . $v);
        }
    }

    /**
     * ^<major>.<minor> (e.g. 6.0.3 => ^6.0, 4.2.1 => ^4.2)
     */
    private function buildCaretMinorConstraint(string $version): string
    {
        $version = ltrim($version, 'v');
        $parts = explode('.', $version);

        $major = (int) ($parts[0] ?? 0);
        $minor = (int) ($parts[1] ?? 0);

        return '^' . $major . '.' . $minor;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchPackagistP2(string $packageName): array
    {
        if (isset($this->packagistCache[$packageName])) {
            return $this->packagistCache[$packageName];
        }

        $url = sprintf(self::PACKAGIST_P2_URL_TEMPLATE, $packageName);
        $json = @file_get_contents($url);

        if (!is_string($json) || $json === '') {
            $this->packagistCache[$packageName] = [];

            return [];
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            $this->packagistCache[$packageName] = [];

            return [];
        }

        $this->packagistCache[$packageName] = $data;

        return $data;
    }
}
