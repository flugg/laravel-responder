<?php

namespace Mangopixel\Responder;

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
}