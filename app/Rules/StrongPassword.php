<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Lang;

/**
 * The password policy, in one place: the server-side rule, the live checklist shown
 * under password fields (resources/views/partials/password-rules.blade.php) and the
 * admin "Password generator" all read it from here, so they cannot drift apart.
 *
 * Same policy as the installer's admin account (InstallController::accountcheck).
 */
class StrongPassword implements ValidationRule
{
    public const MIN = 9;

    public const MAX = 16;

    /** Characters that satisfy the "special character" requirement. */
    public const SPECIAL = '~!@#$%^&*()_+|;:,.<>?';

    /**
     * Requirement key => [pattern (valid in both PHP and JavaScript), label lang key].
     * "length" has no pattern; it is checked against MIN/MAX.
     *
     * @return array<string, array{pattern: ?string, label: string}>
     */
    public static function requirements(): array
    {
        return [
            'length'    => ['pattern' => null, 'label' => 'lang.password_rule_length'],
            'lowercase' => ['pattern' => '[a-z]', 'label' => 'lang.password_rule_lowercase'],
            'uppercase' => ['pattern' => '[A-Z]', 'label' => 'lang.password_rule_uppercase'],
            'number'    => ['pattern' => '[0-9]', 'label' => 'lang.password_rule_number'],
            'special'   => ['pattern' => '[~!@#$%^&*()_+|;:,.<>?]', 'label' => 'lang.password_rule_special'],
        ];
    }

    /** Translated label of one requirement. */
    public static function label(string $key): string
    {
        return Lang::get(self::requirements()[$key]['label'], ['min' => self::MIN, 'max' => self::MAX]);
    }

    /**
     * Keys of the requirements $value does not meet (empty = valid).
     *
     * @return string[]
     */
    public static function failures(string $value): array
    {
        $failed = [];
        foreach (self::requirements() as $key => $requirement) {
            if ($requirement['pattern'] === null) {
                $length = mb_strlen($value);
                $ok = $length >= self::MIN && $length <= self::MAX;
            } else {
                $ok = preg_match('/'.$requirement['pattern'].'/u', $value) === 1;
            }
            if (!$ok) {
                $failed[] = $key;
            }
        }

        return $failed;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $failed = is_string($value) ? self::failures($value) : array_keys(self::requirements());
        if ($failed) {
            $fail(Lang::get('lang.password_does_not_meet_requirements', [
                'rules' => implode(', ', array_map([self::class, 'label'], $failed)),
            ]));
        }
    }

    /**
     * A random password that meets every requirement.
     *
     * "<", ">" and "&" are left out: the password is mailed to the user in an HTML
     * template, where they could be read as markup and show the wrong password.
     */
    public static function generate(int $length = 12): string
    {
        $length = max(self::MIN, min(self::MAX, $length));
        $sets = [
            'abcdefghijklmnopqrstuvwxyz',
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            '0123456789',
            str_replace(['<', '>', '&'], '', self::SPECIAL),
        ];

        $chars = [];
        foreach ($sets as $set) {
            $chars[] = $set[random_int(0, strlen($set) - 1)];
        }
        $all = implode('', $sets);
        while (count($chars) < $length) {
            $chars[] = $all[random_int(0, strlen($all) - 1)];
        }
        for ($i = count($chars) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$chars[$i], $chars[$j]] = [$chars[$j], $chars[$i]];
        }

        return implode('', $chars);
    }
}
