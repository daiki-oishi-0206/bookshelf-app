<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class GenreController extends Controller
{
    public function index(): View
    {
        return View('genres.index');
    }
}
