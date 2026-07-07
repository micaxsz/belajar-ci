<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
$diskon = (int) session()->get('active_diskon');
$grandTotal = 0;
if (!empty($items)) {
    foreach ($items as $it) {
        $grandTotal += max(0, (int)$it['price'] - $diskon) * (int)$it['qty'];
    }
}
?>

<div class="row">
    <div class="col-lg-6">

        <?= form_open('buy', 'class="row g-3"') ?>

        <?= form_hidden('username', session()->get('username')) ?>
        <?= form_hidden('total_harga', '') ?>

        <div class="col-12">
            <?= form_label('Nama', 'nama', ['class' => 'form-label']) ?>
            <?= form_input([
                'name' => 'nama',
                'id' => 'nama',
                'class' => 'form-control',
                'value' => session()->get('username'),
                'readonly' => true
            ]) ?>
        </div>
        <div class="col-12">
            <?= form_label('Alamat', 'alamat', ['class' => 'form-label']) ?>
            <?= form_input([
                'name' => 'alamat',
                'id' => 'alamat',
                'class' => 'form-control'
            ]) ?>
        </div>
        <div class="col-12">
            <?= form_label('Kelurahan', 'kelurahan', ['class' => 'form-label']) ?>
            <?= form_dropdown('kelurahan', [], '', ['id' => 'kelurahan', 'class' => 'form-control']) ?>
        </div>
        <div class="col-12">
            <?= form_label('Layanan', 'layanan', ['class' => 'form-label']) ?>
            <?= form_dropdown('layanan', [], '', ['id' => 'layanan', 'class' => 'form-control']) ?>
        </div>
        <div class="col-12">
            <?= form_label('Ongkir', 'ongkir', ['class' => 'form-label']) ?>
            <?= form_input([
                'name' => 'ongkir',
                'id' => 'ongkir',
                'class' => 'form-control',
                'readonly' => true
            ]) ?>
        </div>
        <div class="col-12">
            <?= form_submit(
                'submit',
                'Buat Pesanan',
                ['class' => 'btn btn-primary']
            ) ?>
        </div>

        <?= form_close() ?>
    </div>
    <div class="col-lg-6">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Nama</th>
                    <th scope="col">Harga</th>
                    <th scope="col">Jumlah</th>
                    <th scope="col">Sub Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($items)):
                    foreach ($items as $index => $item):
                        $hargaAsli   = (int) $item['price'];
                        $hargaDiskon = max(0, $hargaAsli - $diskon);
                        $rowSubtotal = $hargaDiskon * (int) $item['qty'];
                        ?>
                        <tr>
                            <td>
                                <?= $item['name'] ?>
                            </td>
                            <td>
                                <?php if ($diskon > 0): ?>
                                    <del class="text-danger"><?= number_to_currency($hargaAsli, 'IDR') ?></del><br>
                                    <?= number_to_currency($hargaDiskon, 'IDR') ?>
                                <?php else: ?>
                                    <?= number_to_currency($hargaAsli, 'IDR') ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $item['qty'] ?>
                            </td>
                            <td>
                                <?= number_to_currency($rowSubtotal, 'IDR') ?>
                            </td>
                        </tr>
                        <?php
                    endforeach;
                endif;
                ?>
                <tr>
                    <td colspan="2"></td>
                    <td>Subtotal</td>
                    <td>
                        <?= number_to_currency($grandTotal, 'IDR') ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td>Total</td>
                    <td><span id="total">
                            <?= number_to_currency($grandTotal, 'IDR') ?>
                        </span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('script') ?>
<script>
    $(document).ready(function () {

        let ongkir = 0;
        let subtotal = <?= $grandTotal ?>;

        function hitungTotal() {
            let total = subtotal + ongkir;

            $("#ongkir").val(ongkir);
            $("#total").text(`IDR ${total.toLocaleString('id-ID')}`);
            $("#total_harga").val(total);
        }

        hitungTotal();

        $('#kelurahan').select2({
            placeholder: 'Cari daerah tujuan',
            minimumInputLength: 3,
            ajax: {
                url: "<?= site_url('ajax/destinations') ?>",
                dataType: "json",
                delay: 300,
                data: function (params) {
                    return {
                        q: params.term
                    }
                },
                processResults: function (data) {
                    return data;
                }
            }
        });

        $("#kelurahan").on("change", function () {

            let id_kelurahan = $(this).val();

            $("#layanan").empty();

            ongkir = 0;
            hitungTotal();

            $.ajax({
                url: "<?= site_url('ajax/costs') ?>",
                data: {
                    destination: id_kelurahan
                },
                dataType: "json",
                success: function (data) {

                    console.log('Costs response:', data);

                    data.forEach(function (item) {

                        $("#layanan").append(
                            $("<option>", {
                                value: item.cost,
                                text: `${item.description} (${item.service}) estimasi ${item.etd}`
                            })
                        );

                    });

                },
                error: function (xhr, status, error) {
                    console.error('Costs AJAX error:', status, error);
                    console.error('Response:', xhr.responseText);
                }
            });

        });

        $("#layanan").on("change", function () {

            ongkir = parseInt($(this).val());

            hitungTotal();

        });

    });
</script>
<?= $this->endSection() ?>