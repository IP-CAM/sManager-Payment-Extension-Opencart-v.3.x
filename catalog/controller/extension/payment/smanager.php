<?php
/**
 * catalog/controller/extension/payment/smanager.php
 *
 * Copyright (c) 2021 Smanager
 *
 *
 * @author     Saleh Ahmad,    email: nissongo102@gmail.com
 * @author     Riyad Mohammad, email: riyadmohammadraju@gmail.com
 * @copyright  2021 sManager
 * @version    3.0.0
 */

class ControllerExtensionPaymentSmanager extends Controller
{
    public function index()
    {
        $data['button_confirm'] = $this->language->get('button_confirm');

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $data['enter_client_id'] = $this->config->get('payment_smanager_clientID');
        $data['smanager_client_secret'] = $this->config->get('enter_client_secret');
        $data['tran_id'] = $this->session->data['order_id'];
        $data['total_amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

        $data['cus_name'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
        $data['cus_add1'] = $order_info['payment_address_1'];
        $data['cus_add2'] = $order_info['payment_address_2'];
        $data['cus_city'] = $order_info['payment_city'];
        $data['cus_state'] = $order_info['payment_zone'];
        $data['cus_postcode'] = $order_info['payment_postcode'];
        $data['cus_country'] = $order_info['payment_country'];
        $data['cus_phone'] = $order_info['telephone'];
        $data['cus_email'] = $order_info['email'];

        if ($this->cart->hasShipping()) {
            $data['ship_name'] = $order_info['shipping_firstname'] . ' ' . $order_info['shipping_lastname'];
            $data['ship_add1'] = $order_info['shipping_address_1'];
            $data['ship_add2'] = $order_info['shipping_address_2'];
            $data['ship_city'] = $order_info['shipping_city'];
            $data['ship_state'] = $order_info['shipping_zone'];
            $data['ship_postcode'] = $order_info['shipping_postcode'];
            $data['ship_country'] = $order_info['shipping_country'];
        } else {
            $data['ship_name'] = '';
            $data['ship_add1'] = '';
            $data['ship_add2'] = '';
            $data['ship_city'] = '';
            $data['ship_state'] = '';
            $data['ship_postcode'] = '';
            $data['ship_country'] = '';
        }

        $data['currency'] = $order_info['currency_code'];
        $data['success_url'] = $this->url->link('extension/payment/smanager/callback', '', 'SSL');
        $data['fail_url'] = $this->url->link('extension/payment/smanager/Failed', '', 'SSL');
        $data['cancel_url'] = $this->url->link('extension/payment/smanager/Cancelled', '', 'SSL');

        ////Hash Key Gernarate For smanager
        $security_key = $this->smanager_hash_key($this->config->get('smanager_client_secret'), $data);

        $data['verify_sign'] = $security_key['verify_sign'];
        $data['verify_key'] = $security_key['verify_key'];

        $products = '';

        foreach ($this->cart->getProducts() as $product) {
            $products .= $product['quantity'] . ' x ' . $product['name'] . ', ';
        }

        $data['detail1_text'] = $products;

        if ($this->config->get('payment_smanager_test')=='live') {
            $data['process_url'] = $this->url->link('extension/payment/smanager/sendrequest', '', 'SSL');
            $data['api_type'] = "NO";
        } else {
            $data['process_url'] = $this->url->link('extension/payment/smanager/sendrequest', '', 'SSL');
            $data['api_type'] = "YES";
        }

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/smanager')) {
            return $this->load->view($this->config->get('config_template') . '/template/extension/payment/smanager', $data);
        } else {
            return $this->load->view('extension/payment/smanager', $data);
        }
    }

    public function sendrequest()
    {
        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);// update order status as pending

        foreach ($this->cart->getProducts() as $product) {
            $products = $product['name'] . ', ';
        }

        $quantity = 0;

        foreach ($this->cart->getProducts() as $product) {
            $quantity++;
        }

        $data['client_id'] = $this->config->get('enter_client_id');
        $data['tran_id'] = uniqid();
        $data['total_amount'] = $this->currency
            ->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

        $data['client_secret'] = $this->config->get('payment_smanager_clientSecret');

        $data['cus_name']     = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
        $data['cus_add1']     = $order_info['payment_address_1'];
        $data['cus_add2']     = $order_info['payment_address_2'];
        $data['cus_city']     = $order_info['payment_city'];
        $data['cus_state']    = $order_info['payment_zone'];
        $data['cus_postcode'] = $order_info['payment_postcode'];
        $data['cus_country']  = $order_info['payment_country'];
        $data['cus_phone']    = $order_info['telephone'];
        $data['cus_email']    = $order_info['email'];

        if ($this->cart->hasShipping()) {
            $data['ship_name'] = $order_info['shipping_firstname'] . ' ' . $order_info['shipping_lastname'];
            $data['ship_add1'] = $order_info['shipping_address_1'];
            $data['ship_add2'] = $order_info['shipping_address_2'];
            $data['ship_city'] = $order_info['shipping_city'];
            $data['ship_state'] = $order_info['shipping_zone'];
            $data['ship_postcode'] = $order_info['shipping_postcode'];
            $data['ship_country'] = $order_info['shipping_country'];
            $ship = "YES";
        } else {
            $data['ship_name'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
            $data['ship_add1'] = $order_info['payment_address_1'];
            $data['ship_add2'] = $order_info['payment_address_2'];
            $data['ship_city'] = $order_info['payment_city'];
            $data['ship_state'] = $order_info['payment_zone'];
            $data['ship_postcode'] = $order_info['payment_postcode'];
            $data['ship_country'] = $order_info['payment_country'];
            $ship = "NO";
        }

        $orderId = $this->session->data['order_id'];
        $trnxId = 'trnx_' . $orderId . '_' . uniqid();

        $data['currency']    = $order_info['currency_code'];
        $data['success_url'] = $this->url->link('extension/payment/smanager/callback', 'transaction_id=' . $trnxId, 'SSL');
        $data['fail_url']    = $this->url->link('extension/payment/smanager/failed', '', 'SSL');

        $data['shipping_method']  = $ship;
        $data['num_of_item']      = $quantity;
        $data['product_name']     = $products;
        $data['product_category'] = 'Ecommerce';
        $data['product_profile']  = 'general';

        $security_key = $this
            ->smanager_hash_key($this->config->get('payment_smanager_clientSecret'), $data);

        $data['verify_sign'] = $security_key['verify_sign'];
        $data['verify_key']  = $security_key['verify_key'];

        $client_id     = urldecode($this->config->get('payment_smanager_clientID'));
        $client_secret = urldecode($this->config->get('payment_smanager_clientSecret'));

        $headerInfo = [
            'client-id: ' . $client_id,
            'client-secret: ' . $client_secret,
            'Accept: application/json'
        ];

        $redirect_url = 'https://api.sheba.xyz/v1/ecom-payment/initiate';
        $api_type = "NO";

        $amount          = $this->currency
            ->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
        $success_url     = $data['success_url'];
        $fail_url        = $this->url->link('checkout/cart');
        $customerName    = $order_info['firstname'] . ' ' . $order_info['lastname'];
        $customerPhoneNo = $order_info['telephone'];

        $postfields = [
            'amount'          => $amount,
            'transaction_id'  => $trnxId,
            'success_url'     => $success_url,
            'fail_url'        => $fail_url,
            'customer_name'   => $customerName,
            'customer_mobile' => $customerPhoneNo,
            'purpose'         => 'Online Payment',
            'payment_details' => 'Payment for buying items'
        ];

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $redirect_url );
        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($handle, CURLOPT_POST, 1 );
        curl_setopt($handle, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headerInfo);
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);

