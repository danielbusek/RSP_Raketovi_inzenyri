<?php if ($GLOBALS['use_foot'] ?? true): ?>
<footer>
    <div class="container">
        <div class="row gy-5">
            
            <div class="col-lg-4 col-md-12">
                <h5 class="fw-bold text-white mb-3">
                    <i class="fa-solid fa-rocket me-2 text-primary"></i>Raketoví inženýři
                </h5>
                <p class="text-secondary small pe-lg-5">
                    Platforma nové generace pro publikování a recenzování odborných článků z oblasti kosmonautiky a vědy.
                </p>
                <div class="d-flex gap-3 mt-4">
                    <a href="#"><i class="fa-brands fa-github fa-lg"></i></a>
                    <a href="#"><i class="fa-brands fa-twitter fa-lg"></i></a>
                    <a href="#"><i class="fa-brands fa-discord fa-lg"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-6">
                <h5>Produkt</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php">Články</a></li>
                    <li><a href="#">Recenzní řízení</a></li>
                    <li><a href="#">Ceník</a></li>
                    <li><a href="#">Pro autory</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-6">
                <h5>Zdroje</h5>
                <ul class="list-unstyled">
                    <li><a href="#">Dokumentace</a></li>
                    <li><a href="#">API</a></li>
                    <li><a href="#">Komunita</a></li>
                    <li><a href="#">Status serverů</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-12">
                <h5>Kontakt</h5>
                <ul class="list-unstyled">
                    <li><a href="#"><i class="fa-solid fa-envelope me-2"></i> support@raketovi.cz</a></li>
                    <li><a href="#"><i class="fa-solid fa-location-dot me-2"></i> Tolstého 1556, Jihlava 1</a></li>
                </ul>
                <div class="mt-4">
                    <p class="small text-secondary mb-2">Odebírat novinky</p>
                    <div class="input-group">
                        <input type="email" class="form-control bg-dark border-secondary text-white placeholder-gray" placeholder="Váš email...">
                        <button class="btn btn-primary">Ok</button>
                    </div>
                </div>
            </div>

        </div>

        <div class="border-top border-secondary mt-5 pt-4 text-center text-secondary small">
            <div class="row">
                <div class="col-md-6 text-md-start">
                    &copy; <?= date('Y') ?> Raketoví inženýři Inc. Všechna práva vyhrazena.
                </div>
                <div class="col-md-6 text-md-end mt-2 mt-md-0">
                    <a href="#" class="me-3">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </div>
</footer>
<?php endif; ?>
</body>
</html>
<?php // require 'db_close.php' ?>