<?php

declare(strict_types=1);

namespace SharpAPI\HrRelatedSkills\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use SharpAPI\HrRelatedSkills\HrRelatedSkillsProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [HrRelatedSkillsProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('sharpapi-hr-related-skills.api_key', 'test-key');
    }
}
