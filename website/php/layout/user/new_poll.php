<?php
// Filename: php/layout/user/new_poll.php
// Purpose: Form to create polls

namespace frakturmedia\clutch;

?>

<h2>New Poll</h2>

<form action="" method="post">
    <div class="row">
        <div class="col-12 mb-3">
            <label for="ptitle" class="form-label">Title</label>
            <input type="text" class="form-control" autofocus="autofocus" id="ptitle" name="ptitle" maxlength="128" aria-describedby="ptitle" value="

<?php 
if ( isset($_POST['ptitle']) ) {
    echo $_POST['ptitle'];
}
?>
">
        </div>

        <div class="col-12 mb-3">
            <label for="pdescription" class="form-label">Description</label>
            <textarea id="pdescription" name="pdescription" class="form-control" aria-label="descriptionHelp" rows="5">
<?php
if ( isset($_POST['pdescription']) ) {
    echo $_POST['pdescription'];
}
?>
</textarea>
            <small id="descriptionHelp" class="form-text text-muted">You can enter markdown to format text. What is <a href="https://www.markdowntutorial.com/" target="_blank">markdown</a>?</small>
        </div>

        <div class="col-12 mb-3">
            <label for="pdates" class="form-label">Dates</label>
            <textarea id="pdates" name="pdates" class="form-control mb-0" aria-label="datesHelp" rows="5">
<?php
if ( isset($_POST['pdates']) ) {
    echo $_POST['pdates'];
}
?>
</textarea>
            <small id="datesHelp" class="form-text text-muted">Enter dates in YYYY-MM-DD format in rows or comma separated.</small>
        </div>

        <div class="col-12 mb-3">
            <label for="pnotifications" class="form-label">Notify</label>
            <input type="text" class="form-control" autofocus="autofocus" id="pnotifications" name="pnotifications" maxlength="256" aria-describedby="notifHelp" disabled value="

<?php
if ( isset($_POST['pnotifications']) ) {
    echo $_POST['pnotifications'];
}
?>
">
            <small id="notifHelp" class="form-text text-muted">Provide comma separated email addresses of people to be notified when someone submits a poll reponse.</small>
            <div class="text-end">
                <button type="submit" class="btn mt-3">Submit</button>
            </div>
        </div>
    </div>
</form>

