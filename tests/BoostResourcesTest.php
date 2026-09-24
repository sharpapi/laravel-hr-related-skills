<?php

declare(strict_types=1);

function boostPath(string $path = ''): string
{
    return dirname(__DIR__).'/resources/boost'.($path !== '' ? '/'.$path : '');
}

/** @return array<string, string> */
function boostFrontmatter(string $contents): array
{
    if (! preg_match('/\A---\R(.*?)\R---\R/s', $contents, $matches)) {
        return [];
    }

    $data = [];

    foreach (preg_split('/\R/', $matches[1]) ?: [] as $line) {
        if (preg_match('/^([a-z_-]+):\s*(.*)$/i', $line, $pair)) {
            $data[$pair[1]] = trim($pair[2]);
        }
    }

    return $data;
}

it('ships the boost skill', function (): void {
    expect(boostPath('skills/sharpapi-hr-related-skills/SKILL.md'))->toBeFile();
});

it('gives every skill frontmatter whose name matches its folder', function (): void {
    $skills = glob(boostPath('skills/*/SKILL.md')) ?: [];

    expect($skills)->toHaveCount(1);

    foreach ($skills as $skill) {
        $frontmatter = boostFrontmatter((string) file_get_contents($skill));

        expect($frontmatter['name'] ?? null)->toBe(basename(dirname($skill)))
            ->and($frontmatter['description'] ?? '')->not->toBe('');
    }
});

it('keeps the boost resources in the distributed archive', function (): void {
    $root = dirname(__DIR__);
    $attributes = is_file($root.'/.gitattributes') ? (string) file_get_contents($root.'/.gitattributes') : '';
    $composer = json_decode((string) file_get_contents($root.'/composer.json'), true);

    expect($attributes)->not->toMatch('#^/?resources\S*\s+export-ignore#m')
        ->and($composer['archive']['exclude'] ?? [])->not->toContain('resources')
        ->and($composer['archive']['exclude'] ?? [])->not->toContain('/resources');
});
