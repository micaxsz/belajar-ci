<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\RajaOngkirService;

use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;


class TransaksiController extends BaseController
{
    protected $cart;
    protected $transactionModel;
    protected $transactionDetailModel;
    public function __construct()
    {
        helper(['number', 'form']);
        $this->cart = service('cart');
        $this->transactionModel = new TransactionModel();
        $this->transactionDetailModel = new TransactionDetailModel();
    }
    public function index()
    {
        $data = [
            'items' => $this->cart->contents(),
            'total' => $this->cart->total()
        ];

        return view('v_keranjang', $data);
    }

    public function cart_add()
    {
        $this->cart->insert([
            'id' => $this->request->getPost('id'),
            'qty' => 1,
            'price' => $this->request->getPost('harga'),
            'name' => $this->request->getPost('nama'),
            'options' => [
                'foto' => $this->request->getPost('foto')
            ]
        ]);

        session()->setFlashdata(
            'success',
            'Produk berhasil ditambahkan ke keranjang. 
	    <a href="' . base_url('keranjang') . '">Lihat</a>'
        );

        return redirect()->to(base_url('/'));
    }
    public function cart_edit()
    {
        $i = 1;
        foreach ($this->cart->contents() as $item) {
            $qty = $this->request->getPost('qty' . $i++);

            $this->cart->update([
                'rowid' => $item['rowid'],
                'qty' => $qty
            ]);
        }

        session()->setFlashdata(
            'success',
            'Keranjang berhasil diperbarui'
        );

        return redirect()->to(base_url('keranjang'));
    }

    public function cart_delete($rowid)
    {
        $this->cart->remove($rowid);

        session()->setFlashdata(
            'success',
            'Produk berhasil dihapus dari keranjang'
        );

        return redirect()->to(base_url('keranjang'));
    }

    public function cart_clear()
    {
        $this->cart->destroy();

        session()->setFlashdata(
            'success',
            'Keranjang berhasil dikosongkan'
        );

        return redirect()->to(base_url('keranjang'));
    }

    public function checkout()
    {
        $data = [
            'items' => $this->cart->contents(),
            'total' => $this->cart->total()
        ];

        return view('v_checkout', $data);
    }

    public function destinations()
    {
        $search = $this->request->getGet('q');

        $service = new RajaOngkirService();
        $response = $service->getDestination($search);

        $results = [];
        $data = $response['data'] ?? [];

        foreach ($data as $item) {
            $results[] = [
                'id' => $item['id'],
                'text' => $item['label']
            ];
        }

        return $this->response->setJSON([
            'results' => $results
        ]);
    }

    public function costs()
    {
        $origin = '64999';
        $destination = $this->request->getGet('destination');
        $weight = 1000;
        $courier = 'jne';

        $service = new RajaOngkirService();
        $response = $service->getCost($origin, $destination, $weight, $courier);

        $results = [];
        $data = $response['data'] ?? [];

        foreach ($data as $item) {
            // Jika format Komerce (flattened), setiap $item langsung berisi info layanan dan cost
            if (isset($item['cost']) && !is_array($item['cost'])) {
                $results[] = [
                    'service'     => $item['service'] ?? '',
                    'description' => $item['description'] ?? '',
                    'cost'        => $item['cost'],
                    'etd'         => $item['etd'] ?? '-',
                ];
            } 
            // Jika format RajaOngkir standar, ada array 'costs' di dalam setiap $item (kurir)
            elseif (isset($item['costs']) && is_array($item['costs'])) {
                foreach ($item['costs'] as $costItem) {
                    if (isset($costItem['cost']) && is_array($costItem['cost'])) {
                        foreach ($costItem['cost'] as $detail) {
                            $results[] = [
                                'service'     => $costItem['service'] ?? '',
                                'description' => $costItem['description'] ?? '',
                                'cost'        => $detail['value'] ?? 0,
                                'etd'         => $detail['etd'] ?? '-',
                            ];
                        }
                    }
                }
            }
        }

        return $this->response->setJSON($results);
    }
    public function buy()
    {
        $cartItems = $this->cart->contents();

        if (empty($cartItems)) {
            return redirect()->back();
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $diskon = (int) session()->get('active_diskon');

        $subtotal = 0;
        foreach ($cartItems as $item) {
            $hargaDiskon = max(0, (int)$item['price'] - $diskon);
            $subtotal += $item['qty'] * $hargaDiskon;
        }

        $ongkir = (int) $this->request->getPost('ongkir');

        $transaction = [
            'username' => $this->request->getPost('username'),
            'alamat' => $this->request->getPost('alamat'),
            'ongkir' => $ongkir,
            'total_harga' => $subtotal + $ongkir,
            'status' => 0,
        ];

        // insert transaction
        if (!$this->transactionModel->insert($transaction)) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Gagal membuat transaksi');
        }

        $transactionId = $this->transactionModel->getInsertID();

        // insert transaction detail
        foreach ($cartItems as $item) {
            $hargaDiskon = max(0, (int)$item['price'] - $diskon);
            $this->transactionDetailModel->insert([
                'transaction_id' => $transactionId,
                'product_id'     => $item['id'],
                'jumlah'         => $item['qty'],
                'diskon'         => $diskon,
                'subtotal_harga' => $item['qty'] * $hargaDiskon
            ]);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->back()->with('error', 'Gagal membuat transaksi');
        }

        //hapus session keranjang belanja 
        $this->cart->destroy();
        return redirect()->to(base_url());
    }

    public function history()
    {
        $username = session()->get('username');
        
        $transactions = $this->transactionModel->where('username', $username)->findAll();

        $transactionIds = !empty($transactions) ? array_column($transactions, 'id') : [];
        $products = !empty($transactionIds)
            ? $this->transactionDetailModel->getProductsByTransactionIds($transactionIds)
            : [];

        $data = [
            'username'     => $username,
            'transactions' => $transactions,
            'products'     => $products
        ];

        return view('v_history', $data);
    }

    public function pembelian()
    {
        $transactions = $this->transactionModel->findAll();

        $transactionIds = !empty($transactions) ? array_column($transactions, 'id') : [];
        $products = !empty($transactionIds)
            ? $this->transactionDetailModel->getProductsByTransactionIds($transactionIds)
            : [];

        $data = [
            'transactions' => $transactions,
            'products'     => $products
        ];

        return view('v_pembelian', $data);
    }

    public function ubah_status($id)
    {
        $transaction = $this->transactionModel->find($id);
        if ($transaction) {
            $newStatus = $transaction['status'] == 0 ? 1 : 0;
            $this->transactionModel->update($id, ['status' => $newStatus]);
            session()->setFlashdata('success', 'Status pesanan berhasil diubah');
        } else {
            session()->setFlashdata('error', 'Pesanan tidak ditemukan');
        }
        
        return redirect()->to(base_url('pembelian'));
    }
}