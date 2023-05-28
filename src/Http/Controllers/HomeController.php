<?php
namespace Actinity\Actinite\Http\Controllers;

class HomeController
	extends Controller
{
	public function index()
	{
		return view('actinite::any');
	}
}