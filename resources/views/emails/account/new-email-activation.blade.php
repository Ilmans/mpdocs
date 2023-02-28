	<div class="title" style="font-family:Helvetica, Arial, sans-serif;font-size:25px;font-weight:400;color:#374550;text-align:center;">Activate Your New Email!</div>
	<br>
    <div class="hr" style="height:1px;border-bottom:1px solid #cccccc">&nbsp;</div><br>
    <div class="body-text" style="font-family:Helvetica, Arial, sans-serif;font-size:14px;line-height:20px;text-align:left;color:#333333">
			
        <p style="text-align: center;">
			To activate your new email address, please click on below button.<br>
            <a href="<?= route('user.new_email.activation', ['activationKey' => $activationKey, 'userID' => $userID]) ?>" target="_blank" class="lw-button">Activate New Email</a>
        </p>

		<p>This link will become invalid in <?= $expirationTime ?> hours and you have to request again.</p>

		<strong>Your New email details are as follows :- </strong>
        <ol style="list-style:none;padding: 0;">
			<li>
               <strong><?= ('Email :') ?> </strong> <a class="lw-link" href="<?= e( $email ) ?>" ><?= e( $email ) ?></a>
            </li> 
        </ol>
    </div>