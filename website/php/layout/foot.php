<div id="footer" class="container-fluid footer bg-dark-subtle text-body-secondary mt-5">
    <div class="container">
        <footer class="pt-4">
            <div class="row">
                <div id="footer_left" class="col-md-5 mt-3">
                    <p>Created using:</p>
                    <ul>
                        <li><a href="https://getbootstrap.com/">Bootstrap</a> for layout</li>
                        <li><a href="https://getcomposer.org/">Composer</a> for PHP dependencies</li>
                        <li><a href="https://fontawesome.com/v4/icons/">Font Awesome</a> icons</li>
                        <li><a href="https://github.com/">GitHub</a> for version control</li>
                        <li><a href="https://www.php.net/">PHP</a> is the programming language</li>
                        <li><a href="https://linuxmint.com/">Linux Mint</a> operating system</li>
                        <li><a href="https://en.wikipedia.org/wiki/Vim_(text_editor)">Vim</a> for development</li>
                </div>

                <div id="footer_middle" class="col-md-2 mt-3 fs-6 text-center">
                </div>

                <div id="footer_right" class="col-md-3 mt-3 text-end">
                </div>

                <div class="col-12 mt-5 text-center">
                    <p>&copy; DateClutch
<?php

$today = new \DateTime(date('Y-m-d'));
echo $today->format('Y');

?>
                    </p>

                </div>
            </div>
        </footer>
    </div>
</div>
<script src="/js/clutch.js"></script>
</body>
</html>
