<?php declare(strict_types=1);

namespace BulkGate\Plugin;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class Exception extends \Exception
{
}


class StrictException extends Exception
{
}


class JsonException extends \Exception
{
}


class InvalidKeyException extends Exception
{
}


class AuthenticateException extends Exception
{
}


class InvalidResponseException extends Exception
{
}


class InvalidJwtException extends Exception
{
}
