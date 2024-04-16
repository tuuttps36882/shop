<?php
// require_once(__DIR__ . '/phpmailer/phpmailer/src/PHPMailer.php');
// require_once(__DIR__ . '/phpmailer/phpmailer/src/Exception.php');
// require_once(__DIR__ . '/phpmailer/phpmailer/src/SMTP.php');
// require_once(__DIR__ . "/model/pdo.php");

// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\SMTP;
// use PHPMailer\PHPMailer\Exception;

function send_mail($mail, $email)
{
    //     echo $email;
    // return $email;


    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;

    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->SMTPDebug = 0;

    $mail->Host = 'smtp.gmail.com';
    // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->Username = "dattbps36849@fpt.edu.vn";
    $mail->Password = "bkzz nfan jlyl fzul";
    $BASE_DIR = __DIR__;

    // $email = $_POST["email_user"];

    $ran = random_int(100000,999999);
    $token = bin2hex($ran);

    $sql = "UPDATE user
        SET reset_token_hash = :reset_token_hash
        WHERE email_user = :email_user";

    $conn = pdo_get_connection();
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':reset_token_hash', $ran);
    $stmt->bindParam(':email_user', $email);


    if ($stmt->execute()) {
        $mail->setFrom('dattbps36849@fpt.edu.vn', 'DIMO');
        $mail->addAddress($email);
        $mail->Subject = "Password Reset";
        $url = BASE_URL."?act=resetpass&token={$token}";
        $img = BASE_URL.'IMG/Imgpage/imgforgot.png';
        $mail->msgHTML("
       
        <P>
        Hello, we are <b>Dimo</b><br/>
        We received information that you need help because you forgot your password, if it is you, please click on the link below <br/>
        If you have any difficulties, please contact: 213-463-9173 <br/>
        If that's not the case, please ignore this message<br/>
        </P>
        <p>
            Click <a href=\"$url\">here</a> to reset your password.
        </p>

    ");

        try {
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    } else {
        return false;
    }
    return true;
}