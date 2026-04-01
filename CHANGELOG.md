# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
- Stats update cron task
- WHMCS usage stats like cPanel module
- Utilize admin_get_plan to get plan config options for filtering form params
- Move all text to lang files (I'm lazy)
- Add X-Forwarded-For header with request for SSO session
- Ensure Client IP passed with Rampart whitelist

## [1.0.0] - 2019-07-06
### Added
- Support for creating, terminating, (un)suspending, password changes and SSO

## [1.0.1] - 2019-07-06
### Changed
- Defaulted to using Client's last IP instead of the current IP in case it's an admin making the provisioning request and not the client.
### Fixed
- Fixed module options that had incorrect defaults set

## [1.0.2] - 2019-07-07
### Changed
- Some code cleanup and refactoring
### Added
- Added Command Logging to the WHMCS Module Log to help with debugging issues.

## [1.0.3] - 2019-07-16
### Changed
- WHMCS package definition select box for plan selection via API generated List
- Updated the Client Area template to show Quick Links to login to the Panel via different Apps
### Added
- Utilize admin_list_plans to get plans from one apnscp server (admins: make sure to sync plans to all servers!)
- Direct login links (mail, mysql, ...)  Redirect using SSO
- Implemented the Change Plan functionality

## [1.0.4] - 2019-07-22
#### DELETED Release (skip to 1.0.5)

## [1.0.5] - 2019-07-22
### Removed
- Removed hooks.php file, not used yet

## [1.0.6] - 2019-07-28
### Changed
- Refactored most of the SoapApi stuff
### Added
- Usage Stats

## [1.0.7] - 2019-08-15
### Fixed
- Fixed module using the wrong config option

## [1.0.8] - 2020-03-04
### Added
- Rampart Blacklist checks with automatic unbanning and whitelisting when a client views the Service Details page
- Added "Login to Panel" to the left side nav menu on the Service Details page
- Added support for the X-Forwarded-For header
### Changed
- Stripped down the provisioning module, you only need to specify the plan to provision instead of all plan options
### Fixed
- All domains are made lower case prior to making API calls

## [1.0.9] - 2020-09-30
### Added
- Added support for Cancellation Hold
### Changed
- General code improvement and fixes
- Disabled Usage Updates (needs a full rework)
### FIXED
- Updated hook code for WHMCS v8 compatibility

## 2021-01-12
### Added
- Added Usage Stats Updating
- Added SiteID Population
### Fixed
- Fixed Admin Session issues

## [1.0.13] 2022-05-16
### Fixed
- Password not being set when new account created, defaulted to ApisCP generated random password.
- Code formatting, embracing the tab

## [1.0.14] - 2026-04-01
### Added
- `apnscp_getPublicIp()` using cURL with two fallback endpoints (`api.ipify.org`, `checkip.amazonaws.com`) to reliably determine the WHMCS server's public outbound IP even behind NAT or a reverse proxy.
- `ClientAreaPageProductDetails` hook now checks the client's public IP against the ApisCP Rampart (fail2ban) API and automatically unbans it, displaying a notice with the matched jail names.
- `Helper::buildEndpoint()` accepting either a WHMCS module params array or a tblservers row object, covering both module-call and hook/cron contexts.
- `apnscp_getPlans` now tries each enabled server in order and falls back to the next if one fails.
- `apnscp_getPlans` applies a 15-second socket timeout per server attempt to prevent long hangs.
- `apnscp_UsageUpdate` wrapped in try/catch that logs failures to the WHMCS module log instead of throwing an unhandled exception.
- Module logging added throughout `apnscp_getPlans` with per-attempt success and failure entries.

### Changed
- `Helper::buildEndpoint()` replaces standalone `apnscp_buildEndpoint()` across all module functions, hooks, and the cron job.
- `DailyCronJob` now wraps each server iteration in try/catch `\Throwable`, uses correct tblservers column names, `Helper::buildEndpoint()`, and `Helper::formatXml()`.
- `apnscp_checkIP` now always returns a consistent `array{is_banned, jails, rampart_enabled, error}` shape regardless of outcome.
- All `catch (Exception $e)` blocks changed to `catch (\Throwable $e)` to also catch PHP `Error` subclasses.
- All `require_once` paths use absolute `__DIR__` to prevent failures when WHMCS calls module functions from a different working directory.
- `APIVersion` updated from `1.0` to `1.1`.
- All functions and methods now have explicit return type declarations compatible with PHP 8.2+.
- `int|string` union types added to all custom field helper method parameters.
- `apnscpValidateCustomFields` now uses a static in-memory cache so repeated calls within the same request skip the database query.
- `apnscpGetCustomFields` results cached by package ID within `apnscp_UsageUpdate` to avoid redundant queries when multiple sites share a package.
- All public `Helper` methods have complete docblocks.

### Fixed
- `apnscp_getPlans` not loading plans due to `require_once` include cache being poisoned by a relative path in the wrong working directory context. Fixed with a `class_exists` guard and an absolute `require` fallback.
- All action functions crashing with a secondary error when the API connection failed because `$client` was referenced in the catch block before assignment. All catch blocks now guard against an uninitialised client.
- `apnscp_getPlans` querying disabled servers. Added `disabled = 0` filter.
- Deprecated `${variable}` string interpolation in `ServiceSingleSignOn` (syntax removed in PHP 8.2).
- `apnscpGetCustomFields` returning an undefined variable when no custom fields exist. Variable now initialised to `[]`.
- `DailyCronJob` using wrong column names (`serverhttpprefix`, `serverhostname`, `serverport`, `serverpassword`) instead of actual tblservers columns (`secure`, `hostname`, `port`, `password`).
- `DailyCronJob` operator precedence bug where the port fallback (`?: 2083`) applied to the entire concatenated endpoint string instead of just the port value.
- `DailyCronJob` no-op `$opts['dry-run'];` expression that read an undefined array key and discarded the result.
- `DailyCronJob` not catching API errors, causing unhandled exceptions when a server was unreachable during the nightly run.
- `apnscp_checkIP` `admin_collect` call crashing the entire ban-check flow when the server returned a class-not-found error. Now isolated in its own inner try/catch with `$rampart_enabled = true` as a safe fallback.

### Removed
- Standalone `apnscp_buildEndpoint()` function; consolidated into `Helper::buildEndpoint()`.
- Dead `$module`/`$method` local variables from `TerminateAccount`.
- Dangling commented-out `logModuleCall` signature stub.
