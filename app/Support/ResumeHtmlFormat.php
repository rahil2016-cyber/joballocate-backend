<?php

namespace App\Support;

/**
 * Helpers for resume HTML templates: hide empty blocks, normalize summary text.
 */
final class ResumeHtmlFormat
{
    /**
     * Strip tags, convert &lt;br&gt; variants to newlines, decode entities — output is safe for {@see e()} + nl2br.
     */
    public static function plainMultiline(?string $raw): string
    {
        if ($raw === null) {
            return '';
        }
        $s = trim((string) $raw);
        if ($s === '') {
            return '';
        }
        // Decode first so escaped tags like &lt;br /&gt; become real tags, then normalize.
        $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $s = preg_replace('/<\s*br\s*\/?\s*>/iu', "\n", $s) ?? $s;
        $s = strip_tags($s);

        return trim($s);
    }

    public static function filled(?string $s): bool
    {
        if ($s === null) {
            return false;
        }
        $t = trim(strip_tags((string) $s));
        if ($t === '') {
            return false;
        }

        return ! self::isDashOrWhitespaceOnly($t);
    }

    /**
     * Experience row is shown only if there is real narrative or dates — not a lone placeholder title.
     */
    public static function experienceBlockVisible(?string $heading, ?string $dates, ?string $body): bool
    {
        return self::filled($body ?? null) || self::filled($dates ?? null);
    }

    /**
     * @param  array<int, mixed>  $list
     */
    public static function hasExperienceBlocks(array $list): bool
    {
        foreach ($list as $x) {
            if (! is_array($x)) {
                continue;
            }
            if (self::experienceBlockVisible(
                isset($x['heading']) ? (string) $x['heading'] : null,
                isset($x['dates']) ? (string) $x['dates'] : null,
                isset($x['body']) ? (string) $x['body'] : null,
            )) {
                return true;
            }
        }

        return false;
    }

    private static function isDashOrWhitespaceOnly(string $t): bool
    {
        return preg_match('/^[\s\-\x{2013}\x{2014}\x{2212}]+$/u', $t) === 1;
    }

    /**
     * @param  array<int, mixed>  $educationList
     * @param  array{course?: string, college?: string, score?: string}  $graduation
     */
    public static function hasEducationDisplay(array $educationList, array $graduation): bool
    {
        foreach ($educationList as $ed) {
            if (! is_array($ed)) {
                continue;
            }
            foreach (['title', 'institution', 'year', 'marks', 'mode'] as $k) {
                if (self::filled($ed[$k] ?? null)) {
                    return true;
                }
            }
        }

        return self::filled($graduation['course'] ?? null)
            || self::filled($graduation['college'] ?? null)
            || self::filled($graduation['score'] ?? null);
    }

    /**
     * @param  array<int, string>  $strings
     */
    public static function nonEmptyStrings(array $strings): array
    {
        $out = [];
        foreach ($strings as $s) {
            $str = is_string($s) ? $s : (string) $s;
            if (self::filled($str)) {
                $out[] = trim(strip_tags($str));
            }
        }

        return $out;
    }
}
