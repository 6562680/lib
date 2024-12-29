<?php

namespace Gzhegow\Lib\Traits;

use Gzhegow\Lib\Lib;


trait CanTraitBoot
{
    protected static function __traitBoot(array $args = [])
    {
        if (isset(static::$__traitBoot[ static::class ])) {
            return;
        }

        $theParse = Lib::parse();
        $thePhp = Lib::php();

        $traits = $thePhp->class_uses_with_parents(static::class, true);

        foreach ( $traits as $trait ) {
            $fn = '__boot' . $theParse->struct_basename($trait);

            if (method_exists(static::class, $fn)) {
                call_user_func_array(static::class . "::{$fn}", $args);
            }
        }

        static::$__traitBoot[ static::class ] = true;
    }

    /**
     * @var array<class-string<static>, bool>
     */
    protected static $__traitBoot = [];
}
