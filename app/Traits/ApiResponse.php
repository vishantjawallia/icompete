<?php

namespace App\Traits;

trait ApiResponse
{
    /**
     * Send a success response.
     *
     * @param  mixed  $data
     * @param  string  $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($message = 'Request Successful', $data = null, $code = 200)
    {
        $response = [
            'status'  => 'success',
            'message' => $message,
        ];

        if (! is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Send an error response.
     *
     * @param  string  $message
     * @param  int  $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse($message = 'Error', $code = 400, $errors = null)
    {
        $response = [
            'status'  => 'error',
            'message' => $message,
        ];

        if (! is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Not Found Response
     *
     * @param  string  $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function notFoundResponse($message = 'Resource not found')
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Forbidden Response
     *
     * @param  string  $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function forbiddenResponse($message = 'Forbidden')
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Paginated response
     *
     * @param  \Illuminate\Pagination\LengthAwarePaginator  $pagination
     * @return \Illuminate\Http\JsonResponse
     */
    protected function paginatedResponse(string $message, $items, $pagination, int $code = 200)
    {
        $pagination->appends(request()->except('page'));

        return response()->json([
            'status'        => 'success',
            'message'       => $message,
            'data'          => $items,
            'total'         => $pagination->total(),
            'current_page'  => $pagination->currentPage(),
            'current_items' => $pagination->count(),
            'previous_page' => $pagination->previousPageUrl(),
            'next_page'     => $pagination->nextPageUrl(),
            'last_page'     => $pagination->lastPage(),
            'per_page'      => $pagination->perPage(),
        ], $code);
    }

    /**
     * No Content Response
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function noContentResponse(string $message = 'No content', int $code = 204)
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
        ], $code);
    }

    public function paginateAndTransform($query, callable $transformer, $perPage = 30, $shuffle = false)
    {
        $paginator = $query->paginate($perPage);
        $collection = $paginator->getCollection();

        if ($shuffle) {
            $collection = $collection->shuffle();
        }

        $data = $collection->transform($transformer);

        return [$paginator, $data];
    }
}
