<?php

namespace App\Interfaces\Http\Controllers;

use App\Support\ExceptionFormat;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait ResponseTrait
{
    /**
     * The current path of resource to respond
     *
     * @var string
     */
    protected string $resourceItem;

    /**
     * The current path of collection resource to respond
     *
     * @var string
     */
    protected string $resourceCollection;

    protected function respondWithCustomData($data, $status = 200): JsonResponse
    {
        return new JsonResponse([
            'data' => $data,
            'meta' => ['timestamp' => $this->getTimestampInMilliseconds()],
        ], $status);
    }

    protected function getTimestampInMilliseconds(): int
    {
        return intdiv((int) now()->format('Uu'), 1000);
    }

    /**
     * Return no content for delete requests
     */
    protected function respondWithNoContent(): JsonResponse
    {
        return new JsonResponse([
            'data' => null,
            'meta' => ['timestamp' => $this->getTimestampInMilliseconds()],
        ], Response::HTTP_NO_CONTENT);
    }

    /**
     * Return collection response from the application
     */
    protected function respondWithCollection(LengthAwarePaginator $collection)
    {
        return (new $this->resourceCollection($collection))->additional(
            ['meta' => ['timestamp' => $this->getTimestampInMilliseconds()]]
        );
    }

    /**
     * Return single item response from the application
     */
    protected function respondWithItem(Model $item)
    {
        return (new $this->resourceItem($item))->additional(
            ['meta' => ['timestamp' => $this->getTimestampInMilliseconds()]]
        );
    }

    public function respondWithError(
        string $message = 'Something Went Wrong',
        ?string $type = 'GenericException',
        ?string $code = 'generic-error',
        int $status = 500,
        ?string $transactionId = null,
        mixed $exception = null,
        array $headers = [],
    ): JsonResponse {
        $data = [
            'message' => $message,
            'type' => $type,
            'code' => $code,
            'status' => $status,
            'transaction_id' => is_null($transactionId) ? request()->header('x-transaction-id') : $transactionId,
        ];

        $options = 0;

        if (! app()->environment('production')) {
            $data['exception'] = ExceptionFormat::toArray($exception);
            $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
        }

        $data['meta']['timestamp'] = $this->getTimestampInMilliseconds();

        return new JsonResponse(data: $data, status: $status, headers: $headers, options: $options);
    }
}
