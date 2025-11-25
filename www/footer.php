<?php if ($GLOBALS['use_foot'] ?? true): ?>
<footer class="mt-auto bg-dark text-white py-3">
    <div class="container">
        <div class="row align-items-center">

            <!-- LEVÁ STRANA -->
            <div class="col-md-6 text-start small">
                © Casopis.cz – Všechna práva vyhrazena.
            </div>

            <!-- PRAVÁ STRANA -->
            <div class="col-md-6 text-md-end text-start small">
                <span class="me-3">+420 123 456 789</span>
                <a href="mailto:kontakt@casopis.cz" class="text-light text-decoration-none">
                    kontakt@casopis.cz
                </a>
            </div>

        </div>
    </div>
</footer>
<?php endif; ?>

</body>
</html>

<?php //require 'db_close.php' ?>