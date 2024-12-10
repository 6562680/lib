<?php

namespace Gzhegow\Lib\Traits;

use Gzhegow\Lib\Exception\RuntimeException;


trait StrTrait
{
    public static function str_mb(bool $bool = null) : bool
    {
        static $mb;

        $mb = $bool ?? $mb ?? extension_loaded('mbstring');

        if ($mb && ! extension_loaded('mbstring')) {
            throw new RuntimeException('Unable to use multibyte mode without extension: mbstring');
        }

        return $mb;
    }

    /**
     * @param callable|callable-string $fn
     *
     * @return callable|callable-string
     */
    public static function str_mbfunc(string $fn) : string
    {
        return static::str_mb()
            ? 'mb_' . $fn
            : $fn;
    }


    public static function str_is_utf8(string $str) : bool
    {
        return preg_match('//u', $str) === 1;
    }


    public static function str_lines(string $text) : array
    {
        $lines = explode("\n", $text);

        foreach ( $lines as $i => $line ) {
            $line = rtrim($line, PHP_EOL);

            $lines[ $i ] = $line;
        }

        return $lines;
    }

    public static function str_eol(string $text, array &$lines = null) : string
    {
        $lines = static::str_lines($text);

        $output = implode("\n", $lines);

        return $output;
    }


    /**
     * возвращает число символов в строке
     */
    public static function str_len($value) : int
    {
        if (! is_string($value)) {
            return 0;
        }

        if ('' === $value) {
            return 0;
        }

        $len = extension_loaded('mbstring')
            ? mb_strlen($value)
            : count(preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY));

        return $len;
    }

    /**
     * возвращает размер строки в байтах
     */
    public static function str_size($value) : int
    {
        if (! is_string($value)) {
            return 0;
        }

        if ('' === $value) {
            return 0;
        }

        $size = extension_loaded('mbstring')
            ? mb_strlen($value, '8bit')
            : strlen($value);

        return $size;
    }


    /**
     * пишет слово с малой буквы
     */
    public static function str_lcfirst(string $string, string $mb_encoding = null)
    {
        if (static::str_mb()) {
            $mbEncodingArgs = [];
            if (null !== $mb_encoding) {
                $mbEncodingArgs[] = $mb_encoding;
            }

            $result = (''
                . mb_strtolower(mb_substr($string, 0, 1, ...$mbEncodingArgs), ...$mbEncodingArgs)
                . mb_substr($string, 1, null, ...$mbEncodingArgs)
            );

        } else {
            if (static::str_is_utf8($string)) {
                throw new RuntimeException(
                    'You have to enable `php.ini` extension `mbstring` to prevent errors while converting multibyte symbols'
                );
            }

            $result = lcfirst($string);
        }

        return $result;
    }

    /**
     * пишет слово с большой буквы
     */
    public static function str_ucfirst(string $string, string $mb_encoding = null)
    {
        if (static::str_mb()) {
            $mbEncodingArgs = [];
            if (null !== $mb_encoding) {
                $mbEncodingArgs[] = $mb_encoding;
            }

            $result = (''
                . mb_strtoupper(mb_substr($string, 0, 1, ...$mbEncodingArgs), ...$mbEncodingArgs)
                . mb_substr($string, 1, null, ...$mbEncodingArgs)
            );

        } else {
            if (static::str_is_utf8($string)) {
                throw new RuntimeException(
                    'You have to enable `php.ini` extension `mbstring` to prevent errors while converting multibyte symbols'
                );
            }

            $result = ucfirst($string);
        }

        return $result;
    }


    /**
     * пишет каждое слово в предложении с малой буквы
     */
    public static function str_lcwords(string $string, string $separators = " \t\r\n\f\v", string $mb_encoding = null) : string
    {
        $regex = '/(^|[' . preg_quote($separators, '/') . '])(\w)/u';

        $result = preg_replace_callback(
            $regex,
            static function ($m) use ($mb_encoding) {
                $first = $m[ 1 ];
                $last = static::str_lcfirst($m[ 2 ], $mb_encoding);

                return "{$first}{$last}";
            },
            $string
        );

        return $result;
    }

    /**
     * пишет каждое слово в предложении с большой буквы
     */
    public static function str_ucwords(string $string, string $separators = " \t\r\n\f\v", string $mb_encoding = null) : string
    {
        $regex = '/(^|[' . preg_quote($separators, '/') . '])(\w)/u';

        $result = preg_replace_callback(
            $regex,
            static function ($m) use ($mb_encoding) {
                $first = $m[ 1 ];
                $last = static::str_ucfirst($m[ 2 ], $mb_encoding);

                return "{$first}{$last}";
            },
            $string
        );

        return $result;
    }


    /**
     * если строка начинается на искомую, отрезает ее и возвращает укороченную
     * if (null !== ($substr = _str_starts('hello', 'h'))) {} // 'ello'
     */
    public static function str_starts(string $string, string $needle, bool $ignoreCase = null) : ?string
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $string) return null;
        if ('' === $needle) return $string;

        $fnStrlen = static::str_mbfunc('strlen');
        $fnSubstr = static::str_mbfunc('substr');
        $fnStrpos = $ignoreCase
            ? static::str_mbfunc('stripos')
            : static::str_mbfunc('strpos');

        $pos = $fnStrpos($string, $needle);

        $result = 0 === $pos
            ? $fnSubstr($string, $fnStrlen($needle))
            : null;

        return $result;
    }

    /**
     * если строка заканчивается на искомую, отрезает ее и возвращает укороченную
     * if (null !== ($substr = _str_ends('hello', 'o'))) {} // 'hell'
     */
    public static function str_ends(string $string, string $needle, bool $ignoreCase = null) : ?string
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $string) return null;
        if ('' === $needle) return $string;

        $fnStrlen = static::str_mbfunc('strlen');
        $fnSubstr = static::str_mbfunc('substr');
        $fnStrrpos = $ignoreCase
            ? static::str_mbfunc('strripos')
            : static::str_mbfunc('strrpos');

        $pos = $fnStrrpos($string, $needle);

        $result = $pos === $fnStrlen($string) - $fnStrlen($needle)
            ? $fnSubstr($string, 0, $pos)
            : null;

        return $result;
    }

    /**
     * ищет подстроку в строке и разбивает по ней результат
     */
    public static function str_contains(string $string, string $needle, bool $ignoreCase = null, int $limit = null) : array
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $string) return [];
        if ('' === $needle) return [ $string ];

        $strCase = $ignoreCase
            ? str_ireplace($needle, $needle, $string)
            : $string;

        $result = [];

        $fnStrpos = $ignoreCase
            ? static::str_mbfunc('stripos')
            : static::str_mbfunc('strpos');

        if (false !== $fnStrpos($strCase, $needle)) {
            $result = null
                ?? (isset($limit) ? explode($needle, $strCase, $limit) : null)
                ?? (explode($needle, $strCase));
        }

        return $result;
    }


    /**
     * Обрезает у строки подстроку с начала (ltrim, только для строк а не букв)
     */
    public static function str_lcrop(string $string, string $needle, bool $ignoreCase = null, int $limit = -1) : string
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $string) return $string;
        if ('' === $needle) return $string;

        $result = $string;

        $fnStrlen = static::str_mbfunc('strlen');
        $fnSubstr = static::str_mbfunc('substr');
        $fnStrpos = $ignoreCase
            ? static::str_mbfunc('stripos')
            : static::str_mbfunc('strpos');

        $pos = $fnStrpos($result, $needle);

        while ( $pos === 0 ) {
            if (! $limit--) {
                break;
            }

            $result = $fnSubstr($result,
                $fnStrlen($needle)
            );

            $pos = $fnStrpos($result, $needle);
        }

        return $result;
    }

    /**
     * Обрезает у строки подстроку с конца (rtrim, только для строк а не букв)
     */
    public static function str_rcrop(string $string, string $needle, bool $ignoreCase = null, int $limit = -1) : string
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $string) return $string;
        if ('' === $needle) return $string;

        $result = $string;

        $fnStrlen = static::str_mbfunc('strlen');
        $fnSubstr = static::str_mbfunc('substr');
        $fnStrrpos = $ignoreCase
            ? static::str_mbfunc('strripos')
            : static::str_mbfunc('strrpos');


        $pos = $fnStrrpos($result, $needle);

        while ( $pos === ($fnStrlen($result) - $fnStrlen($needle)) ) {
            if (! $limit--) {
                break;
            }

            $result = $fnSubstr($result, 0, $pos);

            $pos = $fnStrrpos($result, $needle);
        }

        return $result;
    }

    /**
     * Обрезает у строки подстроки с обеих сторон (trim, только для строк а не букв)
     */
    public static function str_crop(string $string, $needles, bool $ignoreCase = null, int $limit = -1) : string
    {
        $needles = is_array($needles)
            ? $needles
            : ($needles ? [ $needles ] : []);

        if (! $needles) {
            return $string;
        }

        $needleRcrop = $needleLcrop = array_shift($needles);

        if ($needles) $needleRcrop = array_shift($needles);

        $result = $string;
        $result = static::str_lcrop($result, $needleLcrop, $ignoreCase, $limit);
        $result = static::str_rcrop($result, $needleRcrop, $ignoreCase, $limit);

        return $result;
    }


    /**
     * Добавляет подстроку в начале строки
     */
    public static function str_unltrim(string $string, string $repeat = null, int $times = null) : string
    {
        $repeat = $repeat ?? '';
        $times = $times ?? 1;

        if ('' === $repeat) return $string;

        $times = max(0, $times);

        $result = str_repeat($repeat, $times) . $string;

        return $result;
    }

    /**
     * Добавляет подстроку в конце строки
     */
    public static function str_unrtrim(string $string, string $repeat = null, int $times = null) : string
    {
        $repeat = $repeat ?? '';
        $times = $times ?? 1;

        if ('' === $repeat) return $string;

        $times = max(0, $times);

        $result = $string . str_repeat($repeat, $times);

        return $result;
    }

    /**
     * Оборачивает строку в подстроки, например в кавычки
     */
    public static function str_untrim(string $string, $repeats, int $times = null) : string
    {
        $repeats = is_array($repeats)
            ? $repeats
            : ($repeats ? [ $repeats ] : []);

        if (! $repeats) {
            return $string;
        }

        $repeatUnrtrim = $repeatUnltrim = array_shift($repeats);

        if ($repeats) $repeatUnrtrim = array_shift($repeats);

        $result = $string;
        $result = static::str_unltrim($result, $repeatUnltrim, $times);
        $result = static::str_unrtrim($result, $repeatUnrtrim, $times);

        return $result;
    }


    /**
     * Добавляет подстроку в начало строки, если её уже там нет
     */
    public static function str_prepend(string $string, string $prepend, bool $ignoreCase = null) : string
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $prepend) return $string;

        $fnStrpos = $ignoreCase
            ? static::str_mbfunc('stripos')
            : static::str_mbfunc('strpos');

        $result = 0 === $fnStrpos($string, $prepend)
            ? $string
            : $prepend . $string;

        return $result;
    }

    /**
     * Добавляет подстроку в конец строки, если её уже там нет
     */
    public static function str_append(string $string, string $append, bool $ignoreCase = null) : string
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $append) return $string;

        $fnStrlen = static::str_mbfunc('strlen');
        $fnStrrpos = $ignoreCase
            ? static::str_mbfunc('strripos')
            : static::str_mbfunc('strrpos');

        $result = (($fnStrlen($string) - $fnStrlen($append)) === $fnStrrpos($string, $append))
            ? $string
            : $string . $append;

        return $result;
    }

    /**
     * Оборачивает строку в подстроки, если их уже там нет
     */
    public static function str_wrap(string $string, $wraps, bool $ignoreCase = null) : string
    {
        $wraps = is_array($wraps)
            ? $wraps
            : ($wraps ? [ $wraps ] : []);

        if (! $wraps) {
            return $string;
        }

        $wrapAppend = $wrapPrepend = array_shift($wraps);

        if ($wraps) $wrapAppend = array_shift($wraps);

        $result = $string;
        $result = static::str_prepend($result, $wrapPrepend, $ignoreCase);
        $result = static::str_append($result, $wrapAppend, $ignoreCase);

        return $result;
    }


    /**
     * > gzhegow, str_replace с поддержкой limit замен
     */
    public static function str_replace_limit(
        $search, $replace, $subject, int $limit = null,
        int &$count = null
    ) : string
    {
        $count = null;

        if ((null !== $limit) && ($limit <= 0)) {
            return $subject;

        } elseif (! isset($limit)) {
            $result = str_replace($search, $replace, $subject, $count);

            return $result;
        }

        $occurrences = substr_count($subject, $search);

        if ($occurrences === 0) {
            return $subject;

        } elseif ($occurrences <= $limit) {
            $result = str_replace($search, $replace, $subject, $count);

            return $result;
        }

        $position = 0;
        for ( $i = 0; $i < $limit; $i++ ) {
            $position = strpos($subject, $search, $position) + strlen($search);
        }

        $substring = substr($subject, 0, $position + 1);

        $substring = str_replace($search, $replace, $substring, $count);

        $result = substr_replace($subject, $substring, 0, $position + 1);

        return $result;
    }


    /**
     * 'the Space case'
     */
    public static function str_space(string $string) : string
    {
        if ('' === $string) return '';

        $result = $string;

        $regex = static::str_mb()
            ? '/[^\p{L}\d ]+/iu'
            : '/[^a-z\d ]+/i';

        $result = preg_replace($regex, ' ', $result);

        $regex = static::str_mb()
            ? '/[ ]*\p{Lu}/u'
            : '/[ ]*[A-Z]/';

        $result = preg_replace($regex, ' $0', $result);

        $result = ltrim($result, ' ');

        return $result;
    }

    /**
     * 'the_Snake_case'
     */
    public static function str_snake(string $string) : string
    {
        if ('' === $string) return '';

        $result = $string;

        $regex = static::str_mb()
            ? '/[^\p{L}\d_]+/iu'
            : '/[^a-z\d_]+/i';

        $result = preg_replace($regex, '_', $result);

        $regex = static::str_mb()
            ? '/[_]*\p{Lu}/u'
            : '/[_]*[A-Z]/';

        $result = preg_replace($regex, '_$0', $result);

        $result = ltrim($result, '_');

        return $result;
    }

    /**
     * 'theCamelCase'
     */
    public static function str_camel(string $string) : string
    {
        if ('' === $string) return '';

        $result = $string;

        $regex = static::str_mb()
            ? '/[^\p{L}\d]+([\p{L}\d])/iu'
            : '/[^a-z\d]+([a-z\d])/i';

        $result = preg_replace_callback($regex, function ($m) {
            return static::str_mbfunc('strtoupper')($m[ 1 ]);
        }, $result);

        $result = static::str_lcfirst($result);

        return $result;
    }

    /**
     * 'ThePascalCase'
     */
    public static function str_pascal(string $string) : string
    {
        if ('' === $string) return '';

        $result = $string;

        $regex = static::str_mb()
            ? '/[^\p{L}\d]+([\p{L}\d])/iu'
            : '/[^a-z\d]+([a-z\d])/i';

        $result = preg_replace_callback($regex, function ($m) {
            return static::str_mbfunc('strtoupper')($m[ 1 ]);
        }, $result);

        $result = static::str_ucfirst($result);

        return $result;
    }
}
