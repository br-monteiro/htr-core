<?php
namespace HTR\Common;

use JsonSchema\Validator;

/**
 * This class contains the common methods used with JSON Objects
 *
 * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
 * @since 1.0
 */
class Json
{

    /**
     * Errors of JSON
     * @var string
     */
    private static $error;

    /**
     *
     * @var JsonSchema\Validator
     */
    private static $validate;

    /**
     * Encode the $value to JSON
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @param mixed $value
     * @return string|null
     */
    public static function enconde($value)
    {
        $jsonEncoded = json_encode($value);
        // catch error if it occurs
        self::catchError();

        return $jsonEncoded;
    }

    /**
     * Decode the JSON string to \stdClass
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @param string $value
     * @return \stdClass|string
     */
    public static function decode(string $value)
    {
        $stdObject = json_decode($value);
        // catch error if it occurs
        self::catchError();

        if (self::getError()) {
            return self::getError();
        }

        return $stdObject;
    }

    /**
     * Returns the error occurred while processing JSON
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @return string|bool
     */
    public static function getError()
    {
        return self::$error;
    }

    /**
     * Catch error if it occurs
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     */
    private static function catchError()
    {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                self::$error = false;
                break;
            case JSON_ERROR_DEPTH:
                self::$error = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                self::$error = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                self::$error = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                self::$error = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                self::$error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                self::$error = 'Unknown error';
                break;
        }
    }

    /**
     * Check if the data is according JSON Schema
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @param \stdClass|array $data
     * @param string $fileSchema
     * @return bool
     * @throws \Exception
     */
    public static function validate($data, string $fileSchema): bool
    {
        /**
         * Throw an \Exception in case if there's no file or is unreadable
         */
        if (!file_exists($fileSchema) || !is_readable($fileSchema)) {
            throw new \Exception("The file {$fileSchema} not exists or is not readable.");
        }

        $jsonSchema = self::decode(file_get_contents($fileSchema));

        self::getValidator()->validate($data, $jsonSchema);

        return self::getValidator()->isValid();
    }

    /**
     * Returns the error if it occurs
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @return string|bool
     * @throws \Exception
     */
    public static function getValidateErrors()
    {
        if (!self::$validate) {
            throw new \Exception("No have Schema to validate.");
        }
        return self::getValidator()->getErrors();
    }

    /**
     * Returns the Object Validator
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @return JsonSchema\Validator
     */
    private static function getValidator(): Validator
    {
        if (!self::$validate) {
            self::$validate = new Validator();
        }

        return self::$validate;
    }
}
