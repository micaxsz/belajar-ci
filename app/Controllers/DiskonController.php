<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DiskonModel;

class DiskonController extends BaseController
{
    protected $diskonModel;

    public function __construct()
    {
        $this->diskonModel = new DiskonModel();
    }

    public function index()
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to(base_url());
        }

        $data = [
            'diskon' => $this->diskonModel->findAll()
        ];
        return view('v_diskon', $data);
    }

    public function create()
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to(base_url());
        }

        $rules = [
            'tanggal' => [
                'rules' => 'required|is_unique[discount.tanggal]',
                'errors' => [
                    'required' => 'Tanggal harus diisi.',
                    'is_unique' => 'The tanggal field must contain a unique value.'
                ]
            ],
            'nominal' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Nominal harus diisi.',
                    'numeric' => 'Nominal harus berupa angka.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator->getErrors());
        }

        $this->diskonModel->insert([
            'tanggal' => $this->request->getPost('tanggal'),
            'nominal' => $this->request->getPost('nominal')
        ]);

        return redirect()->to(base_url('diskon'))->with('success', 'Data diskon berhasil ditambahkan.');
    }

    public function edit($id)
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to(base_url());
        }

        $rules = [
            'nominal' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Nominal harus diisi.',
                    'numeric' => 'Nominal harus berupa angka.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator->getErrors());
        }

        $this->diskonModel->update($id, [
            'nominal' => $this->request->getPost('nominal')
        ]);

        return redirect()->to(base_url('diskon'))->with('success', 'Data diskon berhasil diubah.');
    }

    public function delete($id)
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to(base_url());
        }

        $this->diskonModel->delete($id);
        return redirect()->to(base_url('diskon'))->with('success', 'Data diskon berhasil dihapus.');
    }
}
