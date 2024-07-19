<?php declare(strict_types=1);

namespace BulkGate\Plugin\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

interface Configuration
{
	public function url(): string;


	public function product(): string;


	public function name(): string;


	public function version(): string;
}
