<?php

namespace Mangopixel\Responder;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

/**
 * An abstract base transformer. All transformers should extend this, and this class
 * itself extends the Fractal transformer.
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
abstract class Transformer extends TransformerAbstract
{
    /**
     * Transform the model data into a generic array.
     *
     * @param  Model $model
     * @return array
     */
    abstract public function transform( $model ):array;
}