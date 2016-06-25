<?php

namespace Mangopixel\Responder\Exceptions;

use Exception;

/**
 *
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class TransformerNotFoundException extends Exception
{
    /**
     * Constructor.
     *
     * @param string|null $message
     */
    public function __construct( string $message = null )
    {
        parent::__construct( $message ?: '' );
    }
}