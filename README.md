# test-php — PHP2Shell lab (CVE-2026-109618 / CVE-2026-109619)

A controlled PHP project **pinned to an exact known-vulnerable PHP release** so the
PHP2Shell flaws can be reproduced and a patch can be verified by diffing behaviour
against a fixed build.

> ⚠️ This project intentionally runs an outdated, vulnerable PHP. Run it only in an
> isolated lab. `docker-compose.yml` binds to `127.0.0.1` on purpose — never expose
> this build to an untrusted network.

## The vulnerabilities

| CVE | Type | CVSS v4.0 | Fixed |
|-----|------|-----------|-------|
| CVE-2026-109618 | RCE via OOM corruption (partially-initialized request data) | 9.2 | 2026-10-21 |
| CVE-2026-109619 | RCE via stale global state (use-after-free of request-owned state) | 9.2 | 2026-10-21 |

Highest risk is **mod_php**, where an unauthenticated request can trigger the flaw
before any application code runs. Other SAPIs (PHP-FPM, php-cgi, FrankenPHP, LiteSpeed)
are also affected.

**Fixed releases:** 8.5.12, 8.4.27, 8.3.36, 8.2.35. Branches 8.1 and older (incl. 7.x,
5.x) receive **no fix** — the only remediation there is upgrading to a supported branch.

## The pinned version

This lab pins **PHP 8.3.35** — the last vulnerable release on the 8.3 branch — via three
mechanisms so it can't silently drift:

- `Dockerfile`: `FROM php:8.3.35-apache` with a build-time `php -v` assertion
- `composer.json`: `require.php` and `config.platform.php` both set to `8.3.35`
- `.php-version`: `8.3.35` (for `phpenv` / `asdf` style local toolchains)

## Run it

```bash
docker compose up --build
# then:
curl http://127.0.0.1:8080/
```

You'll see the running version and a vulnerability verdict computed by
`src/VersionInfo.php`.

## Detection manifest (`php.SCA.VERSION`)

`php.SCA.VERSION` is a hardcoded SCA manifest that scissor-five's
`hardcoded_airgap()` detector picks up (it scans for any file whose name contains
`.SCA.VERSION`). It declares PHP core itself as a detectable component so the
scanner reports the pinned version and matches it against the PHP2Shell CVEs.

Format — first line sets the language, each dependency is 4 comma-separated fields:

```
language=php
lib_name,lib_vendor,platform,lib_version
```

PHP core is declared by language and version only — `php,,,8.3.35`. Vendor and
platform are left empty: the engine ignores the platform column and resolves the
ecosystem from `language=php`.

Lines starting with `#` or `//` are ignored; rows that don't have exactly 4 fields
are skipped. Keep the
version here in lockstep with the `Dockerfile` / `composer.json` / `.php-version`
pin — bumping to a fixed release (e.g. `8.3.36`) should clear the findings.

## Verify the patch

Edit the `Dockerfile` to the fixed release and rebuild:

```dockerfile
FROM php:8.3.36-apache   # also update the grep assertion to 8.3.36
```

The endpoint will then report `no (patched)`. This before/after diff is the point of
the lab — confirm your detection/mitigation flips correctly across the fix boundary.

## Remediation (real systems)

Upgrade to the fixed release for your branch (≥ 8.3.36 on 8.3). If on an unsupported
branch (≤ 8.1, 7.x, 5.x), migrate to a supported, patched branch — no backport exists.

## Sources

- [Wiz — PHP2Shell blog](https://www.wiz.io/blog/php2shell-php-rce)
- [Wiz CVE-2026-109618](https://www.wiz.io/vulnerability-database/cve/cve-2026-109618)
- [Wiz CVE-2026-109619](https://www.wiz.io/vulnerability-database/cve/cve-2026-109619)
- [BleepingComputer — PHP ships emergency patches](https://www.bleepingcomputer.com/news/security/php-ships-emergency-patches-for-critical-php2shell-flaws/)
- [Picus Security — PHP2Shell explained](https://www.picussecurity.com/resource/php2shell-the-two-critical-php-rce-flaws-cve-2026-109618-cve-2026-109619-explained)
- [Help Net Security — patches](https://helpnetsecurity.com/2026/10/21/php2shell-php-vulnerabilities-patched-cve-2026-109618-cve-2026-109619)
