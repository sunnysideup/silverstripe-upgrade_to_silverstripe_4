<?php

declare(strict_types=1);

namespace Sunnysideup\UpgradeSilverstripe\Tasks\Helpers;

class SilverstripeCorePackageInfo
{
    /**
     * @var array
     */
    private const CORE_PACKAGES_SS3 = [
        'silverstripe/admin',
        'silverstripe/asset-admin',
        'silverstripe/assets',
        'silverstripe/campaign-admin',
        'silverstripe/config',
        'silverstripe/cms',
        'silverstripe/errorpage',
        'silverstripe/framework',
        'silverstripe/reports',
        'silverstripe/siteconfig',
        'silverstripe/versioned',
        'silverstripe/versioned-admin',
    ];
    private const CORE_PACKAGES_SS4 = [
        "silverstripe/recipe-cms" =>  "4",
        "silverstripe/recipe-core" =>  "4",
        "silverstripe/framework" =>  "4",
        "silverstripe/cms" =>  "4",
        "silverstripe/reports" =>  "4",
        "silverstripe/siteconfig" =>  "4",
        "silverstripe/assets" =>  "1",
        "silverstripe/config" =>  "1",
        "silverstripe/admin" =>  "1",
        "silverstripe/asset-admin" =>  "1",
        "silverstripe/versioned-admin" =>  "1",
        "silverstripe/errorpage" =>  "1",
        "silverstripe/versioned" =>  "1",
    ];
    private const CORE_PACKAGES_SS5 = [
        "silverstripe/recipe-cms" =>  "5",
        "silverstripe/recipe-core" =>  "5",
        "silverstripe/framework" =>  "5",
        "silverstripe/cms" =>  "5",
        "silverstripe/reports" =>  "5",
        "silverstripe/siteconfig" =>  "5",
        "silverstripe/assets" =>  "2",
        "silverstripe/config" =>  "2",
        "silverstripe/admin" =>  "2",
        "silverstripe/asset-admin" =>  "2",
        "silverstripe/versioned-admin" =>  "2",
        "silverstripe/errorpage" =>  "2",
        "silverstripe/versioned" =>  "2",
    ];
    private const CORE_PACKAGES_SS6 = [
        "silverstripe/recipe-cms" =>  "6",
        "silverstripe/recipe-core" =>  "6",
        "silverstripe/framework" =>  "6",
        "silverstripe/cms" =>  "6",
        "silverstripe/reports" =>  "6",
        "silverstripe/siteconfig" =>  "6",
        "silverstripe/assets" =>  "3",
        "silverstripe/config" =>  "3",
        "silverstripe/admin" =>  "3",
        "silverstripe/asset-admin" =>  "3",
        "silverstripe/versioned-admin" =>  "3",
        "silverstripe/errorpage" =>  "3",
        "silverstripe/versioned" =>  "3",
        "silverstripe/session-manager" =>  "3"
    ];


    /**
     * @return array
     */
    public static function get_core_packages(int $silverstripeVersion = 4): array
    {
        $name = 'CORE_PACKAGES_SS' . (int) $silverstripeVersion;
        if (defined('self::' . $name)) {
            return (array) constant('self::' . $name);
        } else {
            user_error('No core package list for Silverstripe version ' . $silverstripeVersion);
            return [];
        }
    }
}
