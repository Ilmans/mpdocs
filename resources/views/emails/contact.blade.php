<table cellspacing="0" cellpadding="0" width="100%" class="w320" style="border-collapse: collapse !important; font-family: Helvetica, Arial, sans-serif;">
  <tbody>
    <tr style="font-family: Helvetica, Arial, sans-serif;">
      <td class="header-lg" style="border-collapse: collapse; color: #4d4d4d; font-family: Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 700; line-height: normal; padding: 35px 0 0; text-align: center;">
        <?= ( 'Hi,' ) ?>@if($mailForCustomer == true)<?= $userName ?>@endif
      </td>
    </tr>
    <tr style="font-family: Helvetica, Arial, sans-serif;">
      <td class="free-text" style="border-collapse: collapse; color: #777777; font-family: Helvetica, Arial, sans-serif; font-size: 16px; line-height: 21px; padding: 10px 60px 0px; text-align: center; width: 100% !important;"> 
      	<?=  $usermessage  ?>
      </td>
    </tr>
    @if($mailForAdmin == true)
		<tr style="font-family: Helvetica, Arial, sans-serif;">
			<td class="free-text" style="border-collapse: collapse; color: #777777; font-family: Helvetica, Arial, sans-serif; font-size: 16px; line-height: 21px; padding: 20px 60px 0px; text-align: center; width: 100% !important;"> 
				<strong>By : </strong><?= $userName ?><br>
				<strong>Email : </strong><a href="mailto:<?= $senderEmail ?>"><?= $senderEmail ?></a>
			</td>
		</tr>
		<tr style="font-family: Helvetica, Arial, sans-serif;">
			<td class="free-text" style="border-collapse: collapse; color: #777777; font-family: Helvetica, Arial, sans-serif; font-size: 16px; line-height: 21px; padding: 30px 60px 0px; text-align: center; width: 100% !important;"> 
				Regards,<br>
				<?= $userName ?>

			</td>
		</tr>
    @endif
    <tr style="font-family: Helvetica, Arial, sans-serif;">
      <td class="free-text" style="border-collapse: collapse; color: #777777; font-family: Helvetica, Arial, sans-serif; font-size: 13px; line-height: 21px; padding: 10px 60px 0px; text-align: center; width: 100% !important;">   
      </td>
    </tr>
  </tbody>
</table>