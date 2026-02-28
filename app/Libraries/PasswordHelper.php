<?php

namespace App\Libraries;

class PasswordHelper
{
    private const ALGORITHM = PASSWORD_BCRYPT;
    private const COST = 12;

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

    public static function verifyPassword($password, $hash)
    {
        if (empty($password) || empty($hash)) {
            return false;
        }

        return password_verify($password, $hash);
    }

    public static function needsRehash($hash)
    {
        if (empty($hash)) {
            return true;
        }

        return password_needs_rehash($hash, self::ALGORITHM, [
            'cost' => self::COST
        ]);
    }

    public static function getHashInfo($hash)
    {
        if (empty($hash)) {
            return null;
        }

        return password_get_info($hash);
    }

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
