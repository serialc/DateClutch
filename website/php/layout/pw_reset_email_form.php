<div class="row">
    <div class="col-12">
        <h2>Password reset</h2>
    </div>
    <div class="col-md-6">
        <p>Enter your email.<br>
        You will receive a link to create a new password.</p>
    </div>
    <div class="col-md-6">
        <form action="/password_reset" method="post">

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" autofocus="autofocus" id="email" name="email" maxlength="64" aria-describedby="email" value="<?php echo $user->getEmail(); ?>">
                <div class="form-text"></div>
            </div>

            <button type="submit" class="btn">Submit</button>
        </form>
    </div>
</div>
