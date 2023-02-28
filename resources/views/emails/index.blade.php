<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head style="font-family: Helvetica, Arial, sans-serif;">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" style="font-family: Helvetica, Arial, sans-serif;">
  <meta name="viewport" content="width=device-width, initial-scale=1" style="font-family: Helvetica, Arial, sans-serif;">
  <title style="font-family: Helvetica, Arial, sans-serif;"><?= getConfigurationSettings('name') ?></title>
        <style type="text/css">
            body {
            margin: 0;
            padding: 0;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
            }
            table {
            border-spacing: 0;
            }
            table td {
            border-collapse: collapse;
            }
            table {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
            }
            img {
            -ms-interpolation-mode: bicubic;
            }
            @media screen and (max-width: 599px) {
            .force-row,
            .container {
            width: 100% !important;
            max-width: 100% !important;
            }
            }
            @media screen and (max-width: 400px) {
            .container-padding {
            padding-left: 12px !important;
            padding-right: 12px !important;
            }
            }

			.lw-container {
			/*	padding:30px;*/
			}

            .lw-button {
                background-color: #f44336; /* Green */
                border: none;
                color: white !important;
                padding: 8px 10px;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                font-size: 16px;
                margin: 4px 2px;
                cursor: pointer;
                border-radius: 5px;
				font-family:Helvetica, Arial
            }

            .lw-link {
                color: #2196F3;
                text-decoration: none;
            }

			.lw-alert {
				padding: 15px;
			    margin-bottom: 20px;
			    border: 1px solid transparent;
			    border-radius: 4px;
			}

			.lw-alert-warn {
				color: #8a6d3b;
			    background-color: #fcf8e3;
			    border-color: #faebcc;
			}

        </style>
    </head> 
    <body style="margin:0; padding:30px;background-color: #F0F0F0;">
        <!-- 100% background wrapper (grey background) -->
        <table class="lw-container" border="0" width="100%" height="100%" cellpadding="0" cellspacing="0" style="background-color: #F0F0F0;">
            <tr>
                <td align="center" valign="top" style="background-color: #F0F0F0;">
                    <br>
                    <!-- 600px container (white background) -->
                    <table border="0" width="600" cellpadding="0" cellspacing="0" class="container" style="width:600px;max-width:600px">
                        <tr>
                            <td class="container-padding header" align="left" style="font-family:Helvetica, Arial, sans-serif;font-size:24px;font-weight:bold;padding-bottom:0px;padding-top:0px;color:#DF4726;padding-left:24px;padding-right:24px;background-color:#fff;">
                                <img src="<?= getConfigurationSettings('logo_image_url') ?>">
                            </td>
                        </tr>
                        <tr>
							<td class="container-padding content" align="left" style="padding-left:24px;padding-right:24px;padding-top:12px;padding-bottom:12px;background-color:#ffffff">
								@if(isDemo())
								<center class="lw-alert lw-alert-warn">
				                    <strong>Please Note:</strong> Sample Email Demonstration purposes only
				              	</center>
								@endif
	                            @if(isset($emailsTemplate))
					                @include($emailsTemplate)
					            @endIf
					            @if(isset($emailContent))
					                @include($emailContent)
					            @endIf
							</td>
                        </tr>
                        <tr>
                            <td class="container-padding header" align="left" style="font-family:Helvetica, Arial, sans-serif;padding-bottom:20px;padding-top:20px;padding-left:24px;padding-right:24px;background-color:#d4d4d4;text-align: center;">

                                <address>
                                <?= getConfigurationSettings('name') ?>.<br> 
                            </td>
                        </tr>
                    </table>
                    <!--/600px container -->
                </td>
            </tr>
        </table>
        <!--/100% background wrapper-->
    </body>
</html>