# Pinned to an EXACT known-vulnerable PHP release for PHP2Shell reproduction.
#
# PHP2Shell CVEs (both CVSS 9.2, fixed 2026-10-21):
#   CVE-2026-109618 - RCE via OOM corruption (partially-initialized request data)
#   CVE-2026-109619 - RCE via stale global state (use-after-free of request state)
#
# Fixed releases: 8.5.12, 8.4.27, 8.3.36, 8.2.35
# 8.3.35 is the LAST vulnerable release on the 8.3 branch — use this image to
# reproduce, then rebuild with FROM php:8.3.36-apache to verify the patch.
#
# Highest-risk SAPI per PHP's advisory is mod_php (apache), so we pin that.
FROM php:8.3.35-apache

# Verify the running version at build time so the pin can't silently drift.
RUN php -v | grep -q "PHP 8.3.35" || (echo "ERROR: expected PHP 8.3.35" && php -v && exit 1)

WORKDIR /var/www/html
COPY public/ /var/www/html/
COPY src/   /var/www/src/

RUN a2enmod rewrite

EXPOSE 80
