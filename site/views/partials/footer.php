<?php
declare(strict_types=1);
?>
    </main><!-- /.app-main or /.page-content -->
<?php if ($auth->isLoggedIn()): ?>
</div><!-- /.app-body -->
<?php endif; ?>

<footer class="footer mt-auto py-3 border-top">
    <div class="container text-center small">
        <nav class="mb-1">
            <a href="#" class="link-secondary text-decoration-none">Über uns</a>
            <span class="text-muted mx-1">|</span>
            <a href="#" class="link-secondary text-decoration-none">Stellenangebote</a>
            <span class="text-muted mx-1">|</span>
            <a href="#" class="link-secondary text-decoration-none">Presse</a>
            <span class="text-muted mx-1">|</span>
            <a href="#" class="link-secondary text-decoration-none">Impressum</a>
        </nav>
        <small class="text-muted">&copy; 2026 Fahrzeugkosten-Tracker</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>