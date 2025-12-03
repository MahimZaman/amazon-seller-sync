<?php

class ASOS_INIT
{
    private $recTable = 'asos_records';
    private $status = array('D1-NS-Delivered', 'XB-NS-Shipment has left seller facility and is in transit', 'X6-AJ-Problem resolved and shipment is in transit', 'OD-NS-Out for delivery', 'SD-AD-Delay Delivery because customer requests it', 'AH-Z1-First delivery attempt', 'AH-Z2-Second delivery attemp', 'AH-Z3-Third and final delivery attempt', 'SD-H1-Delivery rescheduled by the carrier', 'DE-A2-Incorrect address (Destination address does not exist)', 'DE-G3-Incorrect address (Apartment/Suit is incorrect)', 'DE-E8-Incorrect address (Company/Person Unknown)', 'DE-J6-Delay in delivery due to external factors', 'A9-AK-Shipment damaged, a delivery may be attempted');
    public function __construct()
    {
        $this->load_admin_scripts();
        $this->create_db();

        add_action('admin_menu', [$this, 'menu_pages']);
        add_action('wp_ajax_asos_update_record', [$this, 'asos_update_record']);
        add_action('wp_ajax_asos_delete_record', [$this, 'asos_delete_record']);
    }

    public function asos_delete_record()
    {
        $recordID = $_POST['recordID'];
        $this->delete_db("asos_records", array('id' => $recordID), array('%d'));

        wp_send_json(array(
            'recordID' => $recordID,
        ));
        wp_die();
    }

    public function asos_update_record()
    {
        $record_id = $_POST['recordID'];
        $record_status = $_POST['recordStatus'];

        $event = explode('-', $record_status);

        $event_status = $event[0];
        $event_region = $event[1];
        $order_status = $event[2];

        $this->update_db(
            'asos_records',
            array(
                'order_status' => $order_status,
                'event_status' => $event_status,
                'event_region' => $event_region,
            ),
            array(
                'id' => $record_id,
            ),
            array(
                '%s',
                '%s',
                '%s'
            ),
            array(
                '%d'
            ),
        );

        wp_die();
    }

    public function menu_pages()
    {
        add_menu_page(
            'Amazon Seller Sync',
            'Amazon Seller Sync',
            'manage_options',
            'asos-records',
            [$this, 'asos_records'],
            'dashicons-amazon',
            99
        );
    }

    public function asos_records()
    {
        require_once ASOS_PATH . 'includes/templates/admin-records.php';
    }

    private function load_admin_scripts()
    {
        add_action('admin_enqueue_scripts', function () {
            wp_enqueue_style('fa-css', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css');
            wp_enqueue_style('main-css', ASOS_URL . 'assets/css/admin/style.css');
            wp_enqueue_script('main-js', ASOS_URL . 'assets/js/admin/script.js', array(), null, true);
            wp_localize_script('main-js', 'asosAdmin', array(
                'ajax' => admin_url('admin-ajax.php'),
            ));
        });
    }

    private function create_db()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'asos_records';
        $create_table = $wpdb->query("CREATE TABLE IF NOT EXISTS $table (
            id INT NOT NULL AUTO_INCREMENT,
            order_id VARCHAR(255), 
            order_status VARCHAR(255),
            event_status VARCHAR(255),
            event_region VARCHAR(255),
            delivery_date VARCHAR(255),
            PRIMARY KEY (id) 
        )");
    }

    private function insert_db($table, $args)
    {
        global $wpdb;
        $myTable = $wpdb->prefix . $table;
        $wpdb->insert($myTable, $args);
    }

    private function update_db($table, $data, $where, $format = [], $where_format = [])
    {
        global $wpdb;
        $myTable = $wpdb->prefix . $table;
        $wpdb->update($myTable, $data, $where, $format, $where_format);
    }

    private function delete_db($table, $where, $where_format)
    {
        global $wpdb;
        $myTable = $wpdb->prefix . $table;
        $wpdb->delete($myTable, $where, $where_format);
    }

    private function handle_add_record()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'])) {
            $order_id = sanitize_text_field($_POST['order_id']) ? sanitize_text_field($_POST['order_id']) : '';
            $order_status = sanitize_text_field($_POST['order_status']) ? sanitize_text_field($_POST['order_status']) : [];
            $delivery_date = sanitize_text_field($_POST['delivery_date']) ? sanitize_text_field($_POST['delivery_date']) : '';

            $event = explode('-', $order_status);
            $event_status = $event[0] ? $event[0] : '';
            $event_region = $event[1] ? $event[1] : '';
            $order_status = $event[2] ? $event[2] : '';
            if ($order_id && $order_status && $delivery_date) {
                $this->insert_db('asos_records', array(
                    'order_id' => $order_id,
                    'order_status' => $order_status,
                    'event_status' => $event_status,
                    'event_region' => $event_region,
                    'delivery_date' => $delivery_date,
                ));
            }
        };
    }

    private function get_records()
    {
        global $wpdb;
        $table = $wpdb->prefix . $this->recTable;
        $records = $wpdb->get_results("SELECT * FROM $table") ? $wpdb->get_results("SELECT * FROM $table") : [];
        return $records;
    }
}
