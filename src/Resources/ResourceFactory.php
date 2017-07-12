<?php

namespace Flugg\Responder\Resources;

use Flugg\Responder\Pagination\CursorFactory;
use Flugg\Responder\Pagination\CursorPaginator;
use Flugg\Responder\Pagination\PaginatorFactory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\Resource\Collection as CollectionResource;
use League\Fractal\Resource\Item as ItemResource;
use League\Fractal\Resource\NullResource;
use League\Fractal\Resource\ResourceInterface;
use Traversable;

/**
 * This class is responsible for making Fractal resources from a variety of data types.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ResourceFactory
{
    /**
     * A service class used to normalize the data into one of the supported data types.
     *
     * @var \Flugg\Responder\DataNormalizer
     */
    protected $normalizer;

    /**
     * A factory used to build Fractal paginator adapters.
     *
     * @var \Flugg\Responder\Pagination\PaginatorFactory
     */
    protected $paginatorFactory;

    /**
     * A factory used to build Fractal cursor objects.
     *
     * @var \Flugg\Responder\Pagination\CursorFactory
     */
    protected $cursorFactory;

    /**
     * Construct the resource factory.
     *
     * @param \Flugg\Responder\Resources\DataNormalizer    $normalizer
     * @param \Flugg\Responder\Pagination\PaginatorFactory $paginatorFactory
     * @param \Flugg\Responder\Pagination\CursorFactory    $cursorFactory
     */
    public function __construct(DataNormalizer $normalizer, PaginatorFactory $paginatorFactory, CursorFactory $cursorFactory)
    {
        $this->normalizer = $normalizer;
        $this->paginatorFactory = $paginatorFactory;
        $this->cursorFactory = $cursorFactory;
    }

    /**
     * Make resource from the given data.
     *
     * @param  mixed $data
     * @return \League\Fractal\Resource\ResourceInterface
     */
    public function make($data = null): ResourceInterface
    {
        $normalizedData = $this->normalizer->normalize($data);
        $resource = $this->instatiateResource($normalizedData);

        if ($data instanceof CursorPaginator) {
            $resource->setCursor($this->cursorFactory->make($data));
        } elseif ($data instanceof LengthAwarePaginator) {
            $resource->setPaginator($this->paginatorFactory->make($data));
        }

        return $resource;
    }

    /**r
     * Instatiate a new resource instance from the given data.
     *
     * @param  mixed $data
     * @return \League\Fractal\Resource\ResourceInterface
     */
    protected function instatiateResource($data): ResourceInterface
    {
        if (is_null($data)) {
            return new NullResource();
        } elseif ($data instanceof Traversable && ! $data instanceof Model) {
            return new CollectionResource($data);
        }

        return new ItemResource($data);
    }
}