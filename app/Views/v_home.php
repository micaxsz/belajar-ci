<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<?php
if (session()->getFlashData('success')) {
    ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashData('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php
}
?>

<div class="row">
    <?php foreach ($products as $key => $item): ?>
        <?php
        $hargaFinal = $item['harga'];
        $isDiskon = !empty($activeDiskon);
        if ($isDiskon) {
            $hargaFinal = $item['harga'] - $activeDiskon['nominal'];
        }
        ?>
        <div class="col-lg-6">
            <?= form_open('keranjang') ?>
            <?= form_hidden([
                'id' => (string) $item['id'],
                'nama' => (string) $item['nama'],
                'harga' => (string) $hargaFinal,
                'foto' => (string) $item['foto']
            ]) ?>

            <div class="card">
                <div class="card-body">
                    <img src="<?= base_url() . "img/" . $item['foto'] ?>" alt="..." width="50%">
                    <h5 class="card-title"><?= $item['nama'] ?></h5>
                    <div class="mb-3" style="font-size: 1.1rem;">
                        <?php if ($isDiskon): ?>
                            <span class="text-muted text-decoration-line-through me-2" style="color: #dc3545 !important;">
                                <?= number_to_currency($item['harga'], 'IDR', 'id_ID', 0) ?>
                            </span>
                            <span class="text-primary fw-bold" style="color: #0d6efd !important;">
                                <?= number_to_currency($hargaFinal, 'IDR', 'id_ID', 0) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-primary fw-bold" style="color: #0d6efd !important;">
                                <?= number_to_currency($item['harga'], 'IDR', 'id_ID', 0) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-info rounded-pill" style="background-color: #0dcaf0; color: white; border: none;">Beli</button>
                </div>
            </div>
            <?= form_close() ?>
        </div>
    <?php endforeach ?>
</div>
<!-- end Table with stripped rows -->
 
<?= $this->endSection() ?>