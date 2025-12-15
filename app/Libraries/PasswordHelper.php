<?php

namespace App\Libraries;

/**
 * PasswordHelper Class
 * 
 * Provides secure password hashing and verification using PHP's built-in
 * password_hash() and password_verify() functions with BCRYPT algorithm.
 * 
 * Security Features:
 * - Uses BCRYPT algorithm (resistant to brute force attacks)
 * - Automatically salts each password
 * - Configurable cost parameter for future-proofing
 * - No plaintext passwords stored or transmitted
 * 
 * @package App\Libraries
 * @author Security Team
 */
class PasswordHelper
{
    /**
     * Algorithm to use for password hashing
     */
    private const ALGORITHM = PASSWORD_BCRYPT;
    
    /**
     * Cost parameter for BCRYPT algorithm
     * Higher values = more secure but slower
     * Recommended: 10-12 (12 takes ~100ms per hash)
     */
    private const COST = 12;

    /**
     * Hash a plaintext password using BCRYPT algorithm
     * 
     * Features:
     * - Automatically generates random salt
     * - Each password gets unique hash
     * - Resistant to rainbow table attacks
     * - Takes ~100ms to compute (intentional slowness prevents brute force)
     * 
     * @param string $password The plaintext password to hash
     * @return string The hashed password ready for database storage
     * 
     * @example
     * $hashedPassword = PasswordHelper::hashPassword('myPassword123');
     * // Outputs: $2y$12$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36DY1pFm
     */
    public static function hashPassword($password)
    {
        if (empty($password)) {
            throw new \InvalidArgumentException('Password cannot be empty');
        }

        $hashed = password_hash($password, self::ALGORITHM, [
            'cost' => self::COST
        ]);

        if ($hashed === false) {
            throw new \RuntimeException('Failed to hash password');
        }

        return $hashed;
    }

    /**
     * Verify a plaintext password against a stored hash
     * 
     * Features:
     * - Time-constant comparison (prevents timing attacks)
     * - Returns false for invalid input
     * - Safe to use with any hash format
     * 
     * @param string $password The plaintext password to verify
     * @param string $hash The hashed password from database
     * @return bool True if password matches the hash, false otherwise
     * 
     * @example
     * if (PasswordHelper::verifyPassword('myPassword123', $storedHash)) {
     *     // Password is correct
     * } else {
     *     // Password is incorrect
     * }
     */
    public static function verifyPassword($password, $hash)
    {
        if (empty($password) || empty($hash)) {
            return false;
        }

        return password_verify($password, $hash);
    }

    /**
     * Check if a password hash needs to be rehashed
     * 
     * This is useful when:
     * - You want to increase COST parameter for security
     * - Algorithm is changed in the future
     * - During login, you can rehash on the fly
     * 
     * @param string $hash The hashed password from database
     * @return bool True if hash needs rehashing, false otherwise
     * 
     * @example
     * if (PasswordHelper::needsRehash($userHash)) {
     *     $newHash = PasswordHelper::hashPassword($plainPassword);
     *     // Save new hash to database
     * }
     */
    public static function needsRehash($hash)
    {
        if (empty($hash)) {
            return true;
        }

        return password_needs_rehash($hash, self::ALGORITHM, [
            'cost' => self::COST
        ]);
    }

    /**
     * Get hash information (algorithm, cost, etc.)
     * 
     * Useful for debugging or monitoring password security settings
     * 
     * @param string $hash The hashed password
     * @return array Information about the hash (algo, algoName, options)
     * 
     * @example
     * $info = PasswordHelper::getHashInfo($hash);
     * // Output: ['algo' => 2, 'algoName' => 'bcrypt', 'options' => ['cost' => 12]]
     */
    public static function getHashInfo($hash)
    {
        if (empty($hash)) {
            return null;
        }

        return password_get_info($hash);
    }

    /**
     * Validate password strength
     * 
     * Returns validation result with strength information
     * Minimum requirements:
     * - Length: 4 characters
     * - Not empty
     * 
     * @param string $password The password to validate
     * @return array ['valid' => bool, 'message' => string, 'strength' => 'weak|normal|strong']
     * 
     * @example
     * $result = PasswordHelper::validatePassword($password);
     * if (!$result['valid']) {
     *     echo $result['message'];
     * }
     */
    public static function validatePassword($password)
    {
        $result = [
            'valid' => true,
            'message' => 'Password valid',
            'strength' => 'normal'
        ];

        if (empty($password)) {
            return [
                'valid' => false,
                'message' => 'Password cannot be empty',
                'strength' => 'weak'
            ];
        }

        $length = strlen($password);

        if ($length < 4) {
            return [
                'valid' => false,
                'message' => 'Password must be at least 4 characters',
                'strength' => 'weak'
            ];
        }

        // Calculate strength
        $hasUppercase = preg_match('/[A-Z]/', $password);
        $hasLowercase = preg_match('/[a-z]/', $password);
        $hasNumbers = preg_match('/[0-9]/', $password);
        $hasSpecialChars = preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?\/]/', $password);

        $strengthScore = $hasUppercase + $hasLowercase + $hasNumbers + $hasSpecialChars;

        if ($length >= 12 && $strengthScore >= 3) {
            $result['strength'] = 'strong';
        } elseif ($length >= 8 && $strengthScore >= 2) {
            $result['strength'] = 'normal';
        } else {
            $result['strength'] = 'weak';
        }

        return $result;
    }
}
