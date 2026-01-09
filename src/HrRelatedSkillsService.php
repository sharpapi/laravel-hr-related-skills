<?php

declare(strict_types=1);

namespace SharpAPI\HrRelatedSkills;

use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use SharpAPI\Core\Client\SharpApiClient;

/**
 * @api
 */
class HrRelatedSkillsService extends SharpApiClient
{
    /**
     * Initializes a new instance of the class.
     *
     * @throws InvalidArgumentException if the API key is empty.
     */
    public function __construct()
    {
        parent::__construct(config('sharpapi-hr-related-skills.api_key'));
        $this->setApiBaseUrl(
            config(
                'sharpapi-hr-related-skills.base_url',
                'https://sharpapi.com/api/v1'
            )
        );
        $this->setApiJobStatusPollingInterval(
            (int) config(
                'sharpapi-hr-related-skills.api_job_status_polling_interval',
                5)
        );
        $this->setApiJobStatusPollingWait(
            (int) config(
                'sharpapi-hr-related-skills.api_job_status_polling_wait',
                180)
        );
        $this->setUserAgent('SharpAPILaravelHrRelatedSkills/1.0.0');
    }

    /**
     * Generates a list of related skills with their weights as a float value (1.0-10.0)
     * where 10 equals 100%, the highest relevance score.
     *
     * @throws GuzzleException
     *
     * @api
     */
    public function relatedSkills(
        string $skillName,
        ?string $language = null,
        ?int $maxQuantity = null
    ): string {
        $response = $this->makeRequest(
            'POST',
            '/hr/related_skills',
            [
                'content' => $skillName,
                'language' => $language,
                'max_quantity' => $maxQuantity,
            ]);

        return $this->parseStatusUrl($response);
    }
}