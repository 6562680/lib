<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf;


class DictOfType extends DictOf
{
    public function __construct(
        string $valueType,
        array $options = []
    )
    {
        $options = []
            + [ 'isOfType' => true ]
            + $options;

        parent::__construct($valueType, null, $options);
    }
}
