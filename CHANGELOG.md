# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
- Stats update cron task
- WHMCS usage stats like cPneal module
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