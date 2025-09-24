<div class="row">
    <div class="col-12 text-end">
        <a class="accent3" href="/password_reset">Change password</a><br>
        <a class="accent3" href="/logout" title="Logout">
            <i class="fa fa-sign-out" aria-hidden="true"></i> Logout
        </a>
    </div>

    <div class="col-lg-12">
        <h2>Account</h2>
        <form action="/user/account" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" autocomplete="off" id="username" name="username" maxlength="32" aria-describedby="usernameHelp" value="<?php echo $user->getName(); ?>">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" autocomplete="off" id="email" name="email" maxlength="62" aria-describedby="emailHelp" value="<?php echo $user->getEmail(); ?>">
            </div>
            <button type="submit" class="btn">Submit</button>
        </form>
    </div>

    <div class="col-lg-12 mt-5">
        <h2>Invitation</h2>
        <p>Provide the name and email of someone you would like to invite to become a <span class="accent1">DateClutch</span> poll creator.</p>
        <form action="/user/account" method="post">
            <div class="mb-3">
                <label for="invite_name" class="form-label">Name</label>
                <input type="text" class="form-control" autocomplete="off" id="invite_name" name="invite_name" maxlength="32" aria-describedby="invite_nameHelp">
            </div>

            <div class="mb-3">
                <label for="invite_email" class="form-label">Email</label>
                <input type="email" class="form-control" autocomplete="off" id="invite_email" name="invite_email" maxlength="62" aria-describedby="emailHelp">
                <small id="emailHelp" class="form-text text-muted">Neither the names or emails will be retained. They are only used to send an email invitation.</small>
            </div>
            <button type="submit" class="btn">Submit</button>
        </form>
    </div>
</div>
