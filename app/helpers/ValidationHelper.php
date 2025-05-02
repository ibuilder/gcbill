<?php

namespace App\Helpers;

class ValidationHelper {

    /**
     * Check if a value is considered "empty" (null, empty string, empty array).
     * @param mixed $value
     * @return bool
     */
    public static function isEmpty(mixed $value): bool {
        return $value === null || $value === '' || $value === [];
    }

    /**
     * Validate if a string is a valid email address.
     * @param string $email
     * @return bool
     */
    public static function isValidEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate if a value is numeric.
     * @param mixed $value
     * @return bool
     */
    public static function isNumeric(mixed $value): bool {
        return is_numeric($value);
    }

    /**
     * Validate if a value is a positive number (integer or float).
     * @param mixed $value
     * @return bool
     */
    public static function isPositiveNumber(mixed $value): bool {
        return is_numeric($value) && $value > 0;
    }

     /**
     * Validate if a value is a non-negative number (integer or float).
     * @param mixed $value
     * @return bool
     */
    public static function isNonNegativeNumber(mixed $value): bool {
        return is_numeric($value) && $value >= 0;
    }

    /**
     * Validate string length is within a range.
     * @param string $value
     * @param int $min Minimum length (inclusive).
     * @param int|null $max Maximum length (inclusive, null for no max).
     * @return bool
     */
    public static function lengthBetween(string $value, int $min, ?int $max = null): bool {
        $len = mb_strlen($value); // Use multibyte-safe length check
        if ($len < $min) {
            return false;
        }
        if ($max !== null && $len > $max) {
            return false;
        }
        return true;
    }

    /**
     * Validate if a date string matches a specific format.
     * @param string $dateString
     * @param string $format (e.g., 'Y-m-d')
     * @return bool
     */
    public static function isValidDateFormat(string $dateString, string $format = 'Y-m-d'): bool {
        $d = \DateTime::createFromFormat($format, $dateString);
        // Check if the date was parsed successfully and matches the original string format
        return $d && $d->format($format) === $dateString;
    }

    // Add more specific validation rules as needed (e.g., isUrl, isInArray, matchesRegex)
}