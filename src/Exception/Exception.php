<?php

namespace Gzhegow\Lib\Exception;

use Gzhegow\Lib\Lib;


class Exception extends \Exception implements
    AggregateExceptionInterface,
    //
    \IteratorAggregate
{
    use ThrowableTrait;

    use AggregateExceptionTrait;


    /**
     * @var string
     */
    public $file;
    /**
     * @var int
     */
    public $line;

    /**
     * @var array
     */
    public $trace;


    public function __construct(...$throwableArgs)
    {
        $args = Lib::php()->throwable_args(...$throwableArgs);

        $this->previousList = array_values($args[ 'previousList' ]);

        parent::__construct(
            $args[ 'message' ],
            $args[ 'code' ],
            $args[ 'previous' ]
        );
    }


    /**
     * @return iterable<string, \Throwable[]>
     */
    public function getIterator()
    {
        /** @var iterable<string, \Throwable[]> $iit */

        $it = new ExceptionIterator([ $this ]);
        $iit = new \RecursiveIteratorIterator($it);

        return $iit;
    }
}
