<?php

namespace Flugg\Responder\Transformers;

use Flugg\Responder\Transformers\Concerns\HasRelationships;
use Flugg\Responder\Transformers\Concerns\MakesResources;
use Flugg\Responder\Transformers\Concerns\OverridesFractal;
use League\Fractal\TransformerAbstract;

/**
 * An abstract transformer class responsible for transforming data.
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
}