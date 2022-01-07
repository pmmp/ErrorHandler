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
use function restore_error_handler;
use function set_error_handler;

final class ErrorToExceptionHandler{
	private function __construct(){

	}

	/** @var ErrorRecord|null */
	private static $lastSilencedError = null;

	/**
	 * @throws \ErrorException
	 */
	public static function handle(int $severity, string $message, string $file, int $line) : bool{
		if((error_reporting() & $severity) !== 0){
			throw new \ErrorException($message, 0, $severity, $file, $line);
		}

		self::$lastSilencedError = new ErrorRecord($severity, $message, $file, $line);
		return true; //stfu operator
	}

	public static function getLastSilencedError() : ErrorRecord{
		if(self::$lastSilencedError === null){
			throw new \LogicException("No error has been generated");
		}
		return self::$lastSilencedError;
	}

	public static function clearLastSilencedError() : void{
		self::$lastSilencedError = null;
	}

	/** @phpstan-impure */
	public static function getAndClearLastSilencedError() : ErrorRecord{
		$result = self::getLastSilencedError();
		self::clearLastSilencedError();
		return $result;
	}

	/**
	 * Shorthand method to set the error-to-exception error handler.
	 */
	public static function set() : void{
		set_error_handler([self::class, 'handle']);
	}

	/**
	 * Runs the given closure inside the error-to-exception handler.
	 * Using this function guarantees that any error will cause an exception to be thrown, regardless of whether the
	 * global exception handler has been set properly or not.
	 *
	 * @phpstan-template TReturn
	 * @phpstan-param \Closure() : TReturn $closure
	 *
	 * @phpstan-return TReturn
	 * @throws \ErrorException
	 */
	public static function trap(\Closure $closure){
		self::set();
		try{
			return $closure();
		}finally{
			restore_error_handler();
		}
	}

	/**
	 * Same as trap(), but removes false from the set of possible return values. Mainly useful for PHPStan to unfalsify
	 * the results of stdlib functions that normally return false when emitting warnings.
	 *
	 * @phpstan-template TReturn
	 * @phpstan-param \Closure() : (TReturn|false) $closure
	 *
	 * @phpstan-return TReturn
	 * @throws \ErrorException
	 */
	public static function trapAndRemoveFalse(\Closure $closure){
		self::set();
		try{
			$result = $closure();
			if($result === false){
				throw new \LogicException("Block must not return false when no error occurred. Use trap() if the block may return false.");
			}
			return $result;
		}finally{
			restore_error_handler();
		}
	}
}
