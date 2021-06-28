<?php
/**
 * SMANAGER
 * @version 1.0.1
 * @author Riyad Mohammad <riyadmohammadraju@gmail.com>
 * @copyright 2021 https://www.smanager.xyz
 * Opencat Payment Module V.3.x
 */

class ControllerExtensionPaymentSmanager extends Controller
{
    private $error = [];

    public function index()
    {
        $this->load->language('extension/payment/smanager');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_smanager', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', 'SSL'));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['merchant'])) {
            $data['error_merchant'] = $this->error['merchant'];
        } else {
            $data['error_merchant'] = '';
        }
        if (isset($this->error['password'])) {
            $data['error_password'] = $this->error['password'];
        } else {
            $data['error_password'] = '';
        }

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text'      => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'separator' => false
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', 'SSL'),
            'separator' => ' :: '
        ];

        $data['breadcrumbs'][] = [
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('extension/payment/smanager', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'separator' => ' :: '
        ];

        $data['action'] = $this->url->link('extension/payment/smanager', 'user_token=' . $this->session->data['user_token'], 'SSL');

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', 'SSL');


        if (isset($this->request->post['payment_smanager_clientID'])) {
            $data['payment_smanager_clientID'] = $this->request->post['payment_smanager_clientID'];
        } else {
            $data['payment_smanager_clientID'] = $this->config->get('payment_smanager_clientID');
        }

        if (isset($this->request->post['enter_client_secret'])) {
            $data['enter_client_secret'] = $this->request->post['enter_client_secret'];
        } else {
            $data['enter_client_secret'] = $this->config->get('enter_client_secret');
        }

        if (isset($this->request->post['payment_smanager_test'])) {
            $data['payment_smanager_test'] = $this->request->post['payment_smanager_test'];
        } else {
            $data['payment_smanager_test'] = $this->config->get('payment_smanager_test');
        }

        if (isset($this->request->post['payment_smanager_total'])) {
            $data['payment_smanager_total'] = $this->request->post['payment_smanager_total'];
        } else {
            $data['payment_smanager_total'] = $this->config->get('payment_smanager_total');
        }

        if (isset($this->request->post['payment_smanager_order_status_id'])) {
            $data['payment_smanager_order_status_id'] = $this->request->post['payment_smanager_order_status_id'];
        } else {
            $data['payment_smanager_order_status_id'] = $this->config->get('payment_smanager_order_status_id');
        }
        if (isset($this->request->post['payment_smanager_order_fail_id'])) {
            $data['payment_smanager_order_fail_id'] = $this->request->post['payment_smanager_order_fail_id'];
        } else {
            $data['payment_smanager_order_fail_id'] = $this->config->get('payment_smanager_order_fail_id');
        }

        if (isset($this->request->post['payment_smanager_order_risk_id'])) {
            $data['payment_smanager_order_risk_id'] = $this->request->post['payment_smanager_order_risk_id'];
        } else {
            $data['payment_smanager_order_risk_id'] = $this->config->get('payment_smanager_order_risk_id');
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['payment_smanager_geo_zone_id'])) {
            $data['payment_smanager_geo_zone_id'] = $this->request->post['payment_smanager_geo_zone_id'];
        } else {
            $data['payment_smanager_geo_zone_id'] = $this->config->get('payment_smanager_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['payment_smanager_status'])) {
            $data['payment_smanager_status'] = $this->request->post['payment_smanager_status'];
        } else {
            $data['payment_smanager_status'] = $this->config->get('payment_smanager_status');
        }

        if (isset($this->request->post['payment_smanager_sort_order'])) {
            $data['payment_smanager_sort_order'] = $this->request->post['payment_smanager_sort_order'];
        } else {
            $data['payment_smanager_sort_order'] = $this->config->get('payment_smanager_sort_order');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $search    = '/admin';
        $replace   = '';

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/smanager', $data));

        $this->response->setOutput($this->load->view('extension/payment/smanager', $data));
    }

    private function str_replace_last( $search , $replace , $str ) {
        if( ( $pos = strrpos( $str , $search ) ) !== false ) {
            $search_length  = strlen( $search );
            $str    = substr_replace( $str , $replace , $pos , $search_length );
        }
        return $str;
    }

    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/smanager')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['payment_smanager_clientID']) {
            $this->error['merchant'] = $this->language->get('error_merchant');
        }
        if (!$this->request->post['payment_smanager_clientSecret']) {
            $this->error['password'] = $this->language->get('error_password');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }
}
