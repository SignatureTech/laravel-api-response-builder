<?php

namespace SignatureTech\ResponseBuilder;

use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ResponseBuilder
{
    /** @var bool */
    private bool $status;

    /** @var string|null */
    private string|null $message = null;

    /** @var int */
    private int $httpCode;

    /** @var mixed */
    private mixed $data = null;

    /** @var mixed */
    private mixed $meta = null;

    /** @var mixed */
    private mixed $link = null;

    /** @var array */
    private array $httpHeaders = [];

    /** @var array */
    private array $response = [];

    /** @var array */
    private array $appends = [];

    /**
     * @param bool $status
     */
    public function __construct(bool $status = true, $httpCode = Response::HTTP_OK)
    {
        $this->status = $status;
        $this->httpCode = $httpCode;
    }

    /**
     * @param int|null $httpCode
     * @return static
     */
    public static function asSuccess(?int $httpCode = null): self
    {
        return new static(true, $httpCode ?? Response::HTTP_OK);
    }


    /**
     * @param int|null $httpCode
     * @return static
     */
    public static function asError(?int $httpCode = null): self
    {
        return new static(false, $httpCode ?? Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param string|null $message
     * @return $this
     */
    public function withMessage(string $message = null): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param array|null $headers
     * @return $this
     */
    public function withHttpHeaders(?array $headers): self
    {
        $this->httpHeaders = $headers;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function with(string $key, mixed $value): self
    {
        $this->appends[$key] = $value;

        return $this;
    }

    /**
     * @param $data
     * @param $resourceNamespace
     * @return $this
     */
    public function withData($data = null, $resourceNamespace = null): self
    {
        if ($data instanceof LengthAwarePaginator) {
            $this->withPagination($data, $resourceNamespace);
        } else {
            $this->data = $data;
        }

        return $this;
    }

    /**
     * @param LengthAwarePaginator $query
     * @return $this
     */
    public function withPagination(LengthAwarePaginator $resource, $resourceNamespace = null, $objectName = null, $additional = null): self
    {
        $this->meta = [
            'total_page' => $resource->lastPage(),
            'current_page' => $resource->currentPage(),
            'total_item' => $resource->total(),
            'per_page' => (int) $resource->perPage(),
        ];

        $this->link = [
            'next' => $resource->hasMorePages(),
            'prev' => boolval($resource->previousPageUrl())
        ];

        $data = $resourceNamespace
            ? ($resource instanceof LengthAwarePaginator || $resource instanceof Collection
                ? $resourceNamespace::collection($resource)
                : $resourceNamespace::make($resource))
            : $resource->items();

        $this->data = $objectName ? [$objectName => $data] : $data;

        if (!empty($additional)) {
            $this->data = array_merge((array) $this->data, $additional);
        }

        return $this;
    }

    /**
     * @param bool $condition
     * @param callable $callback
     * @return $this
     */
    public function when(bool $condition, callable $callback): self
    {
        if ($condition) {
            return $callback($this);
        }

        return $this;
    }

    /**
     * @param mixed $data
     * @param string|null $message
     * @param int|null $httpCode
     * @param array $appends
     * @return Response
     */
    public static function success(mixed $data, string $message = null, int $httpCode = null, array $appends = []): Response
    {
        return self::asSuccess($httpCode)
            ->when(!empty($data), function (ResponseBuilder $builder) use ($data) {
                return $builder->withData($data);
            })
            ->when(!empty($message), function (ResponseBuilder $builder) use ($message) {
                return $builder->withMessage($message);
            })
            ->when(!empty($appends), function (ResponseBuilder $builder) use ($appends) {
                foreach ($appends as $key => $value) {
                    $builder->with($key, $value);
                }

                return $builder;
            })->build();
    }

    /**
     * @param $message
     * @param $httpCode
     * @param $appends
     * @return Response
     */
    public static function error($message, $httpCode = null, $appends = []): Response
    {
        return self::asError($httpCode)
            ->when(!empty($message), function (ResponseBuilder $builder) use ($message) {
                return $builder->withMessage($message);
            })
            ->when(!empty($appends), function (ResponseBuilder $builder) use ($appends) {
                foreach ($appends as $key => $value) {
                    $builder->with($key, $value);
                }

                return $builder;
            })
            ->build();
    }

    /**
     * @param string|null $message
     * @return Response
     */
    public static function notFound(?string $message = 'Resource not found'): Response
    {
        return self::error($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * @param string|null $message
     * @return Response
     */
    public static function unauthorized(?string $message = 'Unauthorized access'): Response
    {
        return self::error($message, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param string|null $message
     * @return Response
     */
    public static function forbidden(?string $message = 'Access forbidden'): Response
    {
        return self::error($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * @param array $errors
     * @param string|null $message
     * @return Response
     */
    public static function validationError(array $errors, ?string $message = 'Validation failed'): Response
    {
        return self::error($message, Response::HTTP_UNPROCESSABLE_ENTITY, ['errors' => $errors]);
    }

    /**
     * @return Response
     */
    public function build(): Response
    {
        $this->response['status'] = $this->status;
        !is_null($this->message) && $this->response['message'] = $this->message;

        foreach ($this->appends as $key => $value) {
            $this->response[$key] = $value;
        }

        !is_null($this->data) && $this->response['data'] = $this->data;
        !is_null($this->meta) && $this->response['meta'] = $this->meta;
        !is_null($this->link) && $this->response['link'] = $this->link;

        return response($this->response, $this->httpCode, $this->httpHeaders);
    }
}
