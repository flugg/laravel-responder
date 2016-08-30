<?php

namespace Flugg\Responder\Traits;

/**
 * Use this trait in your base form request to convert all camel cased parameters to
 * snake case and boolean strings to PHP booleans when accessing the input from
 * the controller.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait ConvertsParameters
{
    /**
     * Check if an input element is set on the request.
     *
     * @param  string $key
     * @return bool
     */
    public function __isset($key)
    {
        return parent::__isset(snake_case($key));
    }

    /**
     * Get an input element from the request.
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        return parent::__get(snake_case($key));
    }

    /**
     * Get the validator instance for the request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        $this->getInputSource()->replace($this->getConvertedParameters());

        return parent::getValidatorInstance();
    }

    /**
     * Get the input source for the request.
     *
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    abstract protected function getInputSource();

    /**
     * Cast and convert parameters.
     *
     * @return array
     */
    protected function getConvertedParameters():array
    {
        $parameters = $this->all();
        $parameters = $this->castBooleans($parameters);
        $parameters = $this->convertToSnakeCase($parameters);

        if (method_exists($this, 'convertParameters')) {
            $parameters = $this->convertParameters($parameters);
        }

        return $parameters;
    }

    /**
     * Get all of the input and files for the request.
     *
     * @return array
     */
    abstract public function all();

    /**
     * Cast all string booleans to real boolean values.
     *
     * @param  mixed $input
     * @return array
     */
    protected function castBooleans($input):array
    {
        if ($this->castToBooleanIsDisabled()) {
            return;
        }

        $casted = [];

        foreach ($input as $key => $value) {
            $casted[$key] = $this->castValueToBoolean($value);
        }

        return $casted;
    }

    /**
     * Checks if the user wants to cast to booleans.
     *
     * @return bool
     */
    protected function castToBooleanIsDisabled():bool
    {
        return isset($this->castBooleans) && ! $this->castBooleans;
    }

    /**
     * Cast a given value to a boolean if it is in fact a boolean.
     *
     * @param  mixed $value
     * @return mixed
     */
    protected function castValueToBoolean($value)
    {
        if (in_array($value, ['true', 'false'])) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return $value;
    }

    /**
     * Convert a string or array to snake case.
     *
     * @param  mixed $input
     * @return mixed
     */
    protected function convertToSnakeCase($input)
    {
        if ($this->convertToSnakeCaseIsDisabled()) {
            return;
        }

        if (is_null($input)) {
            return null;
        } elseif (is_array($input)) {
            return $this->convertArrayToSnakeCase($input);
        }

        return snake_case($input);
    }

    /**
     * Checks if the user wants to convert to snake case.
     *
     * @return bool
     */
    protected function convertToSnakeCaseIsDisabled():bool
    {
        return isset($this->convertToSnakeCase) && ! $this->convertToSnakeCase;
    }

    /**
     * Convert all keys of an array to snake case.
     *
     * @param  array $input
     * @return array
     */
    protected function convertArrayToSnakeCase(array $input):array
    {
        $converted = [];

        foreach ($input as $key => $value) {
            $converted[snake_case($key)] = $value;
        }

        return $converted;
    }
}