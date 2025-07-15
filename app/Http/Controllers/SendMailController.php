<?php

namespace App\Http\Controllers;

use PHPMailer\PHPMailer\{
    Exception,
    PHPMailer,
    OAuth,
};
use League\OAuth2\Client\Provider\Google;

require base_path("vendor/autoload.php");

class SendMailController extends Controller
{
    // Environment variables for email configuration
    protected $clientId;
    protected $clientSecret;
    protected $refreshToken;
    protected $username;
    protected $passwd;

    // Function to initialize environment variables
    public function __construct()
    {
        // Initialize environment variables
        $this->clientId = env("MAIL_CLIENT_ID");
        $this->clientSecret = env("MAIL_CLIENT_SECRET");
        $this->refreshToken = env("MAIL_REFRESH_TOKEN");
        $this->username = env("MAIL_USERNAME");
        $this->passwd = env("MAIL_PASSWORD");
    }

    // Function to send an email
    public function sendMail($receiver, $subject, $data)
    {
        try {
            // Set up email parameters
            $receiver_mail = $receiver;
            $subject_mail = $subject;
            $content_mail = $data;

            // Initialize PHPMailer
            $mail = new PHPMailer(true);
            $provider = new Google([
                "clientId" => $this->clientId,
                "clientSecret" => $this->clientSecret
            ]);

            // Configure SMTP settings
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->Port = 465;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->SMTPAuth = true;
            $mail->AuthType = "XOAUTH2";
            $mail->Username = $this->username;
            $mail->Password = $this->passwd;

            // Set OAuth configuration
            $mail->setOAuth(new OAuth([
                "provider" => $provider,
                "clientId" => $this->clientId,
                "clientSecret" => $this->clientSecret,
                "refreshToken" => $this->refreshToken,
                "userName" => $mail->Username
            ]));

            // Set email details
            $mail->setFrom($mail->Username, config("app.name"));
            $mail->addAddress($receiver_mail);
            $mail->isHTML(true);
            $mail->Subject = $subject_mail;
            $mail->Body = $content_mail;

            // Send email and return response
            if ($mail->send()) {
                return response()->json([
                    "success" => true,
                    "msg" => "send email successfully."
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "msg" => $e->getMessage() . " " . $mail->ErrorInfo
            ]);
        }
    }
}
