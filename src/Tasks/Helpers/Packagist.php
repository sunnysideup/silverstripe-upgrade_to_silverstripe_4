<?php

declare(strict_types=1);

namespace Sunnysideup\UpgradeSilverstripe\Tasks\Helpers;

use Composer\Semver\Semver;
use RuntimeException;
use InvalidArgumentException;

class ComposerCompatibilityUpdater
{
    private const PACKAGIST_P2_URL_TEMPLATE = 'https://repo.packagist.org/p2/%s.json';

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $packagistCache = [];

    /**
     * 1) Check composer content, return incompatible packages.
     *
     * Rules:
     * - If a package release requires ANY acceptable package, then it must allow the acceptable version.
     * - If a package requires NONE of the acceptable packages (in all its stable releases), it passes.
     *
     * @param array<string, mixed> $composerJson
     * @param array<string, string> $acceptablePackagesToConstraints e.g. ['silverstripe/admin' => '^6.0']
     * @return array<string, array{section: string, current: string, reason: string}>
     */
    public function alreadyCompatible(
        array $composerJson,
        array $acceptablePackagesToConstraints,
        bool $includeDevDependencies
    ): bool {
        $requirements = $this->collectRequirements($composerJson, $includeDevDependencies);

        foreach ($acceptablePackagesToConstraints as $acceptablePackage => $acceptableConstraint) {
            if (!is_string($acceptablePackage) || $acceptablePackage === '' || !is_string($acceptableConstraint) || $acceptableConstraint === '') {
                continue;
            }

            if (!isset($requirements[$acceptablePackage])) {
                continue;
            }

            $currentConstraint = (string) $requirements[$acceptablePackage]['current'];

            // best-effort check: if current constraint already allows the acceptable "probe" version
            $probe = $this->probeVersionFromConstraint($acceptableConstraint);
            if ($probe === null) {
                continue;
            }

            if ($this->constraintAllowsVersion($currentConstraint, $probe)) {
                return true;
            }
        }

        return false;
    }


    /**
     * 2) Check composer content, return incompatible packages.
     *
     * Rules:
     * - If a package release requires ANY acceptable package, then it must allow the acceptable version.
     * - If a package requires NONE of the acceptable packages (in all its stable releases), it passes.
     *
     * @param array<string, mixed> $composerJson
     * @param array<string, string> $acceptablePackagesToConstraints e.g. ['silverstripe/admin' => '^6.0']
     * @return array<string, array{section: string, current: string, reason: string}>
     */
    public function findIncompatiblePackages(
        array $composerJson,
        array $acceptablePackagesToConstraints,
        bool $includeDevDependencies
    ): array {
        $requirements = $this->collectRequirements($composerJson, $includeDevDependencies);

        $bad = [];

        foreach ($requirements as $packageName => $meta) {
            if ($this->isPlatformPackage($packageName)) {
                continue;
            }

            try {
                $status = $this->getPackageCompatibilityStatus($packageName, $acceptablePackagesToConstraints);
            } catch (InvalidArgumentException $e) {
                $bad[$packageName] = [
                    'section' => $meta['section'],
                    'current' => $meta['current'],
                    'reason' => 'invalid-acceptable-constraint: ' . $e->getMessage(),
                ];
                continue;
            }

            if ($status['packagistAvailable'] === false) {
                $bad[$packageName] = [
                    'section' => $meta['section'],
                    'current' => $meta['current'],
                    'reason' => 'packagist-unavailable',
                ];
                continue;
            }

            if ($status['isRelevant'] === false) {
                continue; // package does not depend on any acceptable package => pass
            }

            if ($status['bestCompatibleVersion'] === null) {
                $bad[$packageName] = [
                    'section' => $meta['section'],
                    'current' => $meta['current'],
                    'reason' => 'no-compatible-stable-release',
                ];
            }
        }

        ksort($bad);

        return $bad;
    }

