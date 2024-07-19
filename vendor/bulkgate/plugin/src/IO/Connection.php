<?php declare(strict_types=1);

namespace BulkGate\Plugin\IO;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{AuthenticateException, InvalidResponseException};

interface Connection
{
	/**
	 * @throws AuthenticateException|InvalidResponseException
	 */
	public function run(Request $request): Response;
}
