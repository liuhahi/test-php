<?php

declare(strict_types=1);

namespace App;

/**
 * Reports the running PHP version and whether it predates the PHP2Shell fixes.
 *
 * PHP2Shell: CVE-2026-109618 (OOM corruption RCE) and CVE-2026-109619
 * (stale global state RCE), both CVSS 9.2, fixed on 2026-10-21.
 */
final class VersionInfo
{
    /** First fixed patch release per minor branch. */
    private const FIXED = [
        '8.5' => '8.5.12',
        '8.4' => '8.4.27',
        '8.3' => '8.3.36',
        '8.2' => '8.2.35',
    ];

    public function version(): string
    {
        return PHP_VERSION;
    }

    public function sapi(): string
    {
        return PHP_SAPI;
    }

    /**
     * True when the running version is older than the first fixed release on
     * its branch, or on a branch with no fix available (e.g. 7.x, 5.x).
     */
    public function isVulnerable(): bool
    {
        $branch = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;

        if (!isset(self::FIXED[$branch])) {
            // Branches 8.1 and older receive no PHP2Shell fix at all.
            return PHP_VERSION_ID < 80200;
        }

        return version_compare(PHP_VERSION, self::FIXED[$branch], '<');
    }

    public static function fixedVersionForBranch(): string
    {
        $branch = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;

        return self::FIXED[$branch] ?? '8.2.35 (upgrade to a supported branch)';
    }
}
