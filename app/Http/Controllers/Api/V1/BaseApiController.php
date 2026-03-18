<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseApiController extends Controller
{
    /**
     * Return a standard success response.
     */
    protected function success(mixed $data, string $message = 'OK', int $code = 200): JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Return a standard error response.
     */
    protected function error(string $message, int $code = 400, array $errors = []): JsonResponse
    {
        $payload = [
            'status'  => 'error',
            'message' => $message,
        ];

        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $code);
    }

    /**
     * Return a paginated success response.
     */
    protected function paginated(mixed $paginator, string $message = 'OK'): JsonResponse
    {
        $data = $paginator instanceof LengthAwarePaginator
            ? $paginator->items()
            : $paginator->toArray()['data'] ?? $paginator;

        $meta = [];
        if ($paginator instanceof LengthAwarePaginator) {
            $meta['pagination'] = [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ];
        }

        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
            'meta'    => $meta,
        ]);
    }
}
