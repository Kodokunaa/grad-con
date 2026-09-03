<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

function make_mailer() {

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'cccgradconn@gmail.com';   // Admin email
    $mail->Password   = 'anhfwyyhoqannyll';     // Admin app password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('cccgradconn@gmail.com', 'GradConn');
    $mail->isHTML(true);

    return $mail;
}