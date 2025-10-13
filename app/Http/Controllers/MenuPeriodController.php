<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MenuPeriodController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        return view('menu_periods.index');
    }

    public function create()
    {
        return view('menu_periods.create');
    }

    public function edit($id)
    {
        return view('menu_periods.edit')->with('id', $id);
    }

    public function delete($id)
    {
        return view('menu_periods.delete')->with('id', $id);
    }
}
