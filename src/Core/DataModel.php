<?php
namespace Actinity\Actinite\Core;
use Actinity\Actinite\Services\Parser;

class DataModel
    extends RawDataModel
{
    public function __get($key)
    {
        $parse = true;
        if(substr($key,0,5,) === 'raw__') {
            $parse = false;
            $key = substr($key,5);
        }

        $value = parent::__get($key);
        if($parse && $value && is_string($value)) {
            return Parser::output($value);
        }
        return $value;
    }

}
