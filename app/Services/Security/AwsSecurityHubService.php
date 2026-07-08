<?php

namespace App\Services\Security;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Ciągnie aktywne findings z AWS Security Hub (agreguje GuardDuty, Inspector, Config)
 * — u nas AWS hostuje aplikacje wewnętrzne, więc to główne źródło podatności/misconfigów
 * dla tej infrastruktury. Podpisywanie żądań SigV4 własnoręcznie (bez aws/aws-sdk-php),
 * żeby nie ciągnąć ciężkiej zależności tylko dla jednego wywołania.
 */
class AwsSecurityHubService
{
    public function isEnabled(): bool
    {
        return AppSetting::get('aws_security_hub_enabled', '0') === '1'
            && (bool) AppSetting::get('aws_access_key_id')
            && (bool) AppSetting::get('aws_secret_access_key_encrypted')
            && (bool) AppSetting::get('aws_region');
    }

    /**
     * @return array{findings: array<int, array<string, mixed>>, nextToken: ?string}
     */
    public function fetchActiveFindings(int $maxResults = 50, ?string $nextToken = null): array
    {
        if (! $this->isEnabled()) {
            throw new RuntimeException('Synchronizacja AWS Security Hub jest wyłączona.');
        }

        $region = AppSetting::get('aws_region');
        $accessKeyId = AppSetting::get('aws_access_key_id');
        $secretAccessKey = Crypt::decryptString(AppSetting::get('aws_secret_access_key_encrypted'));
        $host = "securityhub.{$region}.amazonaws.com";

        $body = [
            'Filters' => [
                'RecordState' => [['Comparison' => 'EQUALS', 'Value' => 'ACTIVE']],
                'WorkflowStatus' => [['Comparison' => 'NOT_EQUALS', 'Value' => 'SUPPRESSED']],
            ],
            'MaxResults' => $maxResults,
        ];
        if ($nextToken) {
            $body['NextToken'] = $nextToken;
        }
        $payload = json_encode($body);

        $headers = AwsSigV4Signer::signJsonRequest(
            method: 'POST',
            host: $host,
            path: '/',
            region: $region,
            service: 'securityhub',
            accessKeyId: $accessKeyId,
            secretAccessKey: $secretAccessKey,
            payload: $payload,
            extraHeaders: ['x-amz-target' => 'SecurityHub.GetFindings'],
        );

        $response = Http::withHeaders($headers)->withBody($payload, 'application/x-amz-json-1.1')
            ->post("https://{$host}/");

        if ($response->failed()) {
            throw new RuntimeException('Błąd AWS Security Hub (GetFindings): '.$response->body());
        }

        return [
            'findings' => $response->json('Findings', []),
            'nextToken' => $response->json('NextToken'),
        ];
    }

    public function testConnection(): array
    {
        $result = $this->fetchActiveFindings(1);

        return ['sample_count' => count($result['findings'])];
    }
}
