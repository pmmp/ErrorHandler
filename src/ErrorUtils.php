<?php

/*
 * PocketMine Standard PHP Library
 * Copyright (C) 2019 PocketMine Team <https://github.com/pmmp/PocketMine-SPL>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
*/

declare(strict_types=1);

namespace pocketmine\errorhandler;

use function error_reporting;
use function set_error_handler;

final class ErrorUtils{
	private function __construct(){

	}

	/**
	 * @param int    $severity
	 * @param string $message
	 * @param string $file
	 * @param int    $line
	 *
	 * @return bool
	 * @throws \ErrorException
	 */
	public static function errorExceptionHandler(int $severity, string $message, string $file, int $line) : bool{
		if((error_reporting() & $severity) !== 0){
			throw new \ErrorException($message, 0, $severity, $file, $line);
		}

		return true; //stfu operator
	}

	/**
	 * Shorthand method to set the error-to-exception error handler.
	 */
	public static function setErrorExceptionHandler() : void{
		set_error_handler([self::class, 'errorExceptionHandler']);
	}
}
