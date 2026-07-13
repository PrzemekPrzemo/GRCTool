<?php

namespace App\Services\Security;

/**
 * Minimalna implementacja AWS Signature Version 4 dla JSON API (np. Security Hub),
 * żeby uniknąć ciężkiej zależności aws/aws-sdk-php tylko dla jednego wywołania GetFindings.
 * https://docs.aws.amazon.com/general/latest/gr/sigv4-signing-and-authentication.html
 */
class AwsSigV4Signer
{
    /**
     * @param  array<string, string>  $extraHeaders  nagłówki poza Host/X-Amz-Date/Content-Type (np. X-Amz-Target)
     * @return array<string, string> pełny zestaw nagłówków do wysłania z żądaniem (z Authorization)
     */
    public static function signJsonRequest(
        string $method,
        string $host,
        string $path,
        string $region,
        string $service,
        string $accessKeyId,
        string $secretAccessKey,
        string $payload,
        array $extraHeaders = [],
        string $contentType = 'application/x-amz-json-1.1',
    ): array {
        $amzDate = gmdate('Ymd\THis\Z');
        $dateStamp = gmdate('Ymd');

        $headers = array_merge([
            'content-type' => $contentType,
            'host' => $host,
            'x-amz-date' => $amzDate,
        ], array_change_key_case($extraHeaders, CASE_LOWER));

        ksort($headers);

        $canonicalHeaders = '';
        foreach ($headers as $name => $value) {
            $canonicalHeaders .= "{$name}:".trim($value)."\n";
        }
        $signedHeaders = implode(';', array_keys($headers));

        $payloadHash = hash('sha256', $payload);
        $canonicalRequest = implode("\n", [
            $method,
            $path === '' ? '/' : $path,
            '', // no query string
            $canonicalHeaders,
            $signedHeaders,
            $payloadHash,
        ]);

        $algorithm = 'AWS4-HMAC-SHA256';
        $credentialScope = "{$dateStamp}/{$region}/{$service}/aws4_request";
        $stringToSign = implode("\n", [
            $algorithm,
            $amzDate,
            $credentialScope,
            hash('sha256', $canonicalRequest),
        ]);

        $kSecret = 'AWS4'.$secretAccessKey;
        $kDate = hash_hmac('sha256', $dateStamp, $kSecret, true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        $authorization = "{$algorithm} Credential={$accessKeyId}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

        return array_merge($headers, ['authorization' => $authorization]);
    }
}
