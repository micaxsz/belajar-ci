<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use CodeIgniter\HTTP\ResponseInterface;

class ProdukController extends BaseController
{
    protected $model;
    private $token;

    function __construct()
    {
        $this->model = new ProductModel();
        $this->token = env('MY_API_KEY');
    }

    private function authenticate(): bool
    {
        $header = $this->request->getHeaderLine('Authorization');

        if (empty($header)) {
            return false;
        }

        if (!preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return false;
        }

        return $matches[1] === $this->token;
    }

    private function unauthorized()
    {
        return $this->response
            ->setStatusCode(401)
            ->setJSON([
                'status'  => false,
                'message' => 'Unauthorized',
            ]);
    }

    // GET api/products
    public function index()
    {
        if (!$this->authenticate()) {
            return $this->unauthorized();
        }

        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 10);

        $products = $this->model->paginate($perPage, 'default', $page);

        return $this->response->setJSON([
            'data' => $products,
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'last_page'    => $this->model->pager->getPageCount(),
                'total_data'   => $this->model->pager->getTotal(),
                'has_next'     => $page < $this->model->pager->getPageCount(),
                'has_prev'     => $page > 1,
            ],
        ]);
    }

    // GET api/products/:id
    public function show($id = null)
    {
        if (!$this->authenticate()) {
            return $this->unauthorized();
        }

        $product = $this->model->find($id);

        if (!$product) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => false,
                'message' => 'Produk tidak ditemukan',
            ]);
        }

        return $this->response->setJSON(['data' => $product]);
    }

    // POST api/products
    public function create()
    {
        if (!$this->authenticate()) {
            return $this->unauthorized();
        }

        $data = $this->request->getJSON(true);

        $this->model->insert($data);

        return $this->response->setStatusCode(201)->setJSON([
            'status'  => true,
            'message' => 'Produk berhasil ditambahkan',
        ]);
    }

    // PUT/PATCH api/products/:id
    public function update($id = null)
    {
        if (!$this->authenticate()) {
            return $this->unauthorized();
        }

        if (!$this->model->find($id)) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => false,
                'message' => 'Produk tidak ditemukan',
            ]);
        }

        $data = $this->request->getJSON(true);

        $this->model->update($id, $data);

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Produk berhasil diperbarui',
        ]);
    }

    // DELETE api/products/:id
    public function delete($id = null)
    {
        if (!$this->authenticate()) {
            return $this->unauthorized();
        }

        if (!$this->model->find($id)) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => false,
                'message' => 'Produk tidak ditemukan',
            ]);
        }

        $this->model->delete($id);

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Produk berhasil dihapus',
        ]);
    }
}
