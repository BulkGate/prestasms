<?php declare(strict_types=1);

namespace BulkGate\Plugin\DI;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Exception;

class DependencyException extends Exception
{
}


class AutoWiringException extends DependencyException
{
}


class MissingServiceException extends DependencyException
{
}


class MissingParameterException extends DependencyException
{
}


class InvalidStateException extends DependencyException
{
}
