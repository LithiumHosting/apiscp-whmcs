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

## [1.0.10] 2022-05-16
### Fixed
- Password not being set when new account created, defaulted to ApisCP generated random password.
- Code formatting, embracing the tab