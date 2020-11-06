<?php

namespace App;

use Config;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class APIHelpers
{
    /**
     * Case type constants for configuring responses
     */
    const CAMEL_CASE = 'camel-case';
    const SNAKE_CASE = 'snake-case';
    const DEFAULT_CASE = self::SNAKE_CASE;

    /**
     * Case type config path
     */
    const CASE_TYPE_CONFIG_PATH = 'api.formatsOptions.caseType';

    /**
     * The header which can be used to override config provided case type
     */
    const CASE_TYPE_HEADER = 'X-Accept-Case-Type';

    /**
     * @var null|string Cache this value for a given request
     */
    protected static $requestedKeyCaseFormat = null;

    /**
     * Get the required 'case type' for transforming response data
     *
     * @return string
     */
    public static function getResponseCaseType()
    {
        $format = static::$requestedKeyCaseFormat;

        if (! is_null($format)) {
            return $format;
        }

        // See if the client is requesting a specific case type
        $caseFormat = request()->header(static::CASE_TYPE_HEADER, null);
        if (! is_null($caseFormat)) {
            if ($caseFormat == static::CAMEL_CASE) {
                $format = static::CAMEL_CASE;
            } elseif ($caseFormat == static::SNAKE_CASE) {
                $format = static::SNAKE_CASE;
            }
        }

        // Get case format from config (default case)
        if (is_null($format)) {
            $caseFormat = Config(static::CASE_TYPE_CONFIG_PATH);

            // Figure out required case
            if ($caseFormat == static::CAMEL_CASE || empty($caseFormat)) {
                $format = static::CAMEL_CASE;
            } elseif ($caseFormat == static::SNAKE_CASE) {
                $format = static::SNAKE_CASE;
            } else {
                throw new BadRequestHttpException('Invalid case type specified in API config.');
            }
        }

        // Save and return
        static::$requestedKeyCaseFormat = $format;

        return $format;
    }

    /**
     * Formats case of the input array or scalar to desired case
     *
     * @param array|string $input
     * @param int|null $levels How many levels of an array keys to transform - by default recurse infinitely (null)
     * @return array|string $transformed
     */
    public static function formatKeyCaseAccordingToResponseFormat($input, $levels = null)
    {
        // Fail early in the event of special cases (such as a null which could be an array), to prevent unwanted casting
        if (empty($input)) {
            return $input;
        }

        // Use the other function for strings
        if (! is_array($input)) {
            return static::formatCaseAccordingToResponseFormat($input);
        }

        $caseFormat = static::getResponseCaseType();

        if ($caseFormat == static::CAMEL_CASE) {
            $transformed = Helpers::camelCaseArrayKeys($input, $levels);
        } elseif ($caseFormat == static::SNAKE_CASE) {
            $transformed = Helpers::snakeCaseArrayKeys($input, $levels);
        } else {
            // Shouldn't happen
            $transformed = $input;
        }

        return $transformed;
    }

    /**
     * Format the provided string into the required case response format, for attributes (ie. keys)
     *
     * @param string $attributeString
     * @return string
     */
    public static function formatCaseAccordingToResponseFormat($attributeString)
    {
        $format = static::getResponseCaseType();

        if ($format == static::CAMEL_CASE) {
            $attributeString = Helpers::camel($attributeString);
        } else {
            $attributeString = Helpers::snake($attributeString);
        }

        return $attributeString;
    }

    /**
     * Format the provided key string into the required case response format
     *
     * @deprecated Use the updated function name
     * @param string $key
     * @return string
     */
    public static function formatKeyCaseAccordingToReponseFormat($value)
    {
        return self::formatCaseAccordingToResponseFormat($value);
    }

    /**
     * Recursively camel-case an array's keys
     *
     * @param $array
     * @param int|null $levels How many levels of an array keys to transform - by default recurse infinitely (null)
     * @return array $array
     */
    public static function camelCaseArrayKeys($array, $levels = null)
    {
        foreach (array_keys($array) as $key) {
            // Get a reference to the value of the key (avoid copy)
            // Then remove that array element
            $value = &$array[$key];
            unset($array[$key]);

            // Transform key
            $transformedKey = static::camel($key);

            // Recurse
            if (is_array($value) && (is_null($levels) || --$levels > 0)) {
                $value = static::camelCaseArrayKeys($value, $levels);
            }

            // Store the transformed key with the referenced value
            $array[$transformedKey] = $value;

            // We'll be dealing with some large values, so memory cleanup is important
            unset($value);
        }

        return $array;
    }

    /**
     * Recursively snake-case an array's keys
     *
     * @param $array
     * @param int|null $levels How many levels of an array keys to transform - by default recurse infinitely (null)
     * @return array $array
     */
    public static function snakeCaseArrayKeys(array $array, $levels = null)
    {
        foreach (array_keys($array) as $key) {
            // Get a reference to the value of the key (avoid copy)
            // Then remove that array element
            $value = &$array[$key];
            unset($array[$key]);

            // Transform key
            $transformedKey = static::snake($key);

            // Recurse
            if (is_array($value) && (is_null($levels) || --$levels > 0)) {
                $value = static::snakeCaseArrayKeys($value, $levels);
            }

            // Store the transformed key with the referenced value
            $array[$transformedKey] = $value;

            // We'll be dealing with some large values, so memory cleanup is important
            unset($value);
        }

        return $array;
    }

    /**
     * Str::camel wrapper - for specific extra functionality
     * Note this is generally only applicable when dealing with API input/output key case
     *
     * @param string $value
     * @return string
     */
    public static function camel($value)
    {
        // Preserve all caps
        if (strtoupper($value) === $value) {
            return $value;
        }

        return Str::camel($value);
    }

    /**
     * Str::snake wrapper - for specific extra functionality
     * Note this is generally only applicable when dealing with API input/output key case
     *
     * @param string $value
     * @return mixed|string|string[]|null
     */
    public static function snake($value)
    {
        // Preserve all caps
        if (strtoupper($value) === $value) {
            return $value;
        }

        $value = Str::snake($value);

        // Extra things which Str::snake doesn't do, but maybe should
        $value = str_replace('-', '_', $value);
        $value = preg_replace('/__+/', '_', $value);

        return $value;
    }

    /**
     * Get the calling method name
     *
     * @return string
     */
    public static function getCallingMethod()
    {
        return debug_backtrace()[1]['function'];
    }

    /**
     * Converts the name of a model class to the name of the relation of this resource on another model
     *
     * @param string $resourceName The name of the resource we are dealing with
     * @param string $relationType The type of relation - ie.. one to.. X ('one', 'many')
     * @return string The name of the relation, as it would appear inside an eloquent model
     */
    public static function modelRelationName($resourceName, $relationType = 'many')
    {
        if ($relationType == 'many') {
            return lcfirst(Str::plural(class_basename($resourceName)));
        } elseif ($relationType == 'one') {
            return lcfirst(class_basename($resourceName));
        } else {
            return '';
        }
    }
}
