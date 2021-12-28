<!DOCTYPE html>
<html>
<head>
	<title>Forgot Password</title>
</head>
	<body style="font-family:arial">
		<table cellspacing="0" bgcolor="#f7f7f7" cellpadding="0" width="650px" style="padding: 0;border-collapse:collapse; margin: 0 auto;border: 12px groove #cc3f2f;box-shadow: 0px 3px 14px 4px rgba(0,0,0,0.2);">
			<tbody>
				@include('frontend.emails.include.email_header')
				<tr>
					<td>
						<table width="100%" cellpadding="0" cellspacing="0" border="0">
							<tbody>
								<tr>
									<td align="center">
										<span style="font-size:24px; color:#cc3f2f; font-weight: 700;">Forgot Password</span><br />
										<span></span>
									</td>
								</tr>
								<tr>
									<td align="center">
										<p style="margin-bottom:0px; font-size:18px; font-weight:bold; color: #505050;padding: 9px 30px 0px;text-align: left;">Hi, <span>{{@ucwords($data['full_name'])}}</span></p>
										<p style="padding: 9px 30px 20px; line-height:22px; font-size:14px; color: #505050; letter-spacing: 1px; margin: 0;text-align: left;">You are receiving this email because we received a password reset request for your account.</p>										
										<a href="{{@$data['email_verification_link']}}" style="padding: 8px 30px; color: #cc3f2f; margin-top: 10px;text-decoration: none; letter-spacing: 1px; border: 5px double #9f9f9f; border-radius: 0px;font-size: 18px; display: inline-block;font-weight: 800;outline: none;">Reset Password</a>
									</td>
								</tr>
								<tr>
									<td height="30"></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				@include('frontend.emails.include.email_footer')
			</tbody>
		</table>
	</body>
</html>