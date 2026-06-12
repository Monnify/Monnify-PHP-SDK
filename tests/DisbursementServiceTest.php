<?php

namespace Monnify\Tests;

use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Monnify\Services\DisbursementService;
use Monnify\Tests\Concerns\CreatesMockApiClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DisbursementServiceTest extends TestCase
{
    use CreatesMockApiClient;

    #[Test]
    public function singlePostsTheExpectedPayload(): void
    {
        $payload = $this->validSingleTransferPayload();
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->single($payload);

        $this->assertSame('/api/v2/disbursements/single', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame($payload, json_decode((string) $this->history[1]['request']->getBody(), true));
    }

    #[Test]
    public function singleCanMarkRequestsAsAsync(): void
    {
        $payload = $this->validSingleTransferPayload();
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->single($payload, true);

        $body = json_decode((string) $this->history[1]['request']->getBody(), true);
        $this->assertTrue($body['async']);
    }

    #[Test]
    public function bulkPostsTheExpectedPayload(): void
    {
        $payload = $this->validBulkTransferPayload();
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->bulk($payload);

        $this->assertSame('/api/v2/disbursements/batch', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame($payload, json_decode((string) $this->history[1]['request']->getBody(), true));
    }

    #[Test]
    public function authorizationEndpointsPostTheExpectedPayloads(): void
    {
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $payload = ['reference' => 'ref-123', 'authorizationCode' => '123456'];
        $service->authoriseSingle($payload);
        $service->authoriseBulk($payload);

        $this->assertSame('/api/v2/disbursements/single/validate-otp', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame('/api/v2/disbursements/batch/validate-otp', $this->history[2]['request']->getUri()->getPath());
    }

    #[Test]
    public function resendOtpPostsTheReferencePayload(): void
    {
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->resendOTP('ref-123');
        $service->bulkResendOTP('batch-123');

        $this->assertSame('/api/v2/disbursements/single/resend-otp', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame(['reference' => 'ref-123'], json_decode((string) $this->history[1]['request']->getBody(), true));
        $this->assertSame('/api/v2/disbursements/batch/resend-otp', $this->history[2]['request']->getUri()->getPath());
        $this->assertSame(['reference' => 'batch-123'], json_decode((string) $this->history[2]['request']->getBody(), true));
    }

    #[Test]
    public function summaryAndStatusEndpointsUseExpectedQueries(): void
    {
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
            new Response(200, [], $this->json(['ok' => true])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->bulkBatchSummary('batch-123');
        $service->singleStatus('ref-123');
        $service->bulkStatus('batch-123', 25, 2);

        $this->assertSame('/api/v2/disbursements/batch/summary', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame('reference=batch-123', $this->history[1]['request']->getUri()->getQuery());
        $this->assertSame('/api/v2/disbursements/single/summary', $this->history[2]['request']->getUri()->getPath());
        $this->assertSame('reference=ref-123', $this->history[2]['request']->getUri()->getQuery());
        $this->assertSame('/api/v2/disbursements/bulk/batch-123/transactions', $this->history[3]['request']->getUri()->getPath());
        $this->assertSame('pageSize=25&pageNo=2', $this->history[3]['request']->getUri()->getQuery());
    }

    #[Test]
    public function allCanListSingleAndBulkTransactions(): void
    {
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->all();
        $service->all('bulk', 15, 1);

        $this->assertSame('/api/v2/disbursements/single/transactions', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame('/api/v2/disbursements/bulk/transactions', $this->history[2]['request']->getUri()->getPath());
        $this->assertSame('pageSize=15&pageNo=1', $this->history[2]['request']->getUri()->getQuery());
    }

    #[Test]
    public function searchAndWalletBalanceUseExpectedQueries(): void
    {
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->search('1234567890', 50, 3);
        $service->walletBalance('0123456789');

        $this->assertSame('/api/v2/disbursements/search-transactions', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame('sourceAccountNumber=1234567890&pageSize=50&pageNo=3', $this->history[1]['request']->getUri()->getQuery());
        $this->assertSame('/api/v2/disbursements/wallet-balance', $this->history[2]['request']->getUri()->getPath());
        $this->assertSame('accountNumber=0123456789', $this->history[2]['request']->getUri()->getQuery());
    }

    #[Test]
    public function requiredReferencesAreRejected(): void
    {
        $service = $this->service([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Reference must be provided');

        $service->resendOTP('');
    }

    #[Test]
    public function singleRequiresAmount(): void
    {
        $service = $this->service([]);
        $payload = $this->validSingleTransferPayload();
        unset($payload['amount']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('amount is required');

        $service->single($payload);
    }

    #[Test]
    public function bulkAcceptsIntegerLikeNotificationInterval(): void
    {
        $payload = $this->validBulkTransferPayload();
        $payload['notificationInterval'] = '10';
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->bulk($payload);

        $this->assertSame($payload, json_decode((string) $this->history[1]['request']->getBody(), true));
    }

    #[Test]
    public function bulkRejectsUnknownValidationFailureValue(): void
    {
        $service = $this->service([]);
        $payload = $this->validBulkTransferPayload();
        $payload['onValidationFailure'] = 'IGNORE';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('onValidationFailure is invalid');

        $service->bulk($payload);
    }

    /**
     * @param array<int, mixed> $responses
     */
    private function service(array $responses): DisbursementService
    {
        return new DisbursementService($this->apiClient($responses));
    }

    private function validSingleTransferPayload(): array
    {
        return [
            'amount' => 5000,
            'reference' => 'ref-123',
            'narration' => 'Vendor payout',
            'destinationBankCode' => '058',
            'destinationAccountNumber' => '0123456789',
            'destinationAccountName' => 'Jane Doe',
            'currency' => 'NGN',
            'sourceAccountNumber' => '1234567890',
        ];
    }

    private function validBulkTransferPayload(): array
    {
        return [
            'title' => 'April payroll',
            'batchReference' => 'batch-123',
            'narration' => 'Salary',
            'sourceAccountNumber' => '1234567890',
            'notificationInterval' => 10,
            'onValidationFailure' => 'CONTINUE',
            'transactionList' => [
                [
                    'amount' => 5000,
                    'reference' => 'ref-123',
                    'narration' => 'Salary payment',
                    'destinationBankCode' => '058',
                    'destinationAccountNumber' => '0123456789',
                    'destinationAccountName' => 'Jane Doe',
                    'currency' => 'NGN',
                ],
            ],
        ];
    }

}