    /**
     * 3) Like (2) but updates composer content and returns it.
     * On fail it throws.
     *
     * Update rules:
     * - If the package is in $acceptablePackagesToConstraints, set it to that constraint.
     * - If package is relevant (depends on any acceptable pkg) set to ^<major>.<minor> of latest compatible stable release.
     * - If package is not relevant, leave as-is.
     *
     * @param array<string, mixed> $composerJson
     * @param array<string, string> $acceptablePackagesToConstraints
     * @return array<string, mixed>
     */
    public function updateComposerJsonOrFail(
        array $composerJson,
        array $acceptablePackagesToConstraints,
        bool $includeDevDependencies
    ): array {
        $bad = $this->findIncompatiblePackages($composerJson, $acceptablePackagesToConstraints, $includeDevDependencies);

        if ($bad !== []) {
            $names = implode(', ', array_keys($bad));
            throw new RuntimeException('Cannot update composer.json: incompatible packages: ' . $names);
        }

        $composerJson = $this->updateSection($composerJson, 'require', $acceptablePackagesToConstraints);

        if ($includeDevDependencies) {
            $composerJson = $this->updateSection($composerJson, 'require-dev', $acceptablePackagesToConstraints);
        }

        return $composerJson;
    }

    /**
     * @param array<string, mixed> $composerJson
     * @param array<string, string> $acceptablePackagesToConstraints
     * @return array<string, mixed>
     */
    private function updateSection(array $composerJson, string $section, array $acceptablePackagesToConstraints): array
    {
        $requirements = $composerJson[$section] ?? null;
        if (!is_array($requirements)) {
            return $composerJson;
        }

        foreach ($requirements as $packageName => $currentConstraint) {
            if (!is_string($packageName)) {
                continue;
            }

            if ($this->isPlatformPackage($packageName)) {
                continue;
            }

            if (isset($acceptablePackagesToConstraints[$packageName])) {
                $requirements[$packageName] = (string) $acceptablePackagesToConstraints[$packageName];
                continue;
            }

            $status = $this->getPackageCompatibilityStatus($packageName, $acceptablePackagesToConstraints);

            if ($status['packagistAvailable'] === false) {
                throw new RuntimeException('Packagist unavailable while updating: ' . $packageName);
            }

            if ($status['isRelevant'] === false) {
                continue; // leave as-is
            }

            $best = $status['bestCompatibleVersion'];
            if ($best === null) {
                throw new RuntimeException('No compatible stable release found for: ' . $packageName);
            }

            $requirements[$packageName] = $this->buildCaretMinorConstraint($best);
        }

        $composerJson[$section] = $requirements;

        return $composerJson;
    }

    /**
     * @param array<string, mixed> $composerJson
     * @return array<string, array{section: string, current: string}>
     */
    private function collectRequirements(array $composerJson, bool $includeDevDependencies): array
    {
        $out = [];

        $require = $composerJson['require'] ?? [];
        if (is_array($require)) {
            foreach ($require as $packageName => $constraint) {
                if (!is_string($packageName)) {
                    continue;
                }
                $out[$packageName] = [
                    'section' => 'require',
                    'current' => (string) $constraint,
                ];
            }
        }

        if ($includeDevDependencies) {
            $requireDev = $composerJson['require-dev'] ?? [];
            if (is_array($requireDev)) {
                foreach ($requireDev as $packageName => $constraint) {
                    if (!is_string($packageName)) {
                        continue;
                    }
                    $out[$packageName] = [
                        'section' => 'require-dev',
                        'current' => (string) $constraint,
                    ];
                }
            }
        }

        return $out;
    }

    public function isPlatformPackage(string $packageName): bool
    {
        return $packageName === 'php'
            || str_starts_with($packageName, 'ext-')
            || str_starts_with($packageName, 'lib-')
            || $packageName === 'composer-plugin-api';
    }