        $results = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        if ($code == 200 && !( curl_errno($handle))) {
            curl_close( $handle);
            $smanagerResponse = $results;

            $smanager = json_decode($smanagerResponse, true );

            $code    = $smanager['code'];
            $message = $smanager['message'];

            if ($code !== 200) {
                echo json_encode([
                    'error'   => true,
                    'status'  => 'Failed',
                    'message' => $message
                ]);
                return;
            }

            if (isset($smanager['data']['link']) && $smanager['data']['link'] != '') {
                $response['error'] = false;
                $response['plInitiateUrl'] = $smanager['data']['link'];

                echo json_encode($response);
                return;
            }

            $this->model_checkout_order
                ->addOrderHistory($tran_id, $this->config->get('config_order_status_id'), 'Order Initiated');

            if (isset($smanager['data']['link']) && $smanager['data']['link'] != '') {
                return json_encode([
                    'message' => 'Successful',
                    'data'    => $smanager['data']['link']
                ]);
            }
        } else {
            echo $results;
        }
    }

    public function failed()
    {
        $this->load->model('checkout/order');

        if (isset($_POST['tran_id'])) {
            $order_id = $_POST['tran_id'];
        }

        if(isset($_POST['status']) && $_POST['status'] == 'FAILED') {
            $this->model_checkout_order
                ->addOrderHistory($order_id, $this->config->get('payment_smanager_order_fail_id'), "Order Failed By User", false);
            echo "
                <script>
                    location.href = '" . $this->url->link('checkout/failure', '', 'SSL') . "';
                </script>
            ";
        }
    }

    public function callback()
    {
        if (!$this->request->get['transaction_id']) {
            echo "Invalid Information";
            return;
        }

        $clientID     = urldecode($this->config->get('payment_smanager_clientID'));
        $clientSecret = urldecode($this->config->get('payment_smanager_clientSecret'));

        $transid  = $this->request->get['transaction_id'];
        $order_id = explode('_', $transid)[1];

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);
        $amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

        $requested_url = 'https://api.sheba.xyz/v1/ecom-payment/details?transaction_id=' . $transid;

        $amount = $this->currency
            ->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

        $headerInfo = [
            'client-id: ' . $clientID,
            'client-secret: ' . $clientSecret,
            'Accept: application/json'
        ];

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $requested_url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headerInfo);

        $result = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        $status = $amount = '';
        if ($code == 200 && !( curl_errno($handle))) {
            $smanagerResponse = $result;
            $responsejSON = json_decode($smanagerResponse, true );

            $status    = $responsejSON['data']['payment_status'];
            $amount    = $responsejSON['data']['amount'];
            $tran_id   = $responsejSON['data']['transaction_id'];
            $tran_date = $responsejSON['data']['payment_details']['transaction_details']['result']['order']['created_at'];
        }

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_basket'),
            'href' => $this->url->link('checkout/cart')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_checkout'),
            'href' => $this->url->link('checkout/checkout', '', 'SSL')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_failed'),
            'href' => $this->url->link('checkout/success')
        ];

        $data['heading_title'] = $this->language->get('text_failed');

        $data['button_continue'] = $this->language->get('button_continue');

        if ($order_info && ($status === 'completed')) {
            $this->language->load('extension/payment/smanager');

            $data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

            if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
                $data['base'] = HTTP_SERVER;
            } else {
                $data['base'] = HTTPS_SERVER;
            }

            $data['language']  = $this->language->get('code');
            $data['direction'] = $this->language->get('direction');

            $data['heading_title'] = sprintf($this->language->get('heading_title'),
                $this->config->get('config_name'));

            $data['text_response']     = $this->language->get('text_response');
            $data['text_success']      = $this->language->get('text_success');
            $data['text_success_wait'] = sprintf($this->language
                ->get('text_success_wait'), $this->url->link('checkout/success'));
            $data['text_failure']      = $this->language->get('text_failure');
            $data['text_failure_wait'] = sprintf($this->language
                ->get('text_failure_wait'), $this->url->link('checkout/cart'));

            $msg = '';

            if (isset($status) && ($status === 'completed')) {
                $this->load->model('checkout/order');
                $order_status = $order_info['order_status'];
                $amount_rat = $_POST['amount'];

                if ($order_status == 'Pending') {
                    $message = '';
                    $message .= 'Payment Status = ' . $status . "\n";
                    $message .= 'Your Order id = ' . $tran_id . "\n";
                    $message .= 'Payment Date = ' . $tran_date . "\n";

                    if ($_POST['currency_amount'] == $amount) {
                        $this->model_checkout_order
                                ->addOrderHistory($_POST['tran_id'], $this->config->get('config_order_status_id'));
                    } else {
                        echo 'Your Paid Amount is Mismatched!';
                        return;
                    }
                }

                $this->model_checkout_order
                    ->addOrderHistory($order_id, $this->config->get('payment_smanager_order_status_id'), $message, false);

                header("Location: ".$this->url->link('checkout/success'));
            } else {
                $data['continue']       = $this->url->link('checkout/cart');
                $data['column_left']    = $this->load->controller('common/column_left');
                $data['column_right']   = $this->load->controller('common/column_right');
                $data['content_top']    = $this->load->controller('common/content_top');
                $data['content_bottom'] = $this->load->controller('common/content_bottom');
                $data['footer']         = $this->load->controller('common/footer');
                $data['header']         = $this->load->controller('common/header');

                if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/Commerce_failure')) {
                    $this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/extension/payment/Commerce_failure', $data));
                } else {
                    $this->response->setOutput($this->load->view('extension/payment/Commerce_failure', $data));
                }
            }
        }
    }

    // Hash Key Generate For sManager
    public function smanager_hash_key($client_secret="", $parameters=array())
    {
        $return_key = [
            "verify_sign" => '',
            "verify_key"  => ''
        ];

        if (!empty($parameters)) {
            # ADD THE PASSWORD

            $parameters['client_secret'] = md5($client_secret);

            # SORTING THE ARRAY KEY
            ksort($parameters);

            # CREATE HASH DATA
            $hash_string="";
            $verify_key = "";	# VARIFY SIGN
            foreach($parameters as $key=>$value) {
                $hash_string .= $key.'='.($value).'&';
                if ($key!='client_secret') {
                    $verify_key .= "{$key},";
                }
            }
            $hash_string = rtrim($hash_string,'&');
            $verify_key = rtrim($verify_key,',');

            # THAN MD5 TO VALIDATE THE DATA

            $verify_sign = md5($hash_string);
            $return_key['verify_sign'] = $verify_sign;
            $return_key['verify_key'] = $verify_key;
        }
        return $return_key;
    }

}
