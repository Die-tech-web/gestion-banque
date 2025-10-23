<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\CompteResource;

trait ApiResponseTrait
{
    /**
     * Return a success JSON response.
     *
     * @param array|object|null $data
     * @param string $message
     * @param int $statusCode
     * @param array $pagination
     * @param array $links
     * @return JsonResponse
     */
    protected function success(array|object|null $data = null, string $message = 'Success', int $statusCode = Response::HTTP_OK): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => null, // Initialize data to null
        ];

        $pagination = [];
        $links = [];

        // Automatically extract pagination and links if $data is a paginator instance
        if ($data instanceof LengthAwarePaginator) {
            $pagination = [
                'currentPage' => $data->currentPage(),
                'totalPages' => $data->lastPage(),
                'totalItems' => $data->total(),
                'itemsPerPage' => $data->perPage(),
                'hasNext' => $data->hasMorePages(),
                'hasPrevious' => $data->currentPage() > 1,
            ];

            $links = [
                'self' => $data->url($data->currentPage()),
                'next' => $data->nextPageUrl(),
                'first' => $data->url(1),
                'last' => $data->url($data->lastPage()),
            ];
            $response['data'] = CompteResource::collection($data->items()); // Transform items using CompteResource
        } else {
            $response['data'] = $data;
        }

        if (!empty($pagination)) {
            $response['pagination'] = $pagination;
        }

        if (!empty($links)) {
            $response['links'] = $links;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return an error JSON response.
     *
     * @param string $message
     * @param int $statusCode
     * @param array $errors
     * @return JsonResponse
     */
    protected function error(string $message = 'Error', int $statusCode = Response::HTTP_BAD_REQUEST, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}
