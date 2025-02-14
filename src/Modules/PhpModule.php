<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\Interfaces\ToListInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToFloatInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToArrayInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToStringInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToIntegerInterface;
use Gzhegow\Lib\Modules\Php\CallableParser\CallableParser;
use Gzhegow\Lib\Modules\Php\CallableParser\CallableParserInterface;


class PhpModule
{
    /**
     * @var CallableParserInterface
     */
    protected $callableParser;

    /**
     * @var class-string<\LogicException|\RuntimeException>
     */
    protected $throwableClass = LogicException::class;


    public function __construct()
    {
        $this->callableParser = new CallableParser();
    }


    public function callable_parser_static(CallableParserInterface $callableParser = null) : CallableParserInterface
    {
        if (null !== $callableParser) {
            $last = $this->callableParser;

            $current = $callableParser;

            $this->callableParser = $current;

            $result = $last;
        }

        $result = $result ?? $this->callableParser;

        return $result;
    }

    public function callable_parser() : CallableParserInterface
    {
        return $this->callable_parser_static();
    }


    /**
     * @param array|\Countable|null $result
     */
    public function type_countable(&$result, $value) : bool
    {
        $result = null;

        if ($value instanceof \Countable) {
            $result = $value;

            return true;
        }

        if (PHP_VERSION_ID < 70300) {
            return false;
        }

        if (! is_countable($value)) {
            return false;
        }

        $result = $value;

        return true;
    }


    /**
     * @param class-string|null $result
     *
     * @param callable          ...$fnExistsList
     */
    public function type_struct(&$result, $value, bool $useRegex = null, ...$fnExistsList) : bool
    {
        $result = null;

        $useRegex = $useRegex ?? false;
        $fnExistsList = $fnExistsList ?: [
            'class_exists',
            'interface_exists',
            'trait_exists',
        ];

        if (is_object($value)) {
            return ltrim(get_class($value), '\\');
        }

        if (! Lib::type()->string_not_empty($_value, $value)) {
            return false;
        }

        $_value = ltrim($_value, '\\');

        foreach ( $fnExistsList as $fn ) {
            if (call_user_func($fn, $_value)) {
                return $_value;
            }
        }

        if ($useRegex) {
            if (! preg_match(
                '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*$/',
                $_value
            )) {
                return false;
            }
        }

        $result = $_value;

        return true;
    }

    /**
     * @param class-string|null $result
     *
     * @return class-string|null
     */
    public function type_struct_class(&$result, $value, bool $useRegex = null) : bool
    {
        return $this->type_struct($result, $value, $useRegex, 'class_exists');
    }

    /**
     * @param class-string|null $result
     *
     * @return class-string|null
     */
    public function type_struct_interface(&$result, $value, bool $useRegex = null) : bool
    {
        return $this->type_struct($result, $value, $useRegex, 'interface_exists');
    }

    /**
     * @param class-string|null $result
     *
     * @return class-string|null
     */
    public function type_struct_trait(&$result, $value, bool $useRegex = null) : bool
    {
        return $this->type_struct($result, $value, $useRegex, 'trait_exists');
    }


