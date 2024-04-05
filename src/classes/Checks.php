<?php

namespace DjinnDev\InputSanity;

use \DjinnDev\InputSanity\Abstracts\Base;

class Checks extends Base
{
    /**
     * Public.
	 * Determine whether the given value is a binary string by checking to see if it has detectable character encoding.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public static function isBinary($value): bool
	{
		return (false === mb_detect_encoding((string) $value, null, true));
	}

	public static function isTimestamp($value): bool
	{
		if(is_int($value))
		{
			$isEqual = ((int) (string) $value === $value);
		}
		else
		{
			$isEqual = ((string) (int) $value === $value);
			$value = parseInt($value);
		}

		return ($isEqual && $value <= PHP_INT_MAX && $value >= ~PHP_INT_MAX);
	}
}