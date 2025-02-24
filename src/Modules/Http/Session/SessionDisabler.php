<?php

namespace Gzhegow\Lib\Modules\Http\Session;

use Gzhegow\Lib\Modules\HttpModule;
use Gzhegow\Lib\Exception\RuntimeException;


class SessionDisabler implements \ArrayAccess, \Countable
{
    public function offsetExists($offset)
    {
        /** @see HttpModule::static_session() */

        throw new RuntimeException('Native $_SESSION is disabled');
    }

    public function offsetGet($offset)
    {
        /** @see HttpModule::static_session() */

        throw new RuntimeException('Native $_SESSION is disabled');
    }

    public function offsetSet($offset, $value)
    {
        /** @see HttpModule::static_session() */

        throw new RuntimeException('Native $_SESSION is disabled');
    }

    public function offsetUnset($offset)
    {
        /** @see HttpModule::static_session() */

        throw new RuntimeException('Native $_SESSION is disabled');
    }


    public function count()
    {
        /** @see HttpModule::static_session() */

        throw new RuntimeException('Native $_SESSION is disabled');
    }
}