    /**
     * @param array<string, string> $acceptablePackagesToConstraints
     * @return array{packagistAvailable: bool, isRelevant: bool, bestCompatibleVersion: string|null}
     */
    private function getPackageCompatibilityStatus(string $packageName, array $acceptablePackagesToConstraints): array
    {
        $this->assertAcceptableConstraints($acceptablePackagesToConstraints);

        $data = $this->fetchPackagistP2($packageName);
        if ($data === []) {
            return [
                'packagistAvailable' => false,
                'isRelevant' => false,
                'bestCompatibleVersion' => null,
            ];
        }

        $versions = $data['packages'][$packageName] ?? null;
        if (!is_array($versions) || $versions === []) {
            return [
                'packagistAvailable' => true,
                'isRelevant' => false,
                'bestCompatibleVersion' => null,
            ];
        }

        $isRelevant = false;
        $candidates = [];

        foreach ($versions as $versionMeta) {
            if (!is_array($versionMeta)) {
                continue;
            }

            $version = $versionMeta['version'] ?? null;
            if (!is_string($version) || $version === '' || $this->isDevOrPreRelease($version)) {
                continue;
            }

            $requires = $versionMeta['require'] ?? [];
            if (!is_array($requires)) {
                $requires = [];
            }

            $foundAnyAcceptable = false;
            $ok = true;

            foreach ($acceptablePackagesToConstraints as $acceptablePackage => $acceptableConstraint) {
                if (!array_key_exists($acceptablePackage, $requires)) {
                    continue;
                }

                $foundAnyAcceptable = true;
                $isRelevant = true;

                $requiresConstraint = $requires[$acceptablePackage];
                if (!is_string($requiresConstraint) || $requiresConstraint === '') {
                    $ok = false;
                    break;
                }

                $probe = $this->probeVersionFromConstraint($acceptableConstraint);
                if ($probe === null) {
                    throw new InvalidArgumentException('Could not derive probe version from: ' . $acceptablePackage . ':' . $acceptableConstraint);
                }

                if (!$this->constraintAllowsVersion($requiresConstraint, $probe)) {
                    $ok = false;
                    break;
                }
            }

            if ($foundAnyAcceptable && $ok) {
                $candidates[] = ltrim($version, 'v');
            }
        }

        if ($isRelevant === false) {
            return [
                'packagistAvailable' => true,
                'isRelevant' => false,
                'bestCompatibleVersion' => null,
            ];
        }

        if ($candidates === []) {
            return [
                'packagistAvailable' => true,
                'isRelevant' => true,
                'bestCompatibleVersion' => null,
            ];
        }

        $sorted = Semver::rsort($candidates);

        return [
            'packagistAvailable' => true,
            'isRelevant' => true,
            'bestCompatibleVersion' => $sorted[0] ?? null,
        ];
    }

    /**
     * @param array<string, string> $acceptablePackagesToConstraints
     */
    private function assertAcceptableConstraints(array $acceptablePackagesToConstraints): void
    {
        foreach ($acceptablePackagesToConstraints as $package => $constraint) {
            if (!is_string($package) || $package === '' || !is_string($constraint) || $constraint === '') {
                throw new InvalidArgumentException('Acceptable packages must be [package => constraint] with non-empty strings.');
            }
        }
    }

    private function isDevOrPreRelease(string $version): bool
    {
        $lower = strtolower($version);

        if (str_contains($lower, 'dev')) {
            return true;
        }

        return str_contains($version, '-');
    }

    private function constraintAllowsVersion(string $constraint, string $version): bool
    {
        try {
            return Semver::satisfies($version, $constraint);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Turns a constraint like '^6.0' or '~3.0' into a probe version like '6.0.0' or '3.0.0'.
     */
    private function probeVersionFromConstraint(string $constraint): ?string
    {
        if (!preg_match('/(\d+)(?:\.(\d+))?(?:\.(\d+))?/', $constraint, $m)) {
            return null;
        }

        $major = (int) ($m[1] ?? 0);
        $minor = (int) ($m[2] ?? 0);
        $patch = (int) ($m[3] ?? 0);

        if ($major <= 0) {
            return null;
        }

        return $major . '.' . $minor . '.' . $patch;
    }

    /**
     * ^<major>.<minor> (e.g. 6.0.3 => ^6.0)
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

        $context = stream_context_create([
            'http' => [
                'timeout' => 20,
                'header' => "User-Agent: composer-compat-updater\r\n",
            ],
        ]);

        $json = @file_get_contents($url, false, $context);

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
