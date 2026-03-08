<?php

namespace App\Integrations\Petstore;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;
class PetstoreClient
{
    private readonly Client $client;
    public function __construct(
        private readonly string $baseUrl,
        private readonly int $timeout = 10,
        private readonly int $retries = 2,
        ?Client $client = null,
    ) {
        $this->client = $client ?? new Client([
            'base_uri' => rtrim($this->baseUrl, '/') . '/',
            'timeout'  => $this->timeout,
            'handler'  => $this->buildHandlerStack(),
            'headers'  => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }
    public function createPet(array $payload): array
    {
        return $this->post('pet', $payload);
    }
    public function getPet(int $id): array
    {
        return $this->get("pet/{$id}");
    }
    public function updatePet(array $payload): array
    {
        return $this->put('pet', $payload);
    }
    public function deletePet(int $id): void
    {
        $this->delete("pet/{$id}");
    }
    public function findByStatus(string $status = 'available'): array
    {
        $data = $this->get('pet/findByStatus', ['status' => $status]);
        if (! is_array($data)) {
            throw PetstoreException::invalidResponse('Expected an array of pets.');
        }
        return $data;
    }
    public function uploadImage(int $petId, string $filePath, string $additionalMetadata = ''): array
    {
        $multipart = [
            [
                'name'     => 'file',
                'contents' => fopen($filePath, 'r'),
                'filename' => basename($filePath),
            ],
        ];

        if ($additionalMetadata !== '') {
            $multipart[] = [
                'name'     => 'additionalMetadata',
                'contents' => $additionalMetadata,
            ];
        }

        return $this->request('POST', "pet/{$petId}/uploadImage", [
            'multipart' => $multipart,
            'headers'   => ['Accept' => 'application/json'],
        ]);
    }
    private function get(string $uri, array $query = []): array
    {
        return $this->request('GET', $uri, ['query' => $query]);
    }
    private function post(string $uri, array $payload): array
    {
        return $this->request('POST', $uri, ['json' => $payload]);
    }
    private function put(string $uri, array $payload): array
    {
        return $this->request('PUT', $uri, ['json' => $payload]);
    }
    private function delete(string $uri): void
    {
        $this->request('DELETE', $uri);
    }
    private function request(string $method, string $uri, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $uri, $options);
            return $this->decode($response);
        } catch (ConnectException $e) {
            throw PetstoreException::networkError($e->getMessage(), $e);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                throw PetstoreException::fromHttpError(
                    $response->getStatusCode(),
                    (string) $response->getBody(),
                );
            }
            throw PetstoreException::networkError($e->getMessage(), $e);
        }
    }
    private function decode(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        if (empty($body)) {
            return [];
        }
        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw PetstoreException::invalidResponse('JSON decode error: ' . json_last_error_msg());
        }
        return (array) $data;
    }
    private function buildHandlerStack(): HandlerStack
    {
        $stack = HandlerStack::create();
        $stack->push(Middleware::retry(
            decider: function (int $retries, $request, $response, $exception): bool {
                if ($retries >= $this->retries) {
                    return false;
                }
                if ($exception instanceof ConnectException) {
                    return true;
                }
                if ($response && $response->getStatusCode() >= 500) {
                    return true;
                }
                return false;
            },
            delay: fn (int $retries): int => 1000 * $retries,
        ));
        return $stack;
    }
}
