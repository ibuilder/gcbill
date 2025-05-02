<?php

<?php
namespace App\Helpers;

class ViewHelper {
    /**
     * Get Bootstrap badge class based on status string.
     */
    public static function getStatusBadgeClass(string $status): string {
        switch (strtolower($status)) {
            case 'draft': return 'secondary';
            case 'submitted': return 'info';
            case 'approved': return 'primary';
            case 'paid': return 'success';
            case 'rejected':
            case 'void': return 'danger';
            case 'active': return 'success';
            case 'inactive': return 'danger';
            default: return 'light text-dark';
        }
    }

    // Add other view helpers here as needed...
    public static function formatCurrency($value, $currency = 'USD', $locale = 'en_US'): string {
         if ($value === null || $value === '') return ''; // Or return a default like '$0.00'
         $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
         return $formatter->formatCurrency((float)$value, $currency);
    }

     public static function formatDate($dateString, $format = 'm/d/Y'): string {
         if (empty($dateString) || $dateString === '0000-00-00') return 'N/A';
         try {
             $date = new \DateTime($dateString);
             return $date->format($format);
         } catch (\Exception $e) {
             return 'Invalid Date';
         }
     }
}