<?php

namespace Monnify\Tests;

use InvalidArgumentException;
use Monnify\Enums\WebhookEventType;
use Monnify\Webhooks\WebhookPayload;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WebhookPayloadTest extends TestCase
{
    #[Test]
    public function wraps_known_webhook_events(): void
    {
        $payload = WebhookPayload::fromArray([
            'eventType' => 'SUCCESSFUL_TRANSACTION',
            'eventData' => ['paymentReference' => 'MNFY|123'],
        ]);

        $this->assertSame('SUCCESSFUL_TRANSACTION', $payload->eventType);
        $this->assertSame(['paymentReference' => 'MNFY|123'], $payload->eventData);
        $this->assertSame(WebhookEventType::SuccessfulTransaction, $payload->knownEventType());
        $this->assertTrue($payload->is(WebhookEventType::SuccessfulTransaction));
    }

    #[Test]
    public function keeps_unknown_webhook_events_receivable(): void
    {
        $payload = WebhookPayload::fromArray([
            'eventType' => 'NEW_MONNIFY_EVENT',
            'eventData' => ['reference' => 'REF-123'],
        ]);

        $this->assertSame('NEW_MONNIFY_EVENT', $payload->eventType);
        $this->assertNull($payload->knownEventType());
    }

    #[Test]
    public function preserves_undocumented_top_level_fields(): void
    {
        $payload = WebhookPayload::fromArray([
            'eventType' => 'SUCCESSFUL_TRANSACTION',
            'eventData' => ['paymentReference' => 'MNFY|123'],
            'extra' => 'kept',
        ]);

        $this->assertSame('kept', $payload->raw['extra']);
    }

    #[Test]
    public function requires_a_valid_event_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('eventType');

        WebhookPayload::fromArray(['eventData' => []]);
    }

    #[Test]
    public function requires_event_data_to_be_an_array(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('eventData');

        WebhookPayload::fromArray([
            'eventType' => 'SUCCESSFUL_TRANSACTION',
            'eventData' => 'invalid',
        ]);
    }
}
