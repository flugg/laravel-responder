<?php

namespace Flugg\Responder\Transformers;

use Closure;
use Flugg\Responder\Contracts\Transformers\TransformerResolver;
use Flugg\Responder\Transformers\Concerns\HasRelationships;
use Flugg\Responder\Transformers\Concerns\MakesResources;
use Flugg\Responder\Transformers\Concerns\OverridesFractal;
use Illuminate\Contracts\Container\Container;
use League\Fractal\TransformerAbstract;

/**
 * An abstract base transformer class responsible for transforming data.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
abstract class Transformer extends TransformerAbstract
{
    use HasRelationships;
    use MakesResources;
    use OverridesFractal;

    /**
     * The container resolver callback.
     *
     * @var \Closure|null
     */
    protected static $containerResolver;

    /**
     * Set a container using a resolver callback.
     *
     * @param  \Closure $resolver
     * @return void
     */
    public static function containerResolver(Closure $resolver)
    {
        static::$containerResolver = $resolver;
    }

    /**
     * Resolve a container using the resolver callback.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    protected function resolveContainer(): Container
    {
        return call_user_func(static::$containerResolver);
    }

    /**
     * Resolve a transformer from a class name string.
     *
     * @param  string $transformer
     * @return mixed
     */
    protected function resolveTransformer(string $transformer)
    {
        $transformerResolver = $this->resolveContainer()->make(TransformerResolver::class);

        return $transformerResolver->resolve($transformer);
    }
}