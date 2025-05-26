<?php

return [
    'label' => [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'ready_to_pickup' => 'Ready to Pickup',
        'delivered' => 'Delivered',
        'completed' => 'Completed',
        'rejected' => 'Rejected',
        'canceled' => 'Canceled',
        'refunding' => 'Refunding',
        'waiting_confirmation' => 'Waiting Confirmation',
    ],

    'description' => [
        'pending' => 'Your order is waiting for processing',
        'processing' => 'Your order is being processed',
        'shipped' => 'Your order has been shipped',
        'ready_to_pickup' => 'Your order is ready to pickup',
        'delivered' => 'Your order has been delivered',
        'completed' => 'Your order is completed',
        'rejected' => 'Your order was rejected',
        'canceled' => 'Your order was canceled',
        'refunding' => 'Refund process',
        'waiting_confirmation' => 'Your order is waiting for confirmation',
    ],

    'payment_method_label' => [
        'single_payment' => 'Single Payment',
        'split_payment' => 'Split Payment',
    ],

    'payment_method_description' => [
        'single_payment' => 'Payment with a single cash method',
        'split_payment' => 'Payment with installment method or upfront payment',
    ],

    'payments_status_label' => [
        'pending' => 'Awaiting Payment',
        'partially_paid' => 'Partially Paid',
        'paid' => 'Fully Paid',
        'canceled' => 'Canceled',
    ],

    'payments_status_description' => [
        'pending' => 'Awaiting payment',
        'partially_paid' => 'A portion of the total payment has been paid',
        'paid' => 'Payment is fully paid',
        'canceled' => 'Payment is canceled'
    ],

    'shipping_method_label' => [
        'self_pickup' => 'Self Pickup',
        'courier_pickup' => 'Courier Pickup'
    ],

    'shipping_method_description' => [
        'self_pickup' => 'The customer comes to pick up the order',
        'courier_pickup' => 'The courier would pick-up and deliver the order'
    ]
];
