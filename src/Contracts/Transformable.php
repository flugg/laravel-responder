<?php

namespace Mangopixel\Responder\Contracts;

/**
 *
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface Transformable
{
    /**
     *
     *
     * @return string
     */
    public static function transformer():string;
}