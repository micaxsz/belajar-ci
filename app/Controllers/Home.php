<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\DiskonModel;
class Home extends BaseController
{
    protected $productModel;
    protected $diskonModel;

    function __construct()
    {
        helper(['number', 'form']);
        $this->productModel = new ProductModel();
        $this->diskonModel = new DiskonModel();
    }
    public function index()
    {
        $today = date('Y-m-d');
        $activeDiskon = $this->diskonModel->where('tanggal', $today)->first();

        return view('v_home', [
            'products' => $this->productModel->findAll(),
            'activeDiskon' => $activeDiskon
        ]);
    }
}
