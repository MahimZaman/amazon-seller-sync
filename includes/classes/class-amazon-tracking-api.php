<?php

if (!defined('ABSPATH')) return;

class ASOS_AmazonTrackingAPI
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'asos_register_route']);
        add_action('init', [$this, 'create_amazon_sync_manager_role']);
    }

    function create_amazon_sync_manager_role()
    {
        add_role(
            'amazon_sync_manager',
            'Amazon Sync Manager',
            array(
                'read' => true, // Basic read access (same as Subscriber)
            )
        );
    }

    private function validate_amazon_sync_manager($username, $password)
    {
        // Authenticate the user
        $user = wp_authenticate($username, $password);

        // Check if authentication was successful
        if (is_wp_error($user)) {
            return false; // Invalid credentials
        }

        // Check if user has the "amazon_sync_manager" role
        if (in_array('amazon_sync_manager', (array) $user->roles)) {
            return true; // Valid user with correct role
        }

        return false; // Role does not match
    }

    public function asos_register_route()
    {
        register_rest_route('asos/v1', '/shiptrack/', [
            'methods' => 'POST',
            'callback' => [$this, 'asos_shiptrack_callback'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function asos_xml_res_error($status, $message) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<ErrorResponse>'. "\n";
        $xml .= '    <StatusCode>'.$status.'</StatusCode>' . "\n";
        $xml .= '    <ErrorMessage>' . esc_xml($message) . '</ErrorMessage>' . "\n";
        $xml .= '</ErrorResponse>';

        return $xml;
    }

    public function asos_shiptrack_callback(WP_REST_Request $request)
    {
        header('Content-Type: application/xml');
        // Get raw XML input
        $xml_data = $request->get_body();

        // Convert XML to Object
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xml_data);

        if (!$xml) {
			echo $this->asos_xml_res_error(400, 'Invalid XML format');
            exit;
        }

        // Extract Data
        $username = sanitize_text_field((string) $xml->Validation->UserID);
        $password = sanitize_text_field((string) $xml->Validation->Password);
        $apiVersion = sanitize_text_field((string) $xml->APIVersion);
        $tracking_number = sanitize_text_field((string) $xml->TrackingNumber);

        // Validate Request
        if (empty($tracking_number) || empty($username) || empty($password)) {
            // Return the XML response and stop further execution
            echo $this->asos_xml_res_error(400, 'Invalid request');
            exit;
        }

        if ($this->validate_amazon_sync_manager($username, $password) === false) {
            echo $this->asos_xml_res_error(400, 'Invalid credentials');
            exit;
        }

        //$tracking_number = sanitize_text_field($params['TrackingNumber']);
        $status = $this->get_order_status_by_tracking_number($tracking_number);

        if (empty($status)) {
            echo $this->asos_xml_res_error(404, 'No order found for tracking number');
            exit;
        }

        // Build the XML Response
        $response = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><AmazonTrackingResponse/>');
        $response->addChild('APIVersion', '4.0');
        $package_info = $response->addChild('PackageTrackingInfo');
        $package_info->addChild('TrackingNumber', $tracking_number);

        // Add Sample Tracking Events (Modify as needed)
        $history = $package_info->addChild('TrackingEventHistory');

        $event1 = $history->addChild('TrackingEventDetail');
        $event1->addChild('EventStatus', $status['event_status']);
        $event1->addChild('EventReason', $status['event_region']);
        $event1->addChild('EventDateTime', date('c', strtotime($status['delivery_date'])));

        // Convert to XML format and return response
        echo $response->asXML();
        exit; // Prevent further WordPress execution
    }

    function get_order_status_by_tracking_number($tracking_number)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'asos_records';
        $results = $wpdb->get_results("SELECT * FROM $table WHERE order_id = '$tracking_number'");

        if (!empty($results)) {
            return array(
                'event_status' => $results[0]->event_status,
                'event_region' => $results[0]->event_region,
                'delivery_date' => $results[0]->delivery_date,
            );
        }

        return array();
    }
}
