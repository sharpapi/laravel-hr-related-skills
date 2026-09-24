<?php

declare(strict_types=1);

use SharpAPI\HrRelatedSkills\HrRelatedSkillsService;

it('applies the use-polling-interval flag from config', function (bool $flag): void {
    config()->set('sharpapi-hr-related-skills.api_job_status_use_polling_interval', $flag);

    expect((new HrRelatedSkillsService)->isUseCustomInterval())->toBe($flag);
})->with([true, false]);

it('reads the polling settings from config', function (): void {
    config()->set('sharpapi-hr-related-skills.api_job_status_polling_interval', 7);
    config()->set('sharpapi-hr-related-skills.api_job_status_polling_wait', 42);

    $service = new HrRelatedSkillsService;

    expect($service->getApiJobStatusPollingInterval())->toBe(7)
        ->and($service->getApiJobStatusPollingWait())->toBe(42);
});

it('throws a clear exception when the API key is missing', function (): void {
    config()->set('sharpapi-hr-related-skills.api_key', null);

    new HrRelatedSkillsService;
})->throws(InvalidArgumentException::class);
