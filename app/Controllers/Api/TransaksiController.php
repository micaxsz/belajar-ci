<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;
use CodeIgniter\HTTP\ResponseInterface;

class TransaksiController extends BaseController
{
    protected $transactionModel;
    protected $transactionDetailModel;
    private $token;

    function __construct()
    {
        $this->transactionModel       = new TransactionModel();
        $this->transactionDetailModel = new TransactionDetailModel();
        $this->token                  = env('MY_API_KEY');
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

    // GET api/transactions
    public function index()
    {
        if (!$this->authenticate()) {
            return $this->unauthorized();
        }

        $start   = $this->request->getGet('start');
        $end     = $this->request->getGet('end');
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 10);

        $builder = $this->transactionModel->orderBy('created_at', 'DESC');

        if ($start && $end) {
            $builder->where('created_at >=', $start)
                    ->where('created_at <=', $end);
        }

        $transactions = $builder->paginate($perPage, 'default', $page);
        $pager        = $this->transactionModel->pager;

        // Ambil detail produk per transaksi
        $transactionIds = !empty($transactions) ? array_column($transactions, 'id') : [];
        $products       = !empty($transactionIds)
            ? $this->transactionDetailModel->getProductsByTransactionIds($transactionIds)
            : [];

        // Inject details ke setiap transaksi
        foreach ($transactions as $key => $trx) {
            $transactions[$key]['details'] = $products[$trx['id']] ?? [];
        }

        return $this->response->setJSON([
            'filter' => [
                'start' => $start,
                'end'   => $end,
            ],
            'data' => $transactions,
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'last_page'    => $pager->getPageCount(),
                'total_data'   => $pager->getTotal(),
                'has_next'     => $page < $pager->getPageCount(),
                'has_prev'     => $page > 1,
            ],
        ]);
    }
}
