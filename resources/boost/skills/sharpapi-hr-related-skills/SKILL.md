---
name: sharpapi-hr-related-skills
description: Find skills related to a given skill, with relevance weights, via SharpAPI (sharpapi/laravel-hr-related-skills). Use when suggesting related or adjacent skills for a skill taxonomy, CV or job ad with AI, or when code touches HrRelatedSkillsService, relatedSkills(), fetchResults() or config/sharpapi-hr-related-skills.php.
---

# SharpAPI HR Related Skills (`sharpapi/laravel-hr-related-skills`)

Lists skills related to a given skill, each with a relevance weight. The package is a thin Laravel wrapper around `sharpapi/php-core`: one service class (`SharpAPI\HrRelatedSkills\HrRelatedSkillsService`), one config file, no facade and no container binding. Requires `sharpapi/php-core` ≥ 1.4.1 (pulled in by v1.1.0 of this package).

## When to use this skill

- Suggesting related or adjacent skills for a skill taxonomy, profile or job ad with AI.
- Writing or reviewing code that calls `HrRelatedSkillsService::relatedSkills()` or `fetchResults()`, or reads `config('sharpapi-hr-related-skills.*')`.
- Moving a SharpAPI call out of a web request into a queued job, or writing tests around it.

## Install / wiring checklist

1. `composer require sharpapi/laravel-hr-related-skills`. `SharpAPI\HrRelatedSkills\HrRelatedSkillsProvider` is auto-discovered; it only merges and publishes config.
2. Set `SHARP_API_KEY` in `.env`. The service constructor throws `InvalidArgumentException` when the key is empty, so resolving the service without a key fails at once (in tests too).
3. Optional: `php artisan vendor:publish --tag=sharpapi-hr-related-skills` copies `config/sharpapi-hr-related-skills.php`. Only publish it if you need per-package overrides; the env keys below already work without it.
4. Get the service by type-hinting `HrRelatedSkillsService` (constructor or `handle()` injection) or `app(HrRelatedSkillsService::class)`. `new HrRelatedSkillsService()` works too (no constructor args) but tests can't swap it with `$this->mock()`.

## API & config reference

### Config: `config/sharpapi-hr-related-skills.php`

| Key | Env | Default | Effect |
|---|---|---|---|
| `api_key` | `SHARP_API_KEY` | none | Required. |
| `base_url` | `SHARP_API_BASE_URL` | `https://sharpapi.com/api/v1` | API base URL. |
| `api_job_status_polling_wait` | `SHARP_API_JOB_STATUS_POLLING_WAIT` | `180` | Seconds `fetchResults()` keeps polling before it throws `ApiException`. |
| `api_job_status_polling_interval` | `SHARP_API_JOB_STATUS_POLLING_INTERVAL` | `10` | Seconds between status checks when the API sends no `Retry-After` header, or always when the next key is `true`. |
| `api_job_status_use_polling_interval` | `SHARP_API_JOB_STATUS_USE_POLLING_INTERVAL` | `false` | `true` ignores `Retry-After` and polls every `api_job_status_polling_interval` seconds. Applied since v1.1.0; older versions ignored it. |

The other `sharpapi/laravel-*` wrappers read the same `SHARP_API_*` env keys, so one `.env` block configures all of them. The service also inherits the php-core setters (`setApiJobStatusPollingWait()`, `setApiJobStatusPollingInterval()`, `setUseCustomInterval()`) for per-instance overrides.

### Submit: `relatedSkills()`

```php
public function relatedSkills(
    string $skillName,
    ?string $language = null,
    ?int $maxQuantity = null
): string
```

| Parameter | Type | Default | Meaning |
|---|---|---|---|
| `$skillName` | `string` | (required) | The skill to expand, e.g. "Quicken". Sent as `content`. |
| `$language` | `?string` | `null` | Output language as a full English name ("English", "German"), not an ISO code. `null` leaves it to the API. |
| `$maxQuantity` | `?int` | `null` | Upper bound on the number of items returned. Sent as `max_quantity`. |

Sends `POST /hr/related_skills` and returns the job's **status URL** (a string), not the result.

### Collect: `fetchResults()`

```php
public function fetchResults(string $statusUrl): \SharpAPI\Core\DTO\SharpApiJob
```

Blocks and polls until the job is `success` or `failed`, honouring `Retry-After` and the polling wait. `SharpApiJob` has public `id`, `type`, `status` (a plain string) and `?stdClass $result`, plus `getResultJson()`, `getResultArray()` (shallow) and `getResultObject()`.

### Result

Example (shortened from the README). `fetchResults()` returns only the `result` part; the README shows it inside the full `data.attributes` envelope.

```json
{
  "skill": "Quicken",
  "related_skills": [
    { "name": "QuickBooks", "weight": 9.2 },
    { "name": "Accounting", "weight": 8.7 },
    { "name": "Bookkeeping", "weight": 7 }
  ]
}
```

