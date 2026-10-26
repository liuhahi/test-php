<?php

declare(strict_types=1);

require __DIR__ . '/../src/VersionInfo.php';

use App\VersionInfo;

header('Content-Type: text/plain; charset=utf-8');

$info = new VersionInfo();

echo "PHP2Shell lab environment\n";
echo "=========================\n\n";
echo "Running PHP version : " . $info->version() . "\n";
echo "SAPI                : " . $info->sapi() . "\n";
echo "Vulnerable to CVE-2026-109618 / CVE-2026-109619 : "
    . ($info->isVulnerable() ? "YES" : "no (patched)") . "\n\n";

if ($info->isVulnerable()) {
    echo "This build is pinned to a KNOWN-VULNERABLE PHP release on purpose.\n";
    echo "Rebuild against a fixed release (>= " . VersionInfo::fixedVersionForBranch() . ") to verify the patch.\n";
}
