<?php

declare(strict_types=1);

/**
 * GET /api/skill.php — Serve generated skill markdown.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/SkillGenerator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$siteUrl = Env::get('SITE_URL') ?? '';
$path = SkillGenerator::ensureGenerated($baseDir, $siteUrl);
if ($path === '' || !is_readable($path)) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Skill not available']);
    exit;
}

header('Content-Type: text/markdown');
readfile($path);
