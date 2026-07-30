<?php

return [

    'booking_fee' => env('HOSTEL_BOOKING_FEE', 15000),

    'institutional_email_domain' => env('INSTITUTIONAL_EMAIL_DOMAIN', 'students.usiu.ac.ke'),

    'blocks' => ['Block A', 'Block B', 'Block C'],

    'move_out_notice_days' => env('HOSTEL_MOVE_OUT_NOTICE_DAYS', 14),

    'move_out_steps' => [
        [
            'title' => 'Submit your move-out notice',
            'description' => 'Notify the housing office through this portal at least 14 days before your intended vacate date.',
        ],
        [
            'title' => 'Clear your room',
            'description' => 'Remove all personal belongings, clean the room, and leave furniture in its original arrangement.',
        ],
        [
            'title' => 'Return keys and access card',
            'description' => 'Hand over your room keys and hostel access card to your warden or the housing office.',
        ],
        [
            'title' => 'Settle outstanding fees',
            'description' => 'Clear any unpaid hostel fees, damage charges, or other housing-related balances.',
        ],
        [
            'title' => 'Room inspection',
            'description' => 'Schedule and complete a walk-through inspection with your warden or resident assistant.',
        ],
        [
            'title' => 'Sign clearance form',
            'description' => 'Visit the Housing Office (New Block Hostel, Ground Floor) to sign the official move-out clearance form.',
        ],
    ],

];
