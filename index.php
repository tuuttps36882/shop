<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
session_start();
include_once './mailer/src/Exception.php';
include_once './mailer/src/PHPMailer.php';
include_once './mailer/src/SMTP.php';

define('BASE_URL', 'http://localhost/dimo/' );
use PHPMailer\PHPMailer\PHPMailer;

include "model/pdo.php";
include "model/user.php";
include "model/product.php";
include "model/cart.php";
include_once "model/binhluan.php";
include "send-password-reset.php";
include "view/header.php";

include_once 'send-password-reset.php';





if (isset($_GET['act']) && ($_GET['act'] != "")) {
    $act = $_GET['act'];
    switch ($act) {

     
        case 'collection':
            $page = isset($_GET['page']) ? $_GET['page'] : 1;
            $limit = 9;
            $id_category = isset($_GET['id_category']) ? $_GET['id_category'] : "";
            $data = get_product_collection($id_category, $page, $limit);
            $all_cate = get_all_cate();
            include "view/collection.php";
            break;

        case 'detail':
            $id_product = $_GET['id_product'];
            $limit = 4;
            $product_info = get_detail_product($id_product, $limit);
            $all_cmt = get_all_cmt($id_product);
            // post cmt
            if (isset($_POST['submit_cmt']) && $_POST['submit_cmt']) {
                $product_id = $_POST['id_product'];
                $content = $_POST['cmt'];
                $user_id = $_SESSION['name_user']['id_user'];
                tao_binh_luan($user_id, $product_id, $content);
                echo "<script>window.location.href='?act=detail&id_product=$id_product'</script>";
            }


            include "view/detail.php";
            break;

        case 'login':
            if (isset($_POST['login']) && $_POST['login']) {
                $name_user = $_POST['name_user'];
                $password_user = $_POST['password_user'];
                $checkuser = checkuser($name_user, $password_user);
                if (is_array($checkuser)) {
                    $_SESSION['name_user'] = $checkuser;
                    if ($checkuser['role_user'] == 1) {
                        echo "<script>window.location.href='index.php'</script>";
                    }
                }  else {
                    $thongbao = 'LOGIN FAILED';
                }
            }
            include "view/login.php";
            break;

        case 'signup':
            if (isset($_POST['signup']) && ($_POST['signup'])) {
                $name_user = $_POST['name_user'];
                $password_user = $_POST['password_user'];
                $phoneNumber_user = $_POST['phoneNumber_user'];
                $address_user = $_POST['address_user'];
                $email_user = $_POST['email_user'];

                if (strlen($password_user) < 8) {
                    $thongbao = "Password must have 8 characters";
                } else if (strlen($phoneNumber_user) < 10) {
                    $thongbao = "Incorrect telephone number";
                } else if (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email_user)) {
                    $thongbao = "Invalid email address";
                } else {
                    insert_user($email_user, $password_user, $name_user, $phoneNumber_user, $address_user);
                    $thongbao = "Sign Up Success";
                }
            }
            include "view/signup.php";
            break;

        case 'forgotpass':
            $mail = new PHPMailer();
            if (isset($_POST['sendemail']) && ($_POST['sendemail'])) {
                $email_user = $_POST['email_user'];
                $result_sendemail = send_mail($mail, $email_user);
            }
            include "view/forgotpass.php";
            break;

            case 'resetpass':
    
                if (isset($_POST['resetpass']) && !empty($_POST['resetpass'])) {
                    $token = $_POST['token'];
                    // echo $_POST['password'];
                    // die();
                    change_pass($_POST['password'], $token);
                }
    
                include "view/resetpass.php";
                break;

        case 'logout':
            session_unset();
            include "view/home.php";
            break;

        case 'thoat':
            session_unset();
            echo "<script>window.location.href='index.php?act=home'</script>";
            break;

        case 'edituser':
            if (isset($_POST['capnhat']) && ($_POST['capnhat'])) {
                $name_user = $_POST['name_user'];
                $id_user = $_POST['id_user'];
                $email_user = $_POST['email_user'];
                $address_user = $_POST['address_user'];
                $phoneNumber_user = $_POST['phoneNumber_user'];
                $password_user = $_POST['password_user'];
                $checkuser = checkuser($name_user, $password_user);
                update_taikhoan($id_user, $name_user, $email_user, $password_user, $address_user, $phoneNumber_user);
                $_SESSION['name_user'] = checkuser($name_user, $password_user);
                $thongbao = "Update successful";
            }
            include "view/edituser.php";
            break;

            case 'mybill':
                $id_user = $_SESSION['name_user']['id_user'];
                $data = get_bill($id_user);
                $listbill = loadall_bill($_SESSION['name_user']['id_user']);
                include "view/mybill.php";
                break;

        case 'user':
            include "view/user.php";
            break;

        case 'contact':
            if (isset($_POST["btn_submit"]) && $_POST['btn_submit']) {
                // bat loi form
                $errors = [];
                // bat loi Email
                if (empty($_POST['email'])) {
                    $errors['email']['required'] = 'Email is required';
                } else {
                    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                        $errors['email']['invaild'] = 'Email invalidate';
                    }
                }
                // bat loi Fullname 
                if (empty($_POST['fullname'])) {
                    $errors['fullname']['required'] = 'Required to enter full name';
                } else {
                    if (strlen($_POST['fullname']) < 5) {
                        $errors['fullname']['min_length'] = '
                        Full name must be greater than or equal to 5 characters';
                    }
                }
                //bat loi Message
                if (empty($_POST['message'])) {
                    $errors['message']['required'] = '
                    Please enter information';
                } else {
                    if (strlen($_POST['message']) < 15) {
                        $errors['message']['min_length'] = 'Text must be greater than or equal to 15 characters';
                    }
                }
                if (count($errors) > 0) {
                    // unset($result);
                    // return;
                } else {
                    $mail = new PHPMailer;
                    $email = $_POST['email'];
                    $fullName = $_POST['fullname'];
                    $message = $_POST['message'];
            
                    $sql = "INSERT INTO contact (email, full_name, message)
                    VALUES ('$email', '$fullName', '$message')";
                    // mesage
                    $mesage = 'Your feedback has been noted! /We will contact you as soon as possible';
                    $mail->isSMTP(); // Gửi email bằng SMTP            
                    $mail->Host = 'smtp.gmail.com'; //sử dụng SMPT sever là gmail           
                    $mail->SMTPAuth = true; // thực hiện xác thực SMPT (cần Username + Password)           
                    $mail->Username = 'dattbps36849@fpt.edu.vn'; //user Gmail 
                    $mail->Password = 'bkzz nfan jlyl fzul'; //password Gmail
                    $mail->SMTPSecure = 'ssl';
                    $mail->SetLanguage("vi", 'mailer/language');
                    $mail->Port = 465; // sử dụng port được cung cấp từ SMTP 
                    $mail->setFrom('luuthanhdat34@gmail.com');
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = "Code verify change password!";
                    $mail->Body = $message;
                    $mail->send();
                    // send mail
                    if (pdo_get_connection()->query($sql) == true) {
                        $result = 'Your contact information has been sent!';
                    } else {
                        $result = 'You have not entered any information yet';
                    }
                }
            }
            include "view/contact.php";
            break;

        case 'about':
            include "view/about.php";
            break;

        case 'addcart':
            if (isset($_POST['addcart']) && ($_POST['addcart'])) {
                $id_product = $_POST['id_product'];
                $image_product = $_POST['image_product'];
                $name_product = $_POST['name_product'];
                $price_product = $_POST['price_product'];
                if (isset($_POST['amount_product']) && ($_POST['amount_product'] > 0) && ($_POST['amount_product'] != "")) {
                    $amount_product = $_POST['amount_product'];
                } else { // default quantity = 1
                    $amount_product = 1;
                }
                $fg = 0;
                // check product in cart
                // if product exist in cart, update quantity
                $i = 0;
                if (isset($_SESSION['addcart'])) {
                    foreach ($_SESSION['addcart'] as $addcart) {
                        if ($addcart['id_product'] == $id_product) {
                            $amount_product = $_SESSION['addcart'][$i]['amount_product'] + 1;
                            $_SESSION['addcart'][$i]['amount_product'] = $amount_product;
                            $fg = 1;
                            break;
                        }
                        $i++;
                    }
                }
                // if product not exist in cart, add product to cart
                if ($fg == 0) {
                    $cart = array(
                        'id_product' => $id_product,
                        'image_product' => $image_product,
                        'name_product' => $name_product,
                        'amount_product' => $amount_product,
                        'price_product' => $price_product
                    );
                    $_SESSION['addcart'][] = $cart;
                }
            }
            echo "<script>window.location.href='?act=viewcart'</script>";
            // header("location: ?act=viewcart");
            // include "view/viewcart.php";
            break;

            // update quantity product in cart
        case 'pluscart':
            if (isset($_GET['id']) && ($_GET['id'] > 0)) {
                $id_product = $_GET['id'];
                $i = 0;
                foreach ($_SESSION['addcart'] as $cart) {
                    if ($cart['id_product'] == $id_product) {
                        $amount_product = $_SESSION['addcart'][$i]['amount_product'] + 1;
                        $_SESSION['addcart'][$i]['amount_product'] = $amount_product;
                        break;
                    }
                    $i++;
                }
            }
            echo "<script>window.location.href='?act=viewcart'</script>";
            // header("location: ?act=viewcart");
            break;
        case 'minuscart':
            if (isset($_GET['id']) && ($_GET['id'] > 0)) {
                $id_product = $_GET['id'];
                $i = 0;
                foreach ($_SESSION['addcart'] as $cart) {
                    if ($cart['id_product'] == $id_product) {
                        $amount_product = $_SESSION['addcart'][$i]['amount_product'] - 1;
                        if ($amount_product >= 1) {
                            $_SESSION['addcart'][$i]['amount_product'] = $amount_product;
                        }
                        break;
                    }
                    $i++;
                }
            }
            echo "<script>window.location.href='?act=viewcart'</script>";
            // header("location: ?act=viewcart");
            break;

        case 'viewcart':

            include "view/viewcart.php";
            break;
            //delete product and all cart
        case 'delcart':
            if (isset($_GET['id']) && ($_GET['id'] > 0)) {
                array_splice($_SESSION['addcart'], $_GET['id'], 1);
                if (isset($_SESSION['addcart']) && (count($_SESSION['addcart']) > 0)) {
                    // header("location:index.php?act=viewcart");$i=
                    $i = 0;
                    foreach ($_SESSION['addcart'] as $addcart) {
                        if ($_SESSION['addcart'][$i]['id_product'] == $_GET['id']) {
                            array_splice($_SESSION['addcart'], $i, 1);
                            break;
                        }
                        $i++;
                    }
                }
                echo "<script>window.location.href='?act=viewcart'</script>";
            } else {
                if (isset($_SESSION['addcart'])) unset($_SESSION['addcart']);
            }
            if (
                isset($_SESSION['addcart']) && (count($_SESSION['addcart']) > 0)
            ) {
                header("location:index.php?act=viewcart");
            } else {
                echo '
            <div style="text-align: center; margin: 50px;">
                <h2>Your cart is currently empty</h2>
                <p>Explore our great products and add them to your cart now!</p>
                <a href="index.php" style="display: inline-block; padding: 10px 20px; color: white; background-color: #007BFF; text-decoration: none; border-radius: 5px;">Return to shop</a>
            </div>
            ';
            }
            break;
            //checkout vs payment
        case 'bill':
            include "view/bill.php";
            break;

        case 'checkout':
            $mail = new PHPMailer();
            if (isset($_SESSION['name_user']) && ($_SESSION['name_user'])) {
                // tien hanh dat hang
                if (isset($_POST['checkout']) && $_POST['checkout']) {
                    $id_user = $_SESSION['name_user']['id_user'];
                    $name_bill = $_POST['name_user'];
                    $address_bill = $_POST['address_user'];
                    $phoneNumber_user = $_POST['phoneNumber_user'];
                    $email_bill = $_POST['email_user'];
                    $pttt_bill = $_POST['payment-method'];
                    $tolal_bill = $_POST['total'];

                    // paymentwith vnpay
                    if ($pttt_bill == '4') {
                        $result_payment_vnpay = payment_vnpay($mail, $id_user, $name_bill, $address_bill, $phoneNumber_user, $email_bill, $pttt_bill, $tolal_bill);

                        echo "<script>window.location.href='$result_payment_vnpay'</script>";
                    } else {

                        $id_bill = checkout($mail, $id_user, $name_bill, $address_bill, $phoneNumber_user, $email_bill, $pttt_bill, $tolal_bill);
                    }
                    // paymentwith vnpay



                    // header("location: ?act=billcomfirm&id_bill=$id_bill");

                }
                // get result after payment with vnpay
                if (isset($_GET['vnp_ResponseCode']) && $_GET['vnp_ResponseCode']) {
                    $id_user = $_GET['id_user'];
                    $name_bill = $_GET['name_bill'];
                    $address_bill = $_GET['address_bill'];
                    $phoneNumber_user = $_GET['phoneNumber_user'];
                    $email_bill = $_GET['email_bill'];
                    $pttt_bill = $_GET['pttt_bill-method'];
                    $tolal_bill = $_GET['tolal_bill'];

                    $vnp_ResponseCode = $_GET['vnp_ResponseCode'];
                    $vnp_TxnRef = $_GET['vnp_TxnRef'];
                    $vnp_Amount = $_GET['vnp_Amount'];
                    $vnp_OrderInfo = $_GET['vnp_OrderInfo'];
                    $vnp_TransactionNo = $_GET['vnp_TransactionNo'];
                    $vnp_BankCode = $_GET['vnp_BankCode'];
                    $vnp_PayDate = $_GET['vnp_PayDate'];
                    $vnp_ResponseCode = $_GET['vnp_ResponseCode'];
                    $vnp_SecureHash = $_GET['vnp_SecureHash'];

                    $id_bill = result_checkout_vnpay(
                        $vnp_ResponseCode,
                        $vnp_TxnRef,
                        $vnp_Amount,
                        $vnp_OrderInfo,
                        $vnp_TransactionNo,
                        $vnp_BankCode,
                        $vnp_PayDate,
                        $vnp_SecureHash,
                        $mail,
                        $id_user,
                        $name_bill,
                        $address_bill,
                        $phoneNumber_user,
                        $email_bill,
                        $pttt_bill,
                        $tolal_bill
                    );
                }
                // get result after payment with vnpay
                // 
                if (isset($id_bill) && $id_bill) {
                    echo $id_bill;
                    echo "<script>window.location.href='?act=billcomfirm&id_bill=$id_bill'</script>";
                }
                include "view/checkout.php";
            } else {
                echo "<script>window.location.href='?act=login'</script>";
            }
            break;

        case 'billcomfirm':
            include "view/checkout_success.php";
            break;

            // mybill
        case 'mybill':
            $id_user = $_SESSION['name_user']['id_user'];
            $data = get_bill($id_user);
            include "view/mybill.php";
            break;


        default:
            $data1 = get_product_new_home_new();
            $data = get_product_new_home();
            include "view/home.php";
    }
} else {

    $data1 = get_product_new_home_new();
    $data = get_product_new_home();
    include "view/home.php";
}
include "view/footer.php";
