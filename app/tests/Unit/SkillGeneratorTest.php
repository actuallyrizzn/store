<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SkillGeneratorTest extends TestCase
{
    public function testEnsureGeneratedWritesFile(): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'skillgen_' . bin2hex(random_bytes(4));
        mkdir($dir, 0755, true);
        $templatePath = $dir . DIRECTORY_SEPARATOR . 'skill_template.md';
        file_put_contents($templatePath, 'Base: {{SITE_URL}}');
        $path = SkillGenerator::ensureGenerated($dir, 'http://example.test');
        $this->assertFileExists($path);
        $contents = file_get_contents($path);
        $this->assertSame('Base: http://example.test', $contents);
    }
}
