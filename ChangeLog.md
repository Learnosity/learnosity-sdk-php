# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic
Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Fixed
- `DataApi::request*`: default `$requestPacket` to `[]` rather than `null`,
    which would cause cryptic errors if no packet is specified. Additionally, a
    warning is provided if the `$requestPacket` is not a PHP array.

## [v0.10.0] - 2018-09-17
### Added
- Telemetry support
- This ChangeLog!
