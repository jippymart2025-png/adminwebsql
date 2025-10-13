<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('mart.index');
    }

    public function create()
    {
        return view('mart.create');
    }

    public function edit($id)
    {
        return view('mart.edit')->with('id', $id);
    }

    public function view($id)
    {
        return view('mart.view')->with('id', $id);
    }

    public function foods($id)
    {
        return view('martItems.index')->with('id', $id);
    }

    public function orders($id)
    {
        return view('orders.index')->with('id', $id);
    }
}

