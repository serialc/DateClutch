<div id="footer" class="container-fluid footer bg-dark-subtle text-body-secondary mt-5">
    <div class="container">
        <footer class="pt-4">
            <div class="row">
                <div id="footer_left" class="col-md-5 mt-3">
                    <h3 class="fs-6">Made with <i class="fa fa-flash fs-5 ms-2" aria-hidden="true"></i></h3>
                    <ul>
                        <li><a href="https://getbootstrap.com/">Bootstrap</a> for layout</li>
                        <li><a href="https://getcomposer.org/">Composer</a> for PHP dependencies</li>
                        <li><a href="https://fontawesome.com/v4/icons/">Font Awesome</a> icons</li>
                        <li><a href="https://github.com/">GitHub</a> for version control</li>
                        <li><a href="https://www.php.net/">PHP</a> is the programming language</li>
                        <li><a href="https://linuxmint.com/">Linux Mint</a> operating system</li>
                        <li><a href="https://en.wikipedia.org/wiki/Vim_(text_editor)">Vim</a> for development</li>
                </div>

                <div id="footer_middle" class="col-md-4 mt-3">
                    <h3 class="fs-6">Legalese <i class="fa fa-random fs-5 ms-2" aria-hidden="true"></i></h3>
                    <a href="/terms_of_use">Terms</a><br>
                    <a href="/privacy_policy">Privacy</a><br>
                    <a href="/cookies_policy">Cookies</a>
                </div>

                <div id="footer_right" class="col-md-3 mt-3 text-end">
                    <h3 class="fs-6"><i class="fa fa-hand-rock-o fs-4 me-2" aria-hidden="true"></i> DateClutch</h3>
                    <p>Want to contribute?<br>The code is open and available on <a href="https://github.com/serialc/DateClutch" target="_blank">Github</a>.</p>
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
<!-- Modal -->
<div class="modal fade" id="mainModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="modalTitle">Modal title</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="modalBody">
      </div>
      <div class="modal-footer">
        <button type="button" id="modalClose" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" id="modalConfirm" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#mainModal">Save changes</button>
      </div>
    </div>
  </div>
</div>

<script src="/js/clutch.js"></script>
</body>
</html>
