<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">
            <a class="nav-link <?php echo (uri_string() == '') ? "" : "collapsed" ?>" href="<?= base_url() ?>">
                <i class="bi bi-grid"></i>
                <span>Home</span>
            </a>
        </li><!-- End Home Nav -->

        <li class="nav-item">
            <a class="nav-link <?php echo (uri_string() == 'keranjang') ? "" : "collapsed" ?>"
                href="<?= base_url('keranjang') ?>">
                <i class="bi bi-cart-check"></i>
                <span>Keranjang</span>
            </a>
        </li><!-- End Keranjang Nav -->

        <li class="nav-item">
            <a class="nav-link <?php echo (uri_string() == 'history') ? "" : "collapsed" ?>"
                href="<?= base_url('history') ?>">
                <i class="bi bi-person"></i>
                <span>History</span>
            </a>
        </li><!-- End History Nav -->

        <li class="nav-item">
            <a class="nav-link <?php echo (uri_string() == 'faq') ? "" : "collapsed" ?>" href="<?= base_url('faq') ?>">
                <i class="bi bi-info-circle"></i>
                <span>F.A.Q</span>
            </a>
        </li><!-- End F.A.Q Nav -->

    </ul>

</aside><!-- End Sidebar-->