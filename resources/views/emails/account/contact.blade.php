	<div class="title" style="font-family:Helvetica, Arial, sans-serif;font-size:25px;font-weight:400;color:#374550;text-align:center;">You have message from <?= getConfigurationSettings('name') ?>.</div>
	<br>
    <div class="hr" style="height:1px;border-bottom:1px solid #cccccc">&nbsp;</div><br>
    <div class="title" style="font-family:Helvetica, Arial, sans-serif;font-size:16px;font-weight:400;color:#374550">Hi <?= $fullName ?>, </div>
    <br>
    <div class="body-text" style="font-family:Helvetica, Arial, sans-serif;font-size:14px;line-height:20px;text-align:left;color:#333333;">
        <span style="margin-left:30px;"></span><p><?= e( $emailMessage ) ?></p>.
    </div>