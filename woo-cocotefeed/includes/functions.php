<?php

add_action( 'woocommerce_order_status_completed', 'mysite_woocommerce_order_status_completed', 10, 1 );
add_action( 'woocommerce_order_status_cancelled', 'mysite_woocommerce_order_status_cancelled', 10, 1 );

function mysite_woocommerce_order_status_completed( $order_id ) {
    // execute API cashback
    exec_cashback($order_id, 'completed');
}

function mysite_woocommerce_order_status_cancelled( $order_id ){
    // execute API cashback
    exec_cashback($order_id, 'cancelled');
}

function check_cocote_export()
{
    global $wpdb;

    $row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}cocote_export WHERE 1");
    if (!is_null($row)) {
        return $row;
    }
    else{
        return 0;
    }
}

function check_order( $order_id ){
    $order = wc_get_order( $order_id );

    $data_order = array();
    $data_order['orderPrice'] = $order->get_total();
    $data_order['email'] = $order->get_billing_email();

   return $data_order;
}

function exec_cashback($order_id, $status){
    if (!file_exists(plugin_dir_path( __DIR__ ). 'log')) {
        mkdir(plugin_dir_path( __DIR__ ). 'log');
    }
    $fp = fopen(plugin_dir_path( __DIR__ ). 'log' . DIRECTORY_SEPARATOR . 'log_' . date('Ymd') . '.log', 'a+');
    $observer = '[LOG ' . date('Y-m-d H:i:s') . '] function exec_cashback()';
    fwrite($fp, $observer . "\n");
    $observer = '[LOG ' . date('Y-m-d H:i:s') . '] '."Order complete for order $order_id";
    fwrite($fp, $observer . "\n");

    $resultat = check_cocote_export();
    $resultat_order = check_order( $order_id );

    if($resultat!=0 && isset($order_id) && isset($resultat->shop_id) && isset($resultat->private_key) && isset($resultat_order['orderPrice']) && isset($resultat_order['email'])) {

        exec('php '.plugin_dir_path( __DIR__ ) . DIRECTORY_SEPARATOR . 'cashback-cocote.php'.
            ' '.$resultat->shop_id.
            ' '.$resultat->private_key.
            ' '.$resultat_order['email'].
            ' '.$order_id.
            ' '.$resultat_order['orderPrice'].
            ' '.$status
            );
    }
    fclose($fp);
}