<?php
if (!defined('ABSPATH')) exit;
$this->handle_add_record();
?>

<div class="wrap asos-wrap">
    <h2 class="asos-page-title">
        <?php _e('Amazon Seller Sync', 'asos'); ?>
    </h2>
    <hr>
    <div class="asos-container">
        <div class="asos-add-form">
            <form action="" method="post" class="asos-add-record">
                
                <div class="asos-form-group">
                    <label for="order_id" class="asos-label">Amazon Tracking ID:</label>
                    <input type="text" name="order_id" id="order_id" class="asos-field" autocomplete="off" />
                </div>
                <div class="asos-form-group">
                    <label for="order_status" class="asos-label">Order Status:</label>
                    <select name="order_status" id="order_status" class="asos-field">
                        <?php
                        foreach ($this->status as $option) {
                            $value = explode('-', $option)[2];
                            echo '<option value="' . $option . '">' . $value . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="asos-form-group">
                    <label for="delivery_date" class="asos-label">Delivery Date:</label>
                    <input type="date" name="delivery_date" id="delivery_date" class="asos-field" autocomplete="off" />
                </div>
                <button type="submit" class="button button-primary submit-btn">Add Record</button>
            </form>
        </div>

        <div class="asos-filters">
            <div class="asos-form-group">
                <label class="asos-label">Search by Tracking ID:</label>
                <input type="text" id="asos-filter-trackid" class="asos-field" />
            </div>
            <div class="asos-form-group">
                <label class="asos-label">Search by Status:</label>
                <select class="asos-field" id="asos-filter-status">
                    <?php
                    echo '<option value="">Select an option</option>';
                    foreach ($this->status as $option) {
                        $value = explode('-', $option)[2];
                        echo '<option value="' . $option . '">' . $value . '</option>';
                    }
                    ?>
                </select>
                <script>
                    jQuery(".asos-filter-status").val("").change();
                </script>
            </div>
            <div class="asos-form-group">
                <label class="asos-label">Search by Date (From):</label>
                <input type="date" id="asos-filter-dateFrom" class="asos-field" />
            </div>
            <div class="asos-form-group">
                <label class="asos-label">Search by Date (To):</label>
                <input type="date" id="asos-filter-dateTo" class="asos-field" />
            </div>
            <button type="button" class="button button-primary filter_record">Filter</button>
        </div>

        <div class="asos-records-table">
            <table class="asos-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Status</th>
                        <th>Delivery Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($this->get_records())) {
                        foreach ($this->get_records() as $record) {
                    ?>
                            <tr data-row_id='<?php echo $record->id; ?>' data-trackID="<?php echo $record->order_id ?>" data-status="<?php echo implode('-', array($record->event_status, $record->event_region, $record->order_status)); ?>" data-date="<?php echo $record->delivery_date;?>">
                                <td><?php echo $record->order_id; ?></td>
                                <td>
                                    <select id="order_status_<?php echo $record->id; ?>" class="asos-field">
                                        <?php
                                        foreach ($this->status as $option) {
                                            $value = explode('-', $option)[2];
                                            echo '<option value="' . $option . '">' . $value . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <script>
                                        jQuery("#order_status_<?php echo $record->id; ?>").val("<?php echo implode('-', array($record->event_status, $record->event_region, $record->order_status)); ?>").change();
                                    </script>
                                </td>
                                <td><?php echo date('c', strtotime($record->delivery_date)); ?></td>
                                <td>
                                    <button class="button button-primary update_record" data-record_id="<?php echo $record->id; ?>">Update <span class="asos_loader"></span></button>
                                    <button class="button button-primary delete_record" data-record_id="<?php echo $record->id; ?>">Delete <span class="asos_loader"></span></button>
                                </td>
                            </tr>
                    <?php
                        }
                    }
                    ?>

                </tbody>
            </table>
        </div>
    </div>
</div>