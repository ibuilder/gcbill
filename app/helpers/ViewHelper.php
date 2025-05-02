<?php

namespace App\Helpers;

class ViewHelper {
    /**
     * Get Bootstrap badge class based on status string.
     * @param string|null $status
     * @return string CSS class name.
     */
    public static function getStatusBadgeClass(?string $status): string {
        switch (strtolower($status ?? '')) { // Handle null status gracefully
            case 'draft': return 'secondary';
            case 'submitted': return 'info';
            case 'approved': return 'primary';
            case 'paid': return 'success';
            case 'rejected':
            case 'void': return 'danger';
            case 'active': return 'success';
            case 'inactive': return 'secondary'; // Changed inactive to secondary for less alarm
            case 'pending': return 'warning';
            case 'overdue': return 'danger';
            default: return 'light text-dark';
        }
    }

    /**
     * Format a numeric value as currency.
     * @param mixed $value The numeric value.
     * @param string $currency Currency code (e.g., 'USD').
     * @param string $locale Locale string (e.g., 'en_US').
     * @return string Formatted currency string or empty string on failure/invalid input.
     */
    public static function formatCurrency($value, string $currency = 'USD', string $locale = 'en_US'): string {
         // Check if intl extension is loaded
         if (!class_exists('\NumberFormatter')) {
             // Fallback or log error if intl is not available
             return (is_numeric($value) ? number_format((float)$value, 2) : '');
         }

         // Ensure value is numeric before formatting
         if (!is_numeric($value)) {
             return ''; // Or return a default like '$0.00' or 'N/A'
         }

         try {
             $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
             // Handle potential errors during formatter creation if locale is invalid
             if (!$formatter) {
                 return number_format((float)$value, 2); // Fallback formatting
             }
             return $formatter->formatCurrency((float)$value, $currency);
         } catch (\Exception $e) {
             error_log("Error formatting currency: " . $e->getMessage());
             return number_format((float)$value, 2); // Fallback formatting
         }
    }

    /**
     * Format a date string into a specified format.
     * @param string|null $dateString Input date string.
     * @param string $format Desired output format (e.g., 'm/d/Y').
     * @param string $default Output if dateString is empty or invalid (e.g., 'N/A').
     * @return string Formatted date string or default value.
     */
     public static function formatDate(?string $dateString, string $format = 'm/d/Y', string $default = 'N/A'): string {
         // Handle empty or zero-date strings
         if (empty($dateString) || $dateString === '0000-00-00' || $dateString === '0000-00-00 00:00:00') {
             return $default;
         }
         try {
             // Attempt to create DateTime object, handles various input formats reasonably well
             $date = new \DateTime($dateString);
             return $date->format($format);
         } catch (\Exception $e) {
             // Log error if needed: error_log("Invalid date format: " . $dateString);
             return $default; // Return default for invalid date strings
         }
     }

     /**
      * Truncate text to a certain length and add ellipsis.
      * @param string|null $text
      * @param int $length Max length before truncating.
      * @param string $ellipsis String to append if truncated.
      * @return string
      */
     public static function truncateText(?string $text, int $length = 100, string $ellipsis = '...'): string {
        if ($text === null) return '';
        if (mb_strlen($text) > $length) {
            return rtrim(mb_substr($text, 0, $length)) . $ellipsis;
        }
        return $text;
     }

    // Add other view helpers here as needed...
    // e.g., generating HTML elements, formatting phone numbers, etc.
}