	<div class="title" style="font-family:Helvetica, Arial, sans-serif;font-size:25px;font-weight:400;color:#374550;text-align:center;">Account has been created</div>
	<br>
    <div class="hr" style="height:1px;border-bottom:1px solid #cccccc">&nbsp;</div><br>
    <div class="title" style="font-family:Helvetica, Arial, sans-serif;font-size:16px;font-weight:400;color:#374550">Hi <?= $fullName ?>, </div>
    <br>
    <div class="body-text" style="font-family:Helvetica, Arial, sans-serif;font-size:14px;line-height:20px;text-align:left;color:#333333;">
        <span style="margin-left:30px;"></span>Your Account is created in <?= getConfigurationSettings('name') ?>, Use below mentioned login details to access your account:
    </div>
    <div class="body-text" style="font-family:Helvetica, Arial, sans-serif;font-size:14px;line-height:20px;text-align:left;color:#333333">
			
        <p style="text-align: center;">
            <a href="<?=  route('user.login')  ?>" target="_blank" class="lw-button">Login</a>
        </p>

		<strong>Login Details :- </strong>
        <ol style="list-style:none;padding: 0;">
            <li>
                <strong><?= ('Name :') ?> </strong><?= e( $fullName ) ?>
            </li> 
			<li>
               <strong><?= ('Email :') ?> </strong> <a class="lw-link" href="<?= e( $email ) ?>" ><?= e( $email ) ?></a>
            </li> 
            <?php if((isset($password)) and (!empty($password))) { ?>
			<li>
                <strong><?= ('Password :') ?> </strong><?= e( $password ) ?>
            </li> 
			<?php } ?>
        </ol>
    </div>