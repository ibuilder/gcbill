<!-- filepath: c:\Users\iphoe\OneDrive\Documents\Server\construction-billing\production\construction-billing-app\templates\partials\footer.html -->
            <!-- Page content ends here -->
        </main>
    </div> <!-- End row -->
</div> <!-- End container-fluid -->

<footer class="mt-auto py-3 bg-light text-center">
    <div class="container">
        <span class="text-muted">&copy; <?= date('Y') ?> <?= htmlspecialchars($config['app']['name'] ?? 'Construction Billing') ?>. All rights reserved.</span>
    </div>
</footer>

<!-- Bootstrap Bundle JS (includes Popper) -->
<script src="/js/bootstrap.bundle.min.js"></script>
<!-- jQuery (if needed by other scripts or plugins) -->
<script src="/js/jquery.min.js"></script>
<!-- Custom App JS -->
<script src="/js/app.js"></script>
<!-- Add any page-specific JS scripts here if needed -->
<?= $pageScripts ?? '' ?>

</body>
</html>