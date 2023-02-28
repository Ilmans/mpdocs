	<div class="title" style="font-family:Helvetica, Arial, sans-serif;font-size:25px;font-weight:400;color:#374550;text-align:center;">Reset Your Password!</div>
	<br>
    <div class="hr" style="height:1px;border-bottom:1px solid #cccccc">&nbsp;</div><br>
    <div class="body-text" style="font-family:Helvetica, Arial, sans-serif;font-size:14px;line-height:20px;text-align:left;color:#333333">
			
        <p style="text-align: center;">To reset your password, please click on below button.<br>
            <a href="<?= url('/').configItem('reset_password_url').$token ?>" target="_blank" class="lw-button">Reset Password</a>
        </p>

		<p>Please reset your password within <?= $expirationTime ?> hours, otherwise your password reset request will become invalid and you will have to request again.</p>

		We hope that you enjoy your stay with us 
    </div>