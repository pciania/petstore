<?php

use App\Integrations\Petstore\PetstoreClient;
use App\Integrations\Petstore\PetstoreException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
// -----------------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------------
function makeClient(array $responses): PetstoreClient
{
    $mock   = new MockHandler($responses);
    $stack  = HandlerStack::create($mock);
    $guzzle = new Client([
        'base_uri' => 'https://petstore.swagger.io/v2/',
        'timeout'  => 5,
        'handler'  => $stack,
        'headers'  => ['Accept' => 'application/json', 'Content-Type' => 'application/json'],
    ]);
    return new PetstoreClient(
        baseUrl: 'https://petstore.swagger.io/v2',
        timeout: 5,
        retries: 0,
        client: $guzzle,
    );
}
function petJson(array $overrides = []): string
{
    return (string) json_encode(array_merge([
        'id'        => 1,
        'name'      => 'Buddy',
        'status'    => 'available',
        'photoUrls' => [],
        'tags'      => [],
    ], $overrides));
}
// -----------------------------------------------------------------------------
// getPet
// -----------------------------------------------------------------------------
it('fetches a pet by ID', function () {
    $client = makeClient([
        new Response(200, [], petJson(['id' => 42, 'name' => 'Rex'])),
    ]);
    $result = $client->getPet(42);
    expect($result['id'])->toBe(42)
        ->and($result['name'])->toBe('Rex');
});
it('throws PetstoreException on 404', function () {
    $client = makeClient([
        new Response(404, [], '{"message":"Pet not found"}'),
    ]);
    $client->getPet(999);
})->throws(PetstoreException::class);
// -----------------------------------------------------------------------------
// createPet
// -----------------------------------------------------------------------------
it('creates a pet and returns decoded response', function () {
    $client = makeClient([
        new Response(200, [], petJson(['id' => 100, 'name' => 'Kitty'])),
    ]);
    $result = $client->createPet([
        'id'        => 100,
        'name'      => 'Kitty',
        'status'    => 'available',
        'photoUrls' => [],
        'tags'      => [],
    ]);
    expect($result['name'])->toBe('Kitty');
});
it('throws PetstoreException on 500 when creating a pet', function () {
    $client = makeClient([
        new Response(500, [], 'Internal Server Error'),
    ]);
    $client->createPet([
        'id'        => 1,
        'name'      => 'Broken',
        'status'    => 'available',
        'photoUrls' => [],
        'tags'      => [],
    ]);
})->throws(PetstoreException::class);
// -----------------------------------------------------------------------------
// updatePet
// -----------------------------------------------------------------------------
it('updates a pet successfully', function () {
    $client = makeClient([
        new Response(200, [], petJson(['id' => 1, 'name' => 'Buddy Updated'])),
    ]);
    $result = $client->updatePet([
        'id'        => 1,
        'name'      => 'Buddy Updated',
        'status'    => 'pending',
        'photoUrls' => [],
        'tags'      => [],
    ]);
    expect($result['name'])->toBe('Buddy Updated');
});
it('throws PetstoreException with status code on update 400', function () {
    $client = makeClient([
        new Response(400, [], '{"message":"Invalid input"}'),
    ]);
    $client->updatePet([
        'id'        => 1,
        'name'      => 'X',
        'status'    => 'available',
        'photoUrls' => [],
        'tags'      => [],
    ]);
})->throws(PetstoreException::class);
// -----------------------------------------------------------------------------
// deletePet
// -----------------------------------------------------------------------------
it('deletes a pet without throwing', function () {
    $client = makeClient([new Response(200, [], '')]);
    expect(fn () => $client->deletePet(1))->not->toThrow(PetstoreException::class);
});
it('throws PetstoreException when delete returns 404', function () {
    $client = makeClient([
        new Response(404, [], '{"message":"Pet not found"}'),
    ]);
    $client->deletePet(999);
})->throws(PetstoreException::class);
// -----------------------------------------------------------------------------
// findByStatus
// -----------------------------------------------------------------------------
it('returns a list of pets for a given status', function () {
    $body = (string) json_encode([
        ['id' => 1, 'name' => 'Pet A', 'status' => 'available', 'photoUrls' => [], 'tags' => []],
        ['id' => 2, 'name' => 'Pet B', 'status' => 'available', 'photoUrls' => [], 'tags' => []],
    ]);
    $client = makeClient([new Response(200, [], $body)]);
    $result = $client->findByStatus('available');
    expect($result)->toHaveCount(2)
        ->and($result[0]['name'])->toBe('Pet A');
});
// -----------------------------------------------------------------------------
// Network / connectivity errors
// -----------------------------------------------------------------------------
it('throws PetstoreException on network error', function () {
    $mock   = new MockHandler([
        new ConnectException('Connection refused', new Request('GET', 'pet/1')),
    ]);
    $stack  = HandlerStack::create($mock);
    $guzzle = new Client([
        'base_uri' => 'https://petstore.swagger.io/v2/',
        'handler'  => $stack,
    ]);
    $client = new PetstoreClient(
        baseUrl: 'https://petstore.swagger.io/v2',
        timeout: 5,
        retries: 0,
        client: $guzzle,
    );
    $client->getPet(1);
})->throws(PetstoreException::class);
// -----------------------------------------------------------------------------
// PetstoreException helpers
// -----------------------------------------------------------------------------
it('correctly identifies a 404 exception as not-found', function () {
    $e = PetstoreException::fromHttpError(404, 'Not Found');
    expect($e->isNotFound())->toBeTrue()
        ->and($e->isClientError())->toBeTrue()
        ->and($e->getStatusCode())->toBe(404);
});
it('correctly identifies a 500 exception as not a client error', function () {
    $e = PetstoreException::fromHttpError(500, 'Server Error');
    expect($e->isClientError())->toBeFalse()
        ->and($e->isNotFound())->toBeFalse();
});

// -----------------------------------------------------------------------------
// uploadImage
// -----------------------------------------------------------------------------

it('uploads an image and returns decoded response', function () {
    $responseBody = json_encode([
        'code'    => 200,
        'type'    => 'unknown',
        'message' => 'additionalMetadata: null\nFile uploaded to ./file.jpg, 1234 bytes',
    ]);

    $client = makeClient([new Response(200, [], $responseBody)]);

    $tmpFile = tempnam(sys_get_temp_dir(), 'pet_test_') . '.jpg';
    file_put_contents($tmpFile, str_repeat('x', 64));

    try {
        $result = $client->uploadImage(42, $tmpFile, 'test metadata');
    } finally {
        @unlink($tmpFile);
    }

    expect($result['code'])->toBe(200);
});

it('throws PetstoreException when upload returns 404', function () {
    $client = makeClient([
        new Response(404, [], '{"message":"Pet not found"}'),
    ]);

    $tmpFile = tempnam(sys_get_temp_dir(), 'pet_test_') . '.jpg';
    file_put_contents($tmpFile, 'fake image data');

    try {
        $client->uploadImage(999, $tmpFile);
    } finally {
        @unlink($tmpFile);
    }
})->throws(PetstoreException::class);

