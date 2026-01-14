<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Model Audit Configuration
    |--------------------------------------------------------------------------
    |
    | Control which models are logged and which fields are included/excluded.
    | Use fully qualified model class names.
    |
    */
    'models' => [
        'purchase_order' => [
            'include' => [
                'supplier_id',
                'purchase_invoice_number',
                'order_date',
                'received_date',
                'tax',
                'sub_total',
                'total',
                'status',
                'received_by',
            ],
        ],
        'purchase_order_item' => [
            'include' => [
                'purchase_order_id',
                'description',
                'quantity',
                'rate',
                'amount',
            ],
        ],
        'cheque' => [
            'include' => [
                'bank_account_id',
                'party_type',
                'party_id',
                'type',
                'cheque_number',
                'amount',
                'date',
                'status',
                'bank_name',
            ],
        ],
        'payment' => [
            'include' => [
                'party_type',
                'party_id',
                'date',
                'amount',
                'payment_method',
                'shift',
                'transaction_id',
            ],
        ],
        'ledger' => [
            'include' => [
                'date',
                'party_type',
                'party_id',
                'debit',
                'credit',
                'reference_type',
                'reference_id',
                'balance',
            ],
        ],
        'customer' => [
            'include' => [
                'name',
                'email',
                'phone',
                'address',
                'credit_balance',
            ],
        ],
        'supplier' => [
            'include' => [
                'name',
                'email',
                'phone',
                'address',
                'opening_balance',
                'pan',
            ],
        ],
        'invoice' => [
            'include' => [
                'customer_id',
                'invoice_date',
                'tax',
                'sub_total',
                'total',
                'status',
                'payment_type',
                'sale_return',
            ],
        ],
        'invoice_item' => [
            'include' => [
                'invoice_id',
                'description',
                'quantity',
                'rate',
                'amount',
            ],
        ],
        'bank_account' => [
            'include' => [
                'bank_name',
                'account_number',
                'account_type',
                'balance',
            ],
        ],
    ],
];
