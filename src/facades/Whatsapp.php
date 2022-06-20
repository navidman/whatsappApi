<?php


namespace navidman\whatsappApi\facades;


use Illuminate\Support\Facades\Facade;

class Whatsapp extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'whatsapp';
    }
}
