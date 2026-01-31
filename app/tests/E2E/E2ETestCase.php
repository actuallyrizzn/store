<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

abstract class E2ETestCase extends TestCase
{
    protected static function runRequest(array $request): array
    {
        $tmpDir = sys_get_temp_dir();
        $responseFile = $tmpDir . DIRECTORY_SEPARATOR . 'marketplace_e2e_resp_' . getmypid() . '_' . uniqid('', true) . '.json';
        $requestFile = $tmpDir . DIRECTORY_SEPARATOR . 'marketplace_e2e_req_' . getmypid() . '_' . uniqid('', true) . '.json';
        file_put_contents($requestFile, json_encode($request));
        $php = PHP_BINARY;
        $runner = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'run_request.php';
        $cmd = sprintf('"%s" %s %s %s', $php, escapeshellarg($runner), escapeshellarg($responseFile), escapeshellarg($requestFile));
        exec($cmd);
        $content = @file_get_contents($responseFile);
        @unlink($requestFile);
        @unlink($responseFile);
        return is_string($content) ? (json_decode($content, true) ?? ['code' => 0, 'body' => '', 'headers' => []]) : ['code' => 0, 'body' => '', 'headers' => []];
    }
}
