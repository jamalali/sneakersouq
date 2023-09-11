<?php

return [

    'agenty' => [
        'api_key' => env('AGENTY_API_KEY')
    ],

    'sneakers_db' => [
        'url'       => env('SNEAKERS_DB_URL'),
        'api_key'   => env('SNEAKERS_DB_API_KEY'),
        'api_host'  => env('SNEAKERS_DB_API_HOST'),

        'genders' => [
            "Child",
            "Infant",
            "Men",
            "Preschool",
            "Toddler",
            "Unisex",
            "Women",
            "Youth"
        ],

        'brands' => [
            "Asics",
            "Adidas",
            "Alexander Mcqueen",
            "Bape",
            "Bait",
            "Balenciaga",
            "Burberry",
            "Chanel",
            "Common Projects",
            "Converse",
            "Crocs",
            "Diadora",
            "Dior",
            "Fendi",
            "Fila",
            "Gucci",
            "Hoka One One",
            "Jordan",
            "Li-ning",
            "Louis Vuitton",
            "Mschf",
            "New Balance",
            "Nike",
            "Off-white",
            "On",
            "Prada",
            "Puma",
            "Reebok",
            "Saint Laurent",
            "Salomon",
            "Saucony",
            "Under Armour",
            "Vans",
            "Veja",
            "Versace",
            "Yeezy"
        ]
    ],

    'shopify' => [
        'admin_url'     => env('SHOPIFY_ADMIN_URL'),
        'store_url'     => env('SHOPIFY_STORE_URL'),
        'access_token'  => env('SHOPIFY_ACCESS_TOKEN')
    ]

];
