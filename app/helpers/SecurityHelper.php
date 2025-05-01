<?php

namespace App\Helpers;

class SecurityHelper {

    private static string $tokenName = '_csrf_token'; // Session key for the token
    private static string $formInputName = '_token'; // Name of the hidden input field

    /**
     * Generate and store a CSRF token if one doesn't exist.
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
                // Handle error if random_bytes fails
                error_log("Failed to generate CSRF token: " . $e->getMessage());
                // Fallback or throw exception
                $_SESSION[self::$tokenName] = md5(uniqid(rand(), true));
            }
        }
        return $_SESSION[self::$tokenName];
    }

    /**
     * Validate a submitted CSRF token against the one in the session.
     * @param string|null $submittedToken The token from the POST request (or null if not present).
     * @return bool True if valid, false otherwise.
     */
    public static function validateToken(string $submittedToken = null): bool {
         if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $tokenInSession = $_SESSION[self::$tokenName] ?? null;

        if (empty($submittedToken) || empty($tokenInSession)) {
            error_log("CSRF Validation Failed: Submitted or session token missing.");
            return false;
        }

        $isValid = hash_equals($tokenInSession, $submittedToken);
        if (!$isValid) {
             error_log("CSRF Validation Failed: Token mismatch.");
             // Optionally regenerate token after failed attempt to prevent reuse
             // unset($_SESSION[self::$tokenName]);
        }
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
     * @return string HTML input tag.
     */
    public static function csrfField(): string {
        $token = self::generateToken();
        $inputName = self::getFormInputName();
        return '<input type="hidden" name="' . htmlspecialchars($inputName) . '" value="' . htmlspecialchars($token) . '">';
    }
}