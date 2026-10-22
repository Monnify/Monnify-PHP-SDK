<?php

namespace Monnify\Tests;

use Monnify\Exceptions\InvalidWebhookSignatureException;
use Monnify\Webhooks\WebhookSignatureVerifier;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WebhookSignatureVerifierTest extends TestCase
{
    #[Test]
    public function accepts_a_valid_signature(): void
    {
        $verifier = new WebhookSignatureVerifier('test-secret-key');
        $body = json_encode(['eventType' => 'SUCCESSFUL_TRANSACTION']);
        $signature = hash_hmac('sha512', $body, 'test-secret-key');

        $verifier->verify($body, $signature);

        $this->assertTrue($verifier->isValid($body, $signature));
    }

    #[Test]
    public function rejects_an_invalid_signature(): void
    {
        $verifier = new WebhookSignatureVerifier('test-secret-key');

        $this->expectException(InvalidWebhookSignatureException::class);
        $this->expectExceptionMessage('invalid');

        $verifier->verify('{"eventType":"SUCCESSFUL_TRANSACTION"}', 'invalid');
    }

    #[Test]
    public function rejects_a_missing_signature(): void
    {
        $verifier = new WebhookSignatureVerifier('test-secret-key');

        $this->expectException(InvalidWebhookSignatureException::class);
        $this->expectExceptionMessage('missing');

        $verifier->verify('{"eventType":"SUCCESSFUL_TRANSACTION"}', null);
    }

    #[Test]
    public function rejects_a_missing_secret_key(): void
    {
        $this->expectException(InvalidWebhookSignatureException::class);
        $this->expectExceptionMessage('secret key');

        new WebhookSignatureVerifier('');
    }

    #[Test]
    public function exposes_expected_signature_for_framework_adapters(): void
    {
        $verifier = new WebhookSignatureVerifier('test-secret-key');
        $body = '{"eventType":"SUCCESSFUL_TRANSACTION"}';

        $this->assertSame(hash_hmac('sha512', $body, 'test-secret-key'), $verifier->expectedSignature($body));
    }
}
