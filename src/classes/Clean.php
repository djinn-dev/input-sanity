<?php

namespace DjinnDev\InputSanity;

use \DjinnDev\InputSanity\Checks;

class Clean
{
    /**
     * Public.
	 * Trims string and truncates as needed
	 *
	 * @param mixed $value
	 * @param int $maxLength Set to -1 for unlimited
	 * @return mixed
	 */
	public static function trimString($value, int $maxLength = -1)
	{
		// Loop as needed
		if(is_array($value))
		{
			foreach($value as &$nestedValue)
			{
				$nestedValue = self::trimString($nestedValue, $maxLength);
			}
		}
		// Only handles strings
		if(!is_string($value))
		{
			return $value;
		}
		$value = trim($value);
		if($maxLength >= 0)
		{
			$value = substr($value, 0, $maxLength);
		}
		return trim($value);
	}

	/**
     * Public.
     * Forces all strings to use UTF-8 encoding
     * 
	 * @param mixed $value
	 * @return mixed
	 */
	public static function encodeAsUtf8($value)
	{
		if(is_string($value))
		{
			// This should fix any weird encoding
			return mb_convert_encoding($value, mb_detect_encoding($value, mb_detect_order(), true), 'UTF-8');
		}
		elseif(is_object($value))
		{
			// Convert object to array
			$value = json_decode(json_encode($value), 1);
		}

		if(is_array($value))
		{
			foreach($value as &$nestedValue)
			{
				$nestedValue = self::encodeAsUtf8($nestedValue);
			}
		}
		return $value;
	}

    /**
     * Public.
     * Convert non-boolean responses to coresponding boolean value
     * 
	 * @param mixed $value
	 * @param bool $defaultValue
	 * @return bool
	 */
	public static function sanitizeBool($value, bool $defaultValue = false): bool
	{
        if(is_bool($value))
        {
            return $value;
        }

		$value = strtolower($value);
		$validBooleans = [
			0 => false, 1 => true,
			'false' => false, 'true' => true,
			'f' => false, 't' => true,
			'no' => false, 'yes' => true,
			'n' => false, 'y' => true,
		];
		return $validBooleans[$value] ?? $defaultValue;
	}
    
    /**
	 * Public.
     * Trim and clean string inputs
	 *
	 * @param string $value
	 * @param bool $stripTags
	 * @param bool $stripHTMLChars
	 * @param int $maxLength
	 * @return string
	 */
	public static function sanitizeString(string $value, bool $stripTags = true, bool $stripHTMLChars = true, int $maxLength = -1): string
	{
        if(!is_string($value))
        {
            return (string) $value;
        }

		$value = $stripTags ? strip_tags($value) : $value;
		$value = $stripHTMLChars ? html_entity_decode($value) : $value;
		return self::trimValue($value, $maxLength);
	}

	/**
	 * Private.
     * Remove non-numeric characters
	 *
	 * @param mixed $value
	 * @return string
	 */
    private static function __cleanNumericValue($value): string
    {
        return preg_replace("/[^-?0-9.]/", "", (string) $value);
    }

	/**
	 * Public.
     * Clean input and convert to int
	 *
	 * @param mixed $value
	 * @return int
	 */
	public static function sanitizeInt($value): int
	{
        if(is_int($value))
        {
            return $value;
        }

		$value = self::__cleanNumericValue($value);
		return intval($value);
	}

	/**
	 * Public.
     * Clean input and convert to float
	 *
	 * @param mixed $value
	 * @return float
	 */
	public static function sanitizeFloat($value): float
	{
        if(is_float($value))
        {
            return $value;
        }

		$value = self::__cleanNumericValue($value);
		return floatval($value);
	}

	/**
	 * Public.
     * Clean timestamp/datetime and convert to given format
	 *
	 * @param mixed $value
	 * @param string $format
	 * @param string|false $defaultValue
	 * @return float
	 */
    public static function sanitizeDatetime($value, string $format = 'Y-m-d H:i:s', string|false $defaultValue = false): string|false
    {
		if($value === false)
		{
			return false;
		}

        $dtTool = new \DateTime();
		if(Checks::isTimestamp($value))
		{
			$dtTool->setTimestamp($value);
		}
		elseif(strtotime($value) !== false)
		{
			$dtTool->setTimestamp(strtotime($value));
		}
		else
		{
			return self::sanitizeDatetime($defaultValue, $format);
		}

		return $dtTool->format($format);
    }
}