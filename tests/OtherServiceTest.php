<?php

namespace Monnify\Tests;

use GuzzleHttp\Psr7\Response;
use Monnify\Services\OtherService;
use Monnify\Tests\Concerns\CreatesMockApiClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OtherServiceTest extends TestCase
{
    use CreatesMockApiClient;

    #[Test]
    public function banksUsesBanksEndpoint(): void
    {
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['banks' => []])),
        ]);

        $response = $service->banks();

        $this->assertSame(['banks' => []], $response);
        $this->assertSame('/api/v1/banks', $this->history[1]['request']->getUri()->getPath());
    }

    #[Test]
    public function banksWithUssdUsesSdkBanksEndpoint(): void
    {
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['banks' => []])),
        ]);

        $service->banksWithUSSD();

        $this->assertSame('/api/v1/sdk/transactions/banks', $this->history[1]['request']->getUri()->getPath());
    }

    /**
     * @param array<int, mixed> $responses
     */
    private function service(array $responses): OtherService
    {
        return new OtherService($this->apiClient($responses));
    }
}
