<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Poriseba</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f4f7; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f4f7; padding:30px 0;">
    <tr>
        <td align="center">
        <!-- Main Container -->
        <table width="600" cellpadding="0" cellspacing="0" border="0"
               style="max-width:600px; width:100%; background:#ffffff; border-radius:8px; overflow:hidden;">

            <!-- Header / Logo -->
            <tr>
                <td align="center" style="padding:30px 20px 20px;">
                    <img src="{{ $logo }}"
                         alt="Poriseba"
                         style="max-width:180px; height:auto; display:block;">
                </td>
            </tr>

            <!-- Welcome Content -->
            <tr>
                <td style="padding:20px 40px 30px; color:#333333; font-size:15px; line-height:1.6;">

                    <h2 style="margin:0 0 15px; color:#4c3d7c; font-size:24px;">
                        Welcome to Poriseba!
                    </h2>

                    <p style="margin:0 0 15px;">
                        Hello <strong>{{ $name }}</strong>,
                    </p>

                    <p style="margin:0 0 20px;">
                        Your Poriseba account has been successfully created.
                        You can now access the portal using the login credentials
                        provided below.
                    </p>

                    <!-- Credentials Box -->
                    <table width="100%" cellpadding="0" cellspacing="0" border="0"
                           style="background:#f7f5fb; border:1px solid #e3dff0; border-radius:6px; margin:20px 0;">
                        <tr>
                            <td style="padding:20px;">

                                <h3 style="margin:0 0 15px; color:#4c3d7c; font-size:18px;">
                                    Login Details
                                </h3>

                                <p style="margin:8px 0;">
                                    <strong>Email:</strong>
                                    {{ $email }}
                                </p>

                                <p style="margin:8px 0;">
                                    <strong>Password:</strong>
                                    {{ $password }}
                                </p>

                            </td>
                        </tr>
                    </table>

                    <!-- Login Button -->
                    <table cellpadding="0" cellspacing="0" border="0" align="center" style="margin:25px auto;">
                        <tr>
                            <td align="center" bgcolor="#4c3d7c" style="border-radius:5px;">
                                <a href="{{ $loginUrl }}"
                                   style="display:inline-block; padding:12px 30px; color:#ffffff; text-decoration:none; font-size:15px; font-weight:bold;">
                                    Login to Poriseba
                                </a>
                            </td>
                        </tr>
                    </table>

                    <p style="margin:20px 0 10px;">
                        For security reasons, we recommend changing your password
                        after your first login.
                    </p>

                    <p style="margin:15px 0;">
                        If you have any questions or need assistance, please feel
                        free to contact our support team.
                    </p>

                    <p style="margin:25px 0 0;">
                        Thank you for choosing <strong>Poriseba</strong>.
                    </p>

                    <p style="margin:20px 0 0;">
                        Best Regards,<br>
                        <strong>Poriseba Team</strong>
                    </p>

                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td align="center"
                    bgcolor="#4c3d7c"
                    style="padding:15px; color:#ffffff; font-size:13px;">
                    © {{ date('Y') }} Poriseba. All Rights Reserved.
                </td>
            </tr>

        </table>

    </td>
</tr>

</table>

</body>
</html>
