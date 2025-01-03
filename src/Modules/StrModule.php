<?php
/**
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Str\Slugger\Slugger;
use Gzhegow\Lib\Modules\Str\Inflector\Inflector;
use Gzhegow\Lib\Modules\Str\Slugger\SluggerInterface;
use Gzhegow\Lib\Modules\Str\Inflector\InflectorInterface;


class StrModule
{
    /**
     * @var InflectorInterface
     */
    protected $inflector;
    /**
     * @var SluggerInterface
     */
    protected $slugger;

    /**
     * @var bool
     */
    protected $mbMode = false;


    public function __construct()
    {
        $mbMode = extension_loaded('mbstring');

        $this->inflector = new Inflector();
        $this->slugger = new Slugger();

        $this->mbMode = $mbMode;
    }


    public function inflector_static(InflectorInterface $inflector = null) : InflectorInterface
    {
        if (null !== $inflector) {
            $last = $this->inflector;

            $current = $inflector;

            $this->inflector = $current;

            $result = $last;
        }

        $result = $result ?? $this->inflector;

        return $result;
    }

    public function inflector() : InflectorInterface
    {
        return $this->inflector_static();
    }


    public function slugger_static(SluggerInterface $slugger = null) : SluggerInterface
    {
        if (null !== $slugger) {
            $last = $this->slugger;

            $current = $slugger;

            $this->slugger = $current;

            $result = $last;
        }

        $result = $result ?? $this->slugger;

        return $result;
    }

    public function slugger() : SluggerInterface
    {
        return $this->slugger_static();
    }


    public function mb_mode_static(bool $mbMode = null) : bool
    {
        if (null !== $mbMode) {
            if ($mbMode) {
                if (! extension_loaded('mbstring')) {
                    throw new RuntimeException(
                        'Unable to enable `mb_mode` due to `mbstring` extension is missing'
                    );
                }
            }

            $last = $this->mbMode;

            $current = $mbMode;

            $this->mbMode = $current;

            $result = $last;
        }

        $result = $result ?? $this->mbMode;

        return $result;
    }


    /**
     * @param callable|callable-string|null $fn
     */
    public function mb(string $fn = null, ...$args)
    {
        if (null === $fn) {
            $result = $this->mb_mode_static();

        } else {
            $_fn = $this->mb_mode_static()
                ? 'mb_' . $fn
                : $fn;

            $result = $_fn(...$args);
        }

        return $result;
    }

    /**
     * @param callable|callable-string $fn
     *
     * @return callable
     */
    public function mb_func(string $fn)
    {
        if (! $this->mb_mode_static()) {
            return $fn;
        }

        $result = null;

        switch ( $fn ):
            case 'str_split':
                $result = (PHP_VERSION_ID > 74000)
                    ? 'mb_str_split'
                    : [ Lib::mb(), 'str_split' ];

                break;

            default:
                $result = 'mb_' . $fn;

                break;

        endswitch;

        return $result;
    }


    public function is_utf8(string $str) : bool
    {
        return preg_match('//u', $str) === 1;
    }


    public function lines(string $text) : array
    {
        $lines = explode("\n", $text);

        foreach ( $lines as $i => $line ) {
            $line = rtrim($line, PHP_EOL);

            $lines[ $i ] = $line;
        }

        return $lines;
    }

    public function eol(string $text, array &$lines = null) : string
    {
        $lines = $this->lines($text);

        $output = implode("\n", $lines);

        return $output;
    }


    /**
     * возвращает число символов в строке
     */
    public function strlen($value) : int
    {
        if (! is_string($value)) {
            return 0;
        }

        if ('' === $value) {
            return 0;
        }

        $len = $this->mb_mode_static()
            ? mb_strlen($value)
            : count(preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY));

        return $len;
    }

    /**
     * возвращает размер строки в байтах
     */
    public function strsize($value) : int
    {
        if (! is_string($value)) {
            return 0;
        }

        if ('' === $value) {
            return 0;
        }

        // > gzhegow, function results always are the same
        // $size = $this->mb_mode_static()
        //     ? mb_strlen($value, '8bit')
        //     : strlen($value);

        $size = strlen($value);

        return $size;
    }


    /**
     * заменяет все буквы на малые
     */
    public function lower(string $string, string $mb_encoding = null) : string
    {
        if ($this->mb_mode_static()) {
            $mbEncodingArgs = [];
            if (null !== $mb_encoding) {
                $mbEncodingArgs[] = $mb_encoding;
            }

            $result = mb_strtolower($string, ...$mbEncodingArgs);

        } else {
            if ($this->is_utf8($string)) {
                throw new RuntimeException(
                    'The `string` contains UTF-8 symbols, but `mb_mode_static()` returns that multibyte features is disabled'
                );
            }

            $result = strtolower($string);
        }

        return $result;
    }

    /**
     * заменяет все буквы на большие
     */
    public function upper(string $string, string $mb_encoding = null) : string
    {
        if ($this->mb_mode_static()) {
            $mbEncodingArgs = [];
            if (null !== $mb_encoding) {
                $mbEncodingArgs[] = $mb_encoding;
            }

            $result = mb_strtoupper($string, ...$mbEncodingArgs);

        } else {
            if ($this->is_utf8($string)) {
                throw new RuntimeException(
                    'The `string` contains UTF-8 symbols, but `mb_mode_static()` returns that multibyte features is disabled'
                );
            }

            $result = strtoupper($string);
        }

        return $result;
    }


    /**
     * пишет слово с малой буквы
     */
    public function lcfirst(string $string, string $mb_encoding = null) : string
    {
        if ($this->mb_mode_static()) {
            $result = Lib::mb()->lcfirst($string, $mb_encoding);

        } else {
            if ($this->is_utf8($string)) {
                throw new RuntimeException(
                    'The `string` contains UTF-8 symbols, but `mb_mode_static()` returns that multibyte features is disabled'
                );
            }

            $result = lcfirst($string);
        }

        return $result;
    }

    /**
     * пишет слово с большой буквы
     */
    public function ucfirst(string $string, string $mb_encoding = null) : string
    {
        if ($this->mb_mode_static()) {
            $result = Lib::mb()->ucfirst($string, $mb_encoding);

        } else {
            if ($this->is_utf8($string)) {
                throw new RuntimeException(
                    'The `string` contains UTF-8 symbols, but `mb_mode_static()` returns that multibyte features is disabled'
                );
            }

            $result = ucfirst($string);
        }

        return $result;
    }


    /**
     * пишет каждое слово в предложении с малой буквы
     */
    public function lcwords(string $string, string $separators = " \t\r\n\f\v", string $mb_encoding = null) : string
    {
        $regex = '/(^|[' . preg_quote($separators, '/') . '])(\w)/u';

        $result = preg_replace_callback(
            $regex,
            function ($m) use ($mb_encoding) {
                $first = $m[ 1 ];
                $last = $this->lcfirst($m[ 2 ], $mb_encoding);

                return "{$first}{$last}";
            },
            $string
        );

        return $result;
    }

    /**
     * пишет каждое слово в предложении с большой буквы
     */
    public function ucwords(string $string, string $separators = " \t\r\n\f\v", string $mb_encoding = null) : string
    {
        $regex = '/(^|[' . preg_quote($separators, '/') . '])(\w)/u';

        $result = preg_replace_callback(
            $regex,
            function ($m) use ($mb_encoding) {
                $first = $m[ 1 ];
                $last = $this->ucfirst($m[ 2 ], $mb_encoding);

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
    public function starts(string $string, string $needle, bool $ignoreCase = null) : ?string
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $string) return null;
        if ('' === $needle) return $string;

        $fnStrlen = $this->mb_func('strlen');
        $fnSubstr = $this->mb_func('substr');
        $fnStrpos = $ignoreCase
            ? $this->mb_func('stripos')
            : $this->mb_func('strpos');

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
    public function ends(string $string, string $needle, bool $ignoreCase = null) : ?string
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $string) return null;
        if ('' === $needle) return $string;

        $fnStrlen = $this->mb_func('strlen');
        $fnSubstr = $this->mb_func('substr');
        $fnStrrpos = $ignoreCase
            ? $this->mb_func('strripos')
            : $this->mb_func('strrpos');

        $pos = $fnStrrpos($string, $needle);

        $result = $pos === $fnStrlen($string) - $fnStrlen($needle)
            ? $fnSubstr($string, 0, $pos)
            : null;

        return $result;
    }

    /**
     * ищет подстроку в строке и разбивает по ней результат
     */
    public function contains(string $string, string $needle, bool $ignoreCase = null, int $limit = null) : array
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $string) return [];
        if ('' === $needle) return [ $string ];

        $strCase = $ignoreCase
            ? str_ireplace($needle, $needle, $string)
            : $string;

        $result = [];

        $fnStrpos = $ignoreCase
            ? $this->mb_func('stripos')
            : $this->mb_func('strpos');

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
    public function lcrop(string $string, string $lcrop, bool $ignoreCase = null, int $limit = -1) : string
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $string) return $string;
        if ('' === $lcrop) return $string;

        $result = $string;

        $fnStrlen = $this->mb_func('strlen');
        $fnSubstr = $this->mb_func('substr');
        $fnStrpos = $ignoreCase
            ? $this->mb_func('stripos')
            : $this->mb_func('strpos');

        $pos = $fnStrpos($result, $lcrop);

        while ( $pos === 0 ) {
            if (! $limit--) {
                break;
            }

            $result = $fnSubstr($result,
                $fnStrlen($lcrop)
            );

            $pos = $fnStrpos($result, $lcrop);
        }

        return $result;
    }

    /**
     * Обрезает у строки подстроку с конца (rtrim, только для строк а не букв)
     */
    public function rcrop(string $string, string $rcrop, bool $ignoreCase = null, int $limit = -1) : string
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $string) return $string;
        if ('' === $rcrop) return $string;

        $result = $string;

        $fnStrlen = $this->mb_func('strlen');
        $fnSubstr = $this->mb_func('substr');
        $fnStrrpos = $ignoreCase
            ? $this->mb_func('strripos')
            : $this->mb_func('strrpos');


        $pos = $fnStrrpos($result, $rcrop);

        while ( $pos === ($fnStrlen($result) - $fnStrlen($rcrop)) ) {
            if (! $limit--) {
                break;
            }

            $result = $fnSubstr($result, 0, $pos);

            $pos = $fnStrrpos($result, $rcrop);
        }

        return $result;
    }

    /**
     * Обрезает у строки подстроки с обеих сторон (trim, только для строк а не букв)
     */
    public function crop(string $string, $crops, bool $ignoreCase = null, int $limit = -1) : string
    {
        $crops = is_array($crops)
            ? $crops
            : ($crops ? [ $crops ] : []);

        if (! $crops) {
            return $string;
        }

        $needleRcrop = $needleLcrop = array_shift($crops);

        if ($crops) $needleRcrop = array_shift($crops);

        $result = $string;
        $result = $this->lcrop($result, $needleLcrop, $ignoreCase, $limit);
        $result = $this->rcrop($result, $needleRcrop, $ignoreCase, $limit);

        return $result;
    }


    /**
     * Добавляет подстроку в начало строки, если её уже там нет
     */
    public function unlcrop(string $string, string $lcrop, int $times = null, bool $ignoreCase = null) : string
    {
        $times = $times ?? 1;
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $lcrop) return $string;
        if ($times < 1) $times = 1;

        $result = $string;
        $result = $this->lcrop($result, $lcrop, $ignoreCase);
        $result = str_repeat($lcrop, $times) . $result;

        return $result;
    }

    /**
     * Добавляет подстроку в конец строки, если её уже там нет
     */
    public function unrcrop(string $string, string $rcrop, int $times = null, bool $ignoreCase = null) : string
    {
        $times = $times ?? 1;
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $rcrop) return $string;
        if ($times < 1) $times = 1;

        $result = $string;
        $result = $this->rcrop($result, $rcrop, $ignoreCase);
        $result = $result . str_repeat($rcrop, $times);

        return $result;
    }

    /**
     * Оборачивает строку в подстроки, если их уже там нет
     *
     * @param string|string[] $crops
     * @param int|int[]       $times
     */
    public function uncrop(string $string, $crops, $times = null, bool $ignoreCase = null) : string
    {
        $times = $times ?? 1;

        $_crops = (array) $crops;
        $_times = (array) $times;

        if (! $_crops) {
            return $string;
        }

        $result = $string;
        $result = $this->unlcrop($result, $_crops[ 0 ], $_times[ 0 ], $ignoreCase);
        $result = $this->unrcrop($result, $_crops[ 1 ] ?? $_crops[ 0 ], $_times[ 1 ] ?? $_times[ 0 ], $ignoreCase);

        return $result;
    }


    /**
     * > gzhegow, str_replace с поддержкой limit замен
     */
    public function replace_limit(
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
     * 'theCamelCase'
     */
    public function camel(string $string) : string
    {
        if ('' === $string) return '';

        $result = $string;

        $regex = '/[^\p{L}\d]+([\p{L}\d])/iu';

        $result = preg_replace_callback($regex, function ($m) {
            return $this->mb_func('strtoupper')($m[ 1 ]);
        }, $result);

        $result = $this->lcfirst($result);

        return $result;
    }

    /**
     * 'ThePascalCase'
     */
    public function pascal(string $string) : string
    {
        if ('' === $string) return '';

        $result = $string;

        $regex = '/[^\p{L}\d]+([\p{L}\d])/iu';

        $result = preg_replace_callback($regex, function ($m) {
            return $this->mb_func('strtoupper')($m[ 1 ]);
        }, $result);

        $result = $this->ucfirst($result);

        return $result;
    }


    /**
     * 'the Space case'
     */
    public function space(string $string) : string
    {
        if ('' === $string) return '';

        $result = $string;

        $regex = '/[^\p{L}\d ]+/iu';

        $result = preg_replace($regex, ' ', $result);

        $regex = '/(?<=[^\p{Lu} ])(?=\p{Lu})/u';

        $result = preg_replace($regex, ' $2', $result);

        return $result;
    }

    /**
     * 'the_Snake_case'
     */
    public function snake(string $string) : string
    {
        if ('' === $string) return '';

        $result = $string;

        $regex = '/[^\p{L}\d_]+/iu';

        $result = preg_replace($regex, '_', $result);

        $regex = '/(?<=[^\p{Lu}_])(?=\p{Lu})/u';

        $result = preg_replace($regex, '_$2', $result);

        return $result;
    }

    /**
     * 'the-Kebab-case'
     */
    public function kebab(string $string) : string
    {
        if ('' === $string) return '';

        $result = $string;

        $regex = '/[^\p{L}\d-]+/iu';

        $result = preg_replace($regex, '-', $result);

        $regex = '/(?<=[^\p{Lu}-])(?=\p{Lu})/u';

        $result = preg_replace($regex, '-', $result);

        return $result;
    }


    /**
     * 'the space case'
     */
    public function space_lower(string $string) : string
    {
        $result = $string;
        $result = $this->space($result);
        $result = $this->lower($result);

        return $result;
    }

    /**
     * 'the_snake_case'
     */
    public function snake_lower(string $string) : string
    {
        $result = $string;
        $result = $this->snake($result);
        $result = $this->lower($result);

        return $result;
    }

    /**
     * 'the-kebab-case'
     */
    public function kebab_lower(string $string) : string
    {
        $result = $string;
        $result = $this->kebab($result);
        $result = $this->lower($result);

        return $result;
    }


    /**
     * 'THE SPACE CASE'
     */
    public function space_upper(string $string) : string
    {
        $result = $string;
        $result = $this->space($result);
        $result = $this->upper($result);

        return $result;
    }

    /**
     * 'THE_SNAKE_CASE'
     */
    public function snake_upper(string $string) : string
    {
        $result = $string;
        $result = $this->snake($result);
        $result = $this->upper($result);

        return $result;
    }

    /**
     * 'THE-KEBAB-CASE'
     */
    public function kebab_upper(string $string) : string
    {
        $result = $string;
        $result = $this->kebab($result);
        $result = $this->upper($result);

        return $result;
    }
}