    /**
     * @param class-string|null $result
     *
     * @param callable          ...$fnExistsList
     */
    public function type_struct_fqcn(&$result, $value, bool $useRegex = null, ...$fnExistsList) : bool
    {
        $result = null;

        if (! $this->type_struct($_value, $value, $useRegex, ...$fnExistsList)) {
            return false;
        }

        $_value = '\\' . $_value;

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     *
     * @param callable    ...$fnExistsList
     */
    public function type_struct_namespace(&$result, $value, bool $useRegex = null, ...$fnExistsList) : bool
    {
        $result = null;

        if (! $this->type_struct($_value, $value, $useRegex, ...$fnExistsList)) {
            return false;
        }

        if (false !== strpos($_value, '\\')) {
            $_value = str_replace('\\', '/', $_value);
        }

        if (false === strpos($_value, '/')) {
            $_value = null;

        } else {
            $_value = preg_replace('~[/]+~', '/', $_value);

            $namespace = dirname($_value);
            $namespace = str_replace('/', '\\', $namespace);

            $_value = $namespace;
        }

        $result = $value;

        return true;
    }

    /**
     * @param string|null $result
     *
     * @param callable    ...$fnExistsList
     */
    public function type_struct_basename(&$result, $value, bool $useRegex = null, ...$fnExistsList) : bool
    {
        $result = null;

        if (! $this->type_struct($_value, $value, $useRegex, ...$fnExistsList)) {
            return false;
        }

        if (false !== strpos($_value, '\\')) {
            $_value = str_replace('\\', '/', $_value);
        }

        if (false !== strpos($_value, '/')) {
            $_value = preg_replace('~[/]+~', '/', $_value);

            $_value = basename($_value);
        }

        $result = $_value;

        return $_value;
    }


    /**
     * @param resource|null $result
     */
    public function type_resource(&$result, $value) : bool
    {
        $result = null;

        if (false
            || is_resource($value)
            || (gettype($value) === 'resource (closed)')
        ) {
            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param resource|null $result
     */
    public function type_resource_opened(&$result, $value) : bool
    {
        $result = null;

        if (is_resource($value)) {
            $result = $value;

            return true;
        }

        return false;
    }

    /**
     * @param resource|null $result
     */
    public function type_resource_closed(&$result, $value) : bool
    {
        $result = null;

        if ('resource (closed)' === gettype($value)) {
            $result = $value;

            return true;
        }

        return false;
    }


    /**
     * > метод не всегда возвращает callable, поскольку массив [ 'class', 'method' ] не является callable, если метод публичный
     * > используйте type_callable_array, если собираетесь вызывать метод
     *
     * @param array{ 0: class-string, 1: string }|null $result
     */
    public function type_method_array(&$result, $value) : bool
    {
        return $this->callable_parser()->typeMethodArray($result, $value);
    }

    /**
     * > метод не всегда возвращает callable, поскольку строка 'class->method' не является callable
     * > используйте type_callable_string, если собираетесь вызывать метод
     *
     * @param string|null $result
     */
    public function type_method_string(&$result, $value) : bool
    {
        return $this->callable_parser()->typeMethodString($result, $value);
    }


    /**
     * > в версиях PHP до 8.0.0 публичный метод считался callable, если его проверить даже на имени класса
     * > при этом вызвать MyClass::publicMethod было нельзя, т.к. вызываемым является только MyClass::publicStaticMethod
     *
     * @param callable|null $result
     * @param string|object $newScope
     */
    public function type_callable(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->typeCallable($result, $value, $newScope);
    }


    /**
     * @param callable|\Closure|object|null $result
     */
    public function type_callable_object(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->typeCallableObject($result, $value, $newScope);
    }

    /**
     * @param callable|object|null $result
     */
    public function type_callable_object_closure(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->typeCallableObjectClosure($result, $value, $newScope);
    }

    /**
     * @param callable|object|null $result
     */
    public function type_callable_object_invokable(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->typeCallableObjectInvokable($result, $value, $newScope);
    }


    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $result
     * @param string|object                                            $newScope
     */
    public function type_callable_array(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->typeCallableArray($result, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $result
     * @param string|object                                            $newScope
     */
    public function type_callable_array_method(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->typeCallableArrayMethod($result, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: class-string, 1: string }|null $result
     * @param string|object                                     $newScope
     */
    public function type_callable_array_method_static(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->typeCallableArrayMethodStatic($result, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: object, 1: string }|null $result
     * @param string|object                               $newScope
     */
    public function type_callable_array_method_non_static(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->typeCallableArrayMethodNonStatic($result, $value, $newScope);
    }


    /**
     * @param callable-string|null $result
     */
    public function type_callable_string(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->typeCallableString($result, $value, $newScope);
    }

    /**
     * @param callable-string|null $result
     */
    public function type_callable_string_function(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->typeCallableStringFunction($result, $value, $newScope);
    }

    /**
     * @param callable-string|null $result
     */
    public function type_callable_string_method_static(&$result, $value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->typeCallableStringMethodStatic($result, $value, $newScope);
    }


    /**
     * > is_callable является контекстно-зависимой функцией
     * > будучи вызванной снаружи класса она не покажет методы protected/private
     * > используя $newScope можно это обойти
     *
     * @param string|object $newScope
     */
    public function is_callable($value, $newScope = 'static') : bool
    {
        return $this->callable_parser()->isCallable($value, $newScope);
    }


    /**
     * @return array{
     *     internal: array<string, bool>,
     *     user: array<string, bool>,
     * }
     */
    public function get_defined_functions() : array
    {
        $getDefinedFunctions = get_defined_functions();

        $flipInternal = array_fill_keys($getDefinedFunctions[ 'internal' ] ?? [], true);
        $flipUser = array_fill_keys($getDefinedFunctions[ 'user' ] ?? [], true);

        ksort($flipInternal);
        ksort($flipUser);

        $result = [];
        $result[ 'internal' ] += $flipInternal;
        $result[ 'user' ] += $flipUser;

        return $result;
    }

    /**
     * > метод по-умолчанию не сортирует, а также является контекстно-зависимым
     *
     * @param object|class-string $object_or_class
     * @param object|class-string $newScope
     *
     * @return array<string, bool>
     */
    public function get_class_methods($object_or_class, $newScope = 'static') : array
    {
        $fnGetClassMethods = null;
        if ('static' !== $newScope) {
            $_newScope = null
                ?? $newScope
                ?? new class {
                };

            $fnGetClassMethods = (static function ($object_or_class) {
                return get_class_methods($object_or_class);
            })->bindTo(null, $_newScope);
        }

        $getClassMethods = $fnGetClassMethods
            ? $fnGetClassMethods($object_or_class)
            : get_class_methods($fnGetClassMethods);

        $flip = array_fill_keys($getClassMethods, true);

        ksort($flip);

        $result = $flip;

        return $result;
    }


    /**
     * @return callable|null
     */
    public function get_error_handler() // : ?callable
    {
        $handler = set_error_handler(static function () { });
        restore_error_handler();

        return $handler;
    }

    /**
     * @return callable|null
     */
    public function get_exception_handler() // : ?callable
    {
        $handler = set_exception_handler(static function () { });
        restore_exception_handler();

        return $handler;
    }


    public function to_int($value, array $options = []) : int
    {
        if ($value instanceof ToIntegerInterface) {
            $_value = $value->toInteger($options);

        } else {
            if (false
                || is_array($value)
                || is_object($value)
                || is_resource($value)
                || (gettype($value) === 'resource (closed)')
            ) {
                throw new LogicException(
                    [ 'The `value` should not be array, object or resource', $value ]
                );
            }

            $_value = (int) $value;
        }

        return $_value;
    }

    public function to_float($value, array $options = []) : float
    {
        if ($value instanceof ToFloatInterface) {
            $_value = $value->toFloat($options);

        } else {
            if (false
                || is_array($value)
                || is_object($value)
                || is_resource($value)
                || (gettype($value) === 'resource (closed)')
            ) {
                throw new LogicException(
                    [ 'The `value` should not be array, object or resource', $value ]
                );
            }

            $_value = (float) $value;
        }

        return $_value;
    }

    public function to_string($value, array $options = []) : string
    {
        if ($value instanceof ToStringInterface) {
            $_value = $value->toString($options);

        } else {
            if (false
                || is_array($value)
                || is_object($value)
                || is_resource($value)
                || (gettype($value) === 'resource (closed)')
            ) {
                throw new LogicException(
                    [ 'The `value` should not be array, object or resource', $value ]
                );
            }

            $_value = (string) $value;
        }

        return $_value;
    }

    public function to_array($value, array $options = []) : array
    {
        if ($value instanceof ToArrayInterface) {
            $_value = $value->toArray($options);

        } else {
            if (is_object($value) && ! ($value instanceof \stdClass)) {
                throw new LogicException(
                    [ 'The `value` being the object should be instance of: ' . \stdClass::class, $value ]
                );
            }

            $_value = (array) $value;
        }

        return $_value;
    }

    /**
     * @param callable $fnForceWrap
     */
    public function to_list($value, $fnForceWrap = null, array $options = []) : array
    {
        if (null === $value) {
            throw new LogicException('The `value` should be not null');
        }

        if ('' === $value) {
            throw new LogicException('The `value` should not be an empty string');
        }

        if (is_object($value)) {
            if ($value instanceof ToListInterface) {
                $_value = $value->toList($options);

            } else {
                $_value = [ $value ];
            }

        } elseif (is_array($value)) {
            if ($fnForceWrap) {
                $status = call_user_func_array($fnForceWrap, $value);

                $_value = $status
                    ? [ $value ]
                    : $value;

            } else {
                $_value = $value;
            }

        } else {
            $_value = (array) $value;
        }

        return $_value;
    }


    public function count($value) : ?int
    {
        if (is_array($value)) {
            return count($value);
        }

        if ($this->type_countable($_value, $value)) {
            return count($_value);
        }

        return null;
    }


    /**
     * @param int[]    $results
     * @param callable $fnCmp
     */
    public function cmp($a, $b, array $results = [ 0 ], $fnCmp = null) : ?int
    {
        $result = $fnCmp
            ? $fnCmp($a, $b)
            : ($a <=> $b);

        if (! in_array($result, $results, true)) {
            return null;
        }

        return $result;
    }


    public function debug_backtrace($options = null, $limit = null) : array
    {
        $options = $options ?? DEBUG_BACKTRACE_IGNORE_ARGS;
        if ($options < 0) $options = DEBUG_BACKTRACE_IGNORE_ARGS;

        $limit = $limit ?? 0;
        if ($limit < 0) $limit = 1;

        $result = debug_backtrace($options, $limit);

        return $result;
    }


    public function microtime($date = null) : string
    {
        $mt = microtime();

        [ $sec, $msec ] = explode(' ', $mt);

        if (null === $date) {
            $result = ''
                . $sec
                . Lib::type()->the_decimal_point()
                . str_pad($msec, 6, '0');

        } elseif (is_a($date, '\DateTimeInterface')) {
            $result = ''
                . $date->format('s')
                . Lib::type()->the_decimal_point()
                . str_pad($date->format('u'), 6, '0');

        } else {
            throw new LogicException(
                [ 'The `date` must be instance of \DateTimeInterface', $date ]
            );
        }

        return $result;
    }


    /**
     * @param object|class-string $objectOrClass
     *
     * @return class-string[]
     */
    public function class_uses_with_parents($objectOrClass, bool $recursive = null)
    {
        $recursive = $recursive ?? false;

        $className = $objectOrClass;
        if (is_object($objectOrClass)) {
            $className = get_class($objectOrClass);
        }

        $uses = [];

        $sources = []
            + array_reverse(class_parents($className))
            + [ $className => $className ];

        foreach ( $sources as $sourceClassName ) {
            $uses += $this->class_uses($sourceClassName, $recursive);
        }

        $uses = array_unique($uses);

        return $uses;
    }

    /**
     * @param object|class-string $objectOrClass
     *
     * @return class-string[]
     */
    public function class_uses($objectOrClass, bool $recursive = null)
    {
        $recursive = $recursive ?? false;

        $className = $objectOrClass;
        if (is_object($objectOrClass)) {
            $className = get_class($objectOrClass);
        }

        $uses = class_uses($className) ?: [];

        if ($recursive) {
            foreach ( $uses as $usesItem ) {
                // ! recursion
                $uses += $this->class_uses($usesItem);
            }
        }

        return $uses;
    }


    /**
     * > функция get_object_vars() возвращает все элементы для $this, в том числе protected/private
     * > чтобы получить доступ только к публичным свойствам, её нужно вызвать в обертке
     */
    public function get_object_vars(object $object, bool $public = null) : array
    {
        $public = $public ?? true;

        if ($public === true) {
            $vars = get_object_vars($object);

        } else {
            $fn = (function (object $object) : array {
                return get_object_vars($object);
            })->bindTo($object, $object);

            $vars = $fn();

            if ($public === false) {
                $vars = array_diff_key(
                    $vars,
                    get_object_vars($object)
                );
            }
        }

        return $vars;
    }

    /**
     * > функция property_exists() возвращает все свойства, в том числе protected/private
     * > чтобы получить доступ только к публичным свойствам, нужно прибегнуть к вот такой хитрости
     *
     * @param class-string|object $object_or_class
     */
    public function property_exists($object_or_class, string $property, bool $public = null) : bool
    {
        $public = $public ?? false;

        if (! $public) {
            return property_exists($object_or_class, $property);
        }

        if (is_object($object_or_class)) {
            $vars = $this->get_object_vars($object_or_class, $public);

            return array_key_exists($property, $vars);
        }

        $class = $object_or_class;

        try {
            $rp = new \ReflectionProperty($class, $property);

            if ($rp->isPublic()) {
                return true;
            }
        }
        catch ( \Throwable $e ) {
            return false;
        }

        return false;
    }

    /**
     * > функция method_exists() возвращает true для любых методов, в том числе protected/private
     * > чтобы получить доступ только к публичным свойствам, нужно прибегнуть к вот такой хитрости
     *
     * @param class-string|object $object_or_class
     */
    public function method_exists($object_or_class, string $method, bool $public = null) : bool
    {
        $public = $public ?? false;

        if (! method_exists($object_or_class, $method)) {
            return false;
        }

        if ($public) {
            if (is_object($object_or_class)) {
                if (is_callable([ $object_or_class, $method ])) {
                    return true;
                }
            }

            try {
                $rm = new \ReflectionMethod($object_or_class, $method);

                if ($rm->isPublic()) {
                    return true;
                }
            }
            catch ( \Throwable $e ) {
                return false;
            }
        }

        return false;
    }


    /**
     * @param mixed $data
     */
    public function serialize($data) : ?string
    {
        error_clear_last();

        try {
            $result = serialize($data);
        }
        catch ( \Throwable $e ) {
            $result = null;
        }

        if (error_get_last()) {
            $result = null;
        }

        return $result;
    }

    /**
     * @return mixed|null
     */
    public function unserialize(string $data) // : mixed|null
    {
        error_clear_last();

        try {
            $result = unserialize($data);
        }
        catch ( \Throwable $e ) {
            $result = null;
        }

        if (error_get_last()) {
            $result = null;
        }

        if (is_object($result) && (get_class($result) === '__PHP_Incomplete_Class')) {
            $result = null;
        }

        return $result;
    }


    /**
     * @param callable $fn
     */
    public function fn($fn, array $args = []) : \Closure
    {
        return function (...$arguments) use ($fn, $args) {
            $_args = array_merge($arguments, $args);

            return call_user_func_array($fn, $_args);
        };
    }

    /**
     * @param callable $fn
     */
    public function fn_not($fn, array $args = []) : \Closure
    {
        return function (...$arguments) use ($fn, $args) {
            $_args = array_merge($arguments, $args);

            return ! call_user_func_array($fn, $_args);
        };
    }


    public function dirname(string $path, string $separator = null, int $levels = null) : ?string
    {
        $separator = $separator ?? DIRECTORY_SEPARATOR;
        $levels = $levels ?? 1;

        if ('' === $path) return null;

        $_value = $path;

        $hasSeparator = (false !== strpos($_value, $separator));

        $_value = $hasSeparator
            ? str_replace([ '\\', DIRECTORY_SEPARATOR, $separator ], '/', $_value)
            : str_replace([ '\\', DIRECTORY_SEPARATOR ], '/', $_value);

        $_value = ltrim($_value, '/');

        if (false === strpos($_value, '/')) {
            $_value = null;

        } else {
            $_value = preg_replace('~[/]+~', '/', $_value);

            $_value = dirname($_value, $levels);
            $_value = str_replace('/', $separator, $_value);
        }

        return $_value;
    }


    /**
     * @param class-string<\LogicException|\RuntimeException>|null $throwableClass
     *
     * @return class-string<\LogicException|\RuntimeException>
     */
    public function throwable_class_static(string $throwableClass = null) : string
    {
        if (null !== $throwableClass) {
            if (! (false
                || is_subclass_of($throwableClass, \LogicException::class)
                || is_subclass_of($throwableClass, \RuntimeException::class)
            )) {
                throw new LogicException(
                    [
                        'The `throwableClass` should be class-string that is subclass one of: '
                        . implode('|', [
                            \LogicException::class,
                            \RuntimeException::class,
                        ]),
                        $throwableClass,
                    ]
                );
            }

            $last = $this->throwableClass;

            $current = $throwableClass;

            $result = $last;
        }

        $result = $result ?? $this->throwableClass;

        return $result;
    }

    /**
     * @throws \LogicException|\RuntimeException
     */
    public function throw($throwableOrArg, ...$throwableArgs)
    {
        if (
            ($throwableOrArg instanceof \LogicException)
            || ($throwableOrArg instanceof \RuntimeException)
        ) {
            throw $throwableOrArg;
        }

        array_unshift($throwableArgs, $throwableOrArg);

        $throwableClass = $this->throwable_class_static();

        $trace = property_exists($throwableClass, 'trace')
            ? debug_backtrace()
            : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->throw_new($trace, ...$throwableArgs);
    }

    /**
     * @throws \LogicException|\RuntimeException
     */
    public function throw_new(array $trace = null, ...$throwableArgs)
    {
        $throwableClass = $this->throwable_class_static();

        if (null === $trace) {
            $trace = property_exists($throwableClass, 'trace')
                ? debug_backtrace()
                : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        }

        $throwableArgs = $this->throwable_args(...$throwableArgs);
        $throwableArgs[ 'file' ] = $trace[ 0 ][ 'file' ] ?? '{file}';
        $throwableArgs[ 'line' ] = $trace[ 0 ][ 'line' ] ?? '{line}';
        $throwableArgs[ 'trace' ] = $trace;

        $arguments = [];
        $arguments[] = $throwableArgs[ 'message' ] ?? null;
        $arguments[] = $throwableArgs[ 'code' ] ?? null;
        $arguments[] = $throwableArgs[ 'previous' ] ?? null;

        $e = new $throwableClass(...$arguments);

        $fn = (function () use (&$throwableArgs) {
            foreach ( $throwableArgs as $key => $value ) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
            }
        })->bindTo($e, $e);

        $fn();

        throw $e;
    }

    public function throwable_args(...$throwableArgs) : array
    {
        $len = count($throwableArgs);

        $messageList = null;
        $codeList = null;
        $previousList = null;
        $messageCodeList = null;
        $messageDataList = null;

        $__unresolved = [];

        for ( $i = 0; $i < $len; $i++ ) {
            $arg = $throwableArgs[ $i ];

            if (is_int($arg)) {
                $codeList[ $i ] = $arg;

                continue;
            }

            if (is_string($arg) && ('' !== $arg)) {
                $messageList[ $i ] = $arg;

                continue;
            }

            if (false
                || is_array($arg)
                || $arg instanceof \stdClass
            ) {
                $messageData = (array) $arg;

                $messageString = isset($messageData[ 0 ])
                    ? (string) $messageData[ 0 ]
                    : '';

                if ('' !== $messageString) {
                    unset($messageData[ 0 ]);

                    $messageList[ $i ] = $messageString;
                }

                $messageDataList[ $i ] = $messageData;

                continue;
            }

            if ($arg instanceof \Throwable) {
                $previousList[ $i ] = $arg;

                continue;
            }

            $__unresolved[ $i ] = $arg;
        }

        for ( $i = 0; $i < $len; $i++ ) {
            if (isset($messageList[ $i ])) {
                if (preg_match('/^[a-z](?!.*\s)/i', $messageList[ $i ])) {
                    $messageCodeList[ $i ] = strtoupper($messageList[ $i ]);
                }
            }
        }

        $result = [];

        $result[ 'messageList' ] = $messageList;
        $result[ 'codeList' ] = $codeList;
        $result[ 'previousList' ] = $previousList;

        $result[ 'messageCodeList' ] = $messageCodeList;
        $result[ 'messageDataList' ] = $messageDataList;

        $messageDataList = $messageDataList ?? [];

        $message = $messageList ? end($messageList) : null;
        $code = $codeList ? end($codeList) : null;
        $previous = $previousList ? end($previousList) : null;
        $messageCode = $messageCodeList ? end($messageCodeList) : null;

        $messageData = $messageDataList
            ? array_replace(...$messageDataList)
            : [];

        $messageObject = (object) ([ $message ] + $messageData);

        $result[ 'message' ] = $message;
        $result[ 'code' ] = $code;
        $result[ 'previous' ] = $previous;

        $result[ 'messageCode' ] = $messageCode;
        $result[ 'messageData' ] = $messageData;

        $result[ 'messageObject' ] = $messageObject;

        $result[ '__unresolved' ] = $__unresolved;

        return $result;
    }


    /**
     * @return object{ stack: array }
     */
    public function errors() : object
    {
        static $stack;

        $stack = $stack
            ?? new class {
                public $stack = [];
            };

        return $stack;
    }

    /**
     * @return object{ list: array }
     */
    public function errors_current() : ?object
    {
        $stack = $this->errors();

        $errors = count($stack->stack)
            ? end($stack->stack)
            : null;

        return $errors;
    }

    /**
     * @return object{ list: array }
     */
    public function errors_new() : object
    {
        $errors = new class {
            public $list = [];
        };

        return $errors;
    }

    /**
     * @return object{ list: array }
     */
    public function errors_start(object &$errors = null) : object
    {
        $stack = $this->errors();

        $errors = $this->errors_new();

        $stack->stack[] = $errors;

        return $errors;
    }

    public function errors_end(?object $until) : array
    {
        $stack = $this->errors();

        $errors = $this->errors_new();

        while ( count($stack->stack) ) {
            $current = array_pop($stack->stack);

            foreach ( $current->list as $error ) {
                $errors->list[] = $error;
            }

            if ($current === $until) {
                break;
            }
        }

        return $errors->list;
    }

    public function error($error, $result = null) // : mixed
    {
        $current = $this->errors_current();

        if (null !== $current) {
            $current->list[] = $error;
        }

        return $result;
    }
}