- `related_skills` is a list of `{name, weight}`. `weight` is a relevance score from 1.0 to 10.0 (10 = 100%). Items are not guaranteed to be sorted, so sort by `weight` yourself.

### Exceptions

- `InvalidArgumentException`: empty `SHARP_API_KEY`, thrown by the service constructor.
- `SharpAPI\Core\Exceptions\ApiException`: polling ran past `api_job_status_polling_wait`, or HTTP 429 retries ran out.
- `GuzzleHttp\Exception\ClientException` (4xx such as 401 bad key or 422 validation) and other `GuzzleHttp\Exception\GuzzleException`s (5xx, network): from either call.
- A job that finishes with status `failed` does **not** throw. See Gotchas.

## Recipes

### Queued job (the default way to call it)

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use SharpAPI\Core\Enums\SharpApiJobStatusEnum;
use SharpAPI\HrRelatedSkills\HrRelatedSkillsService;

class FindRelatedSkills implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Must exceed SHARP_API_JOB_STATUS_POLLING_WAIT (180 s by default) plus request time.
    public int $timeout = 300;

    // Every retry submits a new SharpAPI job and spends quota again.
    public int $tries = 1;

    public function __construct(public string $skill) {}

    public function handle(HrRelatedSkillsService $service): void
    {
        $statusUrl = $service->relatedSkills($this->skill, language: 'English', maxQuantity: 10);

        $job = $service->fetchResults($statusUrl);

        if ($job->status !== SharpApiJobStatusEnum::SUCCESS->value) {
            Log::warning('SharpAPI relatedSkills failed', ['job_id' => $job->id, 'status' => $job->status]);

            return;
        }

        $result = json_decode($job->getResultJson(), true);

        // ... persist $result
    }
}
```

Dispatch it with `FindRelatedSkills::dispatch(...)`. The worker's `--timeout` (or the Horizon supervisor `timeout`) and the queue connection's `retry_after` must both be at least the job's `$timeout`. Otherwise the worker kills the job mid-poll, or a second worker picks it up and submits it again.

### Reading the result

```php
$data = json_decode($job->getResultJson(), true);
$skills = collect($data['related_skills'] ?? [])
    ->sortByDesc('weight')
    ->pluck('name');
```

## Gotchas

1. `fetchResults()` already blocks and polls, up to `api_job_status_polling_wait` seconds, and honours `Retry-After`. Never write your own `while ($job->status === 'pending')` loop or call `fetchResults()` repeatedly.
2. A failed job does not throw. Compare `$job->status` with `SharpApiJobStatusEnum::SUCCESS->value` (`SharpAPI\Core\Enums\SharpApiJobStatusEnum`, values `new`, `pending`, `failed`, `success`) before reading the result. A failed job's `$result` carries no usable payload, so `->field` access on it gives undefined-property warnings and `null`s.
3. Never call `fetchResults()` inside an HTTP request, Nova action or Livewire action that runs synchronously: it can block for minutes. Use a queued job whose `$timeout` exceeds the polling wait. Keep `$tries` low (1-2), because each retry re-submits and burns quota. Worker/Horizon `timeout` and `retry_after` must be ≥ the job `$timeout`.
4. For reliable arrays use `json_decode($job->getResultJson(), true)`. `getResultArray()` only converts the top level, so nested values stay `stdClass`, and a list result comes back as an object with numeric keys.
5. Language and voice tone are free-text full names ("English", "Spanish", "Professional"), not ISO or locale codes. Passing `en` or an enum `->value` gives unpredictable output; pass a human label.

## Testing

`Http::fake()` does **not** intercept this package: php-core sends requests through its own Guzzle client. Mock the service instead. That only works when your code resolves it from the container (DI or `app()`), not with `new`.

```php
use Mockery\MockInterface;
use SharpAPI\Core\DTO\SharpApiJob;
use SharpAPI\HrRelatedSkills\HrRelatedSkillsService;

$this->mock(HrRelatedSkillsService::class, function (MockInterface $mock) {
    $mock->shouldReceive('relatedSkills')->once()->andReturn('https://sharpapi.com/api/v1/job/status/test-job');
    $mock->shouldReceive('fetchResults')->once()->andReturn(new SharpApiJob(
        id: 'test-job',
        type: 'hr_related_skills',
        status: 'success',
        result: (object) json_decode(json_encode(['skill' => 'PHP', 'related_skills' => [['name' => 'Laravel', 'weight' => 9]]])),
    ));
});
```

- Also cover the failure branch: return a `SharpApiJob` with `status: 'failed'` and `result: null`, and assert nothing gets persisted.
- The real constructor needs a non-empty key. Set `SHARP_API_KEY` (any value) in `phpunit.xml` if a test resolves the real service.
- Queue tests: `Queue::fake()` plus `Queue::assertPushed(FindRelatedSkills::class)`, then test `handle()` separately with the mocked service.
