<?php

namespace App\Helpers;

class SecurityHelper {

    private static string $tokenName = '_csrf_token'; // Session key for the token
    private static string $formInputName = '_token'; // Name of the hidden input field

    /**
     * Generate and store a CSRF token if one doesn't exist.
     * Ensures session is started.
     * @return string The CSRF token.
     */
    public static function generateToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION[self::$tokenName])) {
            try {
                $_SESSION[self::$tokenName] = bin2hex(random_bytes(32));
            } catch (\Exception $e) {
                // Handle error if random_bytes fails (highly unlikely in modern PHP)
                error_log("Failed to generate secure CSRF token: " . $e->getMessage());
                // Fallback to less secure method (consider logging this occurrence)
                $_SESSION[self::$tokenName] = md5(uniqid((string)rand(), true));
            }
        }
        return $_SESSION[self::$tokenName];
    }

    /**
     * Validate a submitted CSRF token against the one in the session.
     * Ensures session is started.
     * @param string|null $submittedToken The token from the POST/request data (or null if not present).
     * @return bool True if valid and matches session token, false otherwise.
     */
    public static function validateToken(string $submittedToken = null): bool {
         if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Retrieve token from session safely
        $tokenInSession = $_SESSION[self::$tokenName] ?? null;

        // Basic checks for missing tokens
        if (empty($submittedToken) || empty($tokenInSession)) {
            error_log("CSRF Validation Failed: Submitted token or session token is missing.");
            return false;
        }

        // Use hash_equals for timing-attack safe comparison
        $isValid = hash_equals($tokenInSession, $submittedToken);

        if (!$isValid) {
             error_log("CSRF Validation Failed: Token mismatch.");
             // Security consideration: Optionally remove the used token from session
             // to prevent replay attacks, although a new one should be generated per request/form load.
             // unset($_SESSION[self::$tokenName]);
        }

        // Optional: Regenerate token after successful validation for single-use tokens
        // if ($isValid) {
        //     unset($_SESSION[self::$tokenName]); // Remove old one
        //     self::generateToken(); // Generate new one for next request (if needed immediately)
        // }

        return $isValid;
    }

    /**
     * Get the name used for the form input field.
     * @return string
     */
    public static function getFormInputName(): string {
        return self::$formInputName;
    }

    /**
     * Generate the HTML hidden input field for the CSRF token.
     * Automatically generates a token if needed.
     * @return string HTML input tag.
     */
    public static function csrfField(): string {
        $token = self::generateToken(); // Ensure a token exists
        $inputName = self::getFormInputName();
        // Use htmlspecialchars to prevent XSS if the token value somehow gets compromised/malformed
        return '<input type="hidden" name="' . htmlspecialchars($inputName, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Basic input sanitization (example).
     * Use more specific sanitization/validation based on context.
     * Consider using filter_var functions.
     *
     * @param string|null $input
     * @return string Sanitized string (trimmed, tags stripped).
     */
    public static function sanitizeString(?string $input): string {
        if ($input === null) {
            return '';
        }
        return trim(strip_tags($input));
    }

    /**
    * Sanitize output for HTML context to prevent XSS.
    *
    * @param string|null $output
    * @return string Escaped string.
    */
    public static function escapeHtml(?string $output): string {
       if ($output === null) {
           return '';
       }
       return htmlspecialchars($output, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}