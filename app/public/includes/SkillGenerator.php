<?php

declare(strict_types=1);

final class SkillGenerator
{
    public static function generate(string $template, string $siteUrl): string
    {
        return str_replace('{{SITE_URL}}', rtrim($siteUrl, '/'), $template);
    }

    public static function ensureGenerated(string $baseDir, string $siteUrl): string
    {
        $templatePath = $baseDir . DIRECTORY_SEPARATOR . 'skill_template.md';
        $outputPath = $baseDir . DIRECTORY_SEPARATOR . 'generated_skill.md';
        $metaPath = $baseDir . DIRECTORY_SEPARATOR . 'generated_skill.meta';

        $template = is_readable($templatePath) ? file_get_contents($templatePath) : '';
        if ($template === false || $template === '') {
            return '';
        }
        $siteUrl = rtrim($siteUrl, '/');
        $meta = $siteUrl;
        if (is_readable($outputPath) && is_readable($metaPath)) {
            $existingMeta = file_get_contents($metaPath);
            if (is_string($existingMeta) && trim($existingMeta) === $meta) {
                return $outputPath;
            }
        }
        $generated = self::generate($template, $siteUrl);
        file_put_contents($outputPath, $generated);
        file_put_contents($metaPath, $meta);
        return $outputPath;
    }
}
