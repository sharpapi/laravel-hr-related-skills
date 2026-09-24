# Changelog

All notable changes to `laravel-hr-related-skills` will be documented in this file.

## v1.1.0 - 2026-09-24

- Ship a Laravel Boost skill (`resources/boost/skills/sharpapi-hr-related-skills`) so AI coding agents use the async submit + `fetchResults()` flow, queued jobs and failed-job checks correctly.
- Fix: `api_job_status_use_polling_interval` (`SHARP_API_JOB_STATUS_USE_POLLING_INTERVAL`) is now applied. Before, the service ignored it and always followed the API's `Retry-After` header. If your `.env` sets it to `true`, polling now uses `SHARP_API_JOB_STATUS_POLLING_INTERVAL` instead.
- Require `sharpapi/php-core` ^1.4.1: a failed job with no result now returns a `SharpApiJob` instead of a `TypeError`, and a missing API key throws `InvalidArgumentException` with a clear message.
- Add a Pest test suite.

## v1.0.2 - 2026-02-21

Security: bumped minimum Laravel version to ^10.48.29 to address file validation bypass vulnerability (CVE). Dropped Laravel 9 support (EOL since Feb 2024).

## 1.0.0 - 2024-06-06

- Initial release
- Support for generating related skills with relevance scores
- Comprehensive documentation and examples