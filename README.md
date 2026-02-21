![SharpAPI GitHub cover](https://sharpapi.com/sharpapi-github-laravel-bg.jpg "SharpAPI Laravel Client")

# AI Related Skills Generator for Laravel

## 🚀 Leverage AI API to identify related skills for HR Tech and recruitment applications.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sharpapi/laravel-hr-related-skills.svg?style=flat-square)](https://packagist.org/packages/sharpapi/laravel-hr-related-skills)
[![Total Downloads](https://img.shields.io/packagist/dt/sharpapi/laravel-hr-related-skills.svg?style=flat-square)](https://packagist.org/packages/sharpapi/laravel-hr-related-skills)

Check the details at SharpAPI's [HR Tech API](https://sharpapi.com/en/catalog/ai/hr-tech) page.

---

## Requirements

- PHP >= 8.1
- Laravel >= 10.48.29

---

## Installation

Follow these steps to install and set up the SharpAPI Laravel Related Skills Generator package.

1. Install the package via `composer`:

```bash
composer require sharpapi/laravel-hr-related-skills
```

2. Register at [SharpAPI.com](https://sharpapi.com/) to obtain your API key.

3. Set the API key in your `.env` file:

```bash
SHARP_API_KEY=your_api_key_here
```

4. **[OPTIONAL]** Publish the configuration file:

```bash
php artisan vendor:publish --tag=sharpapi-hr-related-skills
```

---
## Key Features

- **AI-Powered Related Skills Generation**: Efficiently identify skills related to a given skill with relevance scores.
- **Multi-language Support**: Generate related skills in multiple languages.
- **Customizable Output**: Control the number of related skills returned.
- **Robust Polling for Results**: Polling-based API response handling with customizable intervals.
- **API Availability and Quota Check**: Check API availability and current usage quotas with SharpAPI's endpoints.

---

## Usage

You can inject the `HrRelatedSkillsService` class to access related skills generation functionality. For best results, especially with batch processing, use Laravel's queuing system to optimize job dispatch and result polling.

### Basic Workflow

1. **Dispatch Job**: Send a skill name to the API using `relatedSkills`, which returns a status URL.
2. **Poll for Results**: Use `fetchResults($statusUrl)` to poll until the job completes or fails.
3. **Process Result**: After completion, retrieve the results from the `SharpApiJob` object returned.

> **Note**: Each job typically takes a few seconds to complete. Once completed successfully, the status will update to `success`, and you can process the results as JSON, array, or object format.

---

### Controller Example

Here is an example of how to use `HrRelatedSkillsService` within a Laravel controller:

```php
<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\GuzzleException;
use SharpAPI\HrRelatedSkills\HrRelatedSkillsService;

class SkillsController extends Controller
{
    protected HrRelatedSkillsService $relatedSkillsService;

    public function __construct(HrRelatedSkillsService $relatedSkillsService)
    {
        $this->relatedSkillsService = $relatedSkillsService;
    }

    /**
     * @throws GuzzleException
     */
    public function getRelatedSkills(string $skillName)
    {
        $statusUrl = $this->relatedSkillsService->relatedSkills(
            $skillName,
            'English',   // optional language
            10   // optional maximum quantity
        );
        
        $result = $this->relatedSkillsService->fetchResults($statusUrl);

        return response()->json($result->getResultJson());
    }
}
```

### Handling Guzzle Exceptions

All requests are managed by Guzzle, so it's helpful to be familiar with [Guzzle Exceptions](https://docs.guzzlephp.org/en/stable/quickstart.html#exceptions).

Example:

```php
use GuzzleHttp\Exception\ClientException;

try {
    $statusUrl = $this->relatedSkillsService->relatedSkills('PHP', 'English', 10);
} catch (ClientException $e) {
    echo $e->getMessage();
}
```

---

## Optional Configuration

You can customize the configuration by setting the following environment variables in your `.env` file:

```bash
SHARP_API_KEY=your_api_key_here
SHARP_API_JOB_STATUS_POLLING_WAIT=180
SHARP_API_JOB_STATUS_USE_POLLING_INTERVAL=true
SHARP_API_JOB_STATUS_POLLING_INTERVAL=10
SHARP_API_BASE_URL=https://sharpapi.com/api/v1
```

---

## Related Skills Data Format Example

```json
{
  "data": {
    "type": "api_job_result",
    "id": "bac70cd7-5347-4443-9632-c82019f73e9a",
    "attributes": {
      "status": "success",
      "type": "hr_related_skills",
      "result": {
        "skill": "Quicken",
        "related_skills": [
          {
            "name": "Accounting",
            "weight": 8.7
          },
          {
            "name": "Bookkeeping",
            "weight": 7
          },
          {
            "name": "Financial Management",
            "weight": 6.8
          },
          {
            "name": "Financial Reporting",
            "weight": 7.5
          },
          {
            "name": "Microsoft Excel",
            "weight": 6.5
          },
          {
            "name": "QuickBooks",
            "weight": 9.2
          }
        ]
      }
    }
  }
}
```

---

## Support & Feedback

For issues or suggestions, please:

- [Open an issue on GitHub](https://github.com/sharpapi/laravel-hr-related-skills/issues)
- Join our [Telegram community](https://t.me/sharpapi_community)

---

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for a detailed list of changes.

---

## Credits

- [A2Z WEB LTD](https://github.com/a2zwebltd)
- [Dawid Makowski](https://github.com/makowskid)
- Enhance your [Laravel AI](https://sharpapi.com/) capabilities!

---

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

---

## Follow Us

Stay updated with news, tutorials, and case studies:

- [SharpAPI on X (Twitter)](https://x.com/SharpAPI)
- [SharpAPI on YouTube](https://www.youtube.com/@SharpAPI)
- [SharpAPI on Vimeo](https://vimeo.com/SharpAPI)
- [SharpAPI on LinkedIn](https://www.linkedin.com/products/a2z-web-ltd-sharpapicom-automate-with-aipowered-api/)
- [SharpAPI on Facebook](https://www.facebook.com/profile.php?id=61554115896974)