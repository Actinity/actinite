<?php
namespace Actinity\Actinite\Http\Controllers;

use Actinity\Actinite\Services\TypeService;

class TypeController
    extends Controller
{
    public function index()
    {
        return TypeService::all();
    }
}
