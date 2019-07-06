# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
- Stats update cron task
- WHMCS usage stats like cPanel module
- Direct login links (mail, mysql, ...)  Redirect using SSO

## [1.0.0] - 2019-07-06
### Added
- Support for creating, terminating, (un)suspending, password changes and SSO

## [1.0.1] - 2019-07-06
### Changed
- Defaulted to using Client's last IP instead of the current IP in case it's an admin making the provisioning request and not the client.
### Fixed
- Fixed module options that had incorrect defaults set