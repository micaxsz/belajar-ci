<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('V_home');
    }

    public function produk(): string
    {
        return view('v_produk');
    }

    public function keranjang(): string
    {
        return view('v_keranjang');
    }

    public function faq(): string
    {
        return view('v_faq');
    }
}
