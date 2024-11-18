<div class="row">
    <div class="col-12">
        <h2>Enter your new password</h2>
    </div>
    <div class="col-md-6">
        <p>Welcome back.<br>
        Provide your new password.</p>
    </div>
    <div class="col-md-6">
        <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">

            <div class="mb-3">
                <label for="password" class="form-label">New password</label>
                <input type="password" class="form-control" autofocus="autofocus" id="password" name="password" maxlength="64" aria-describedby="password">
                <div class="form-text"></div>
            </div>

            <button type="submit" class="btn">Save</button>
        </form>
    </div>
</div>
