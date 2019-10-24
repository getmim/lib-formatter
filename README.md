# lib-formatter

Adalah module yang bertugas memformat suatu object atau multiple object
dalam array menjadi suatu bentuk atau properti tipe yang diharapkan. Ini
adalah module yang mungkin digunakan sebelum meneruskan data ke view atau
response api agar data yang dikirimkan siap diproses oleh masing-masing
handler.

Walaupun library ini cukup powerfull, tapi sangat disarankan untuk menggunakannya
hanya sekali dalam satu request untuk meminimalisir penggunaan resource server.
Jika beberapa object yang sama akan di format dengan bentuk yang sama, sangat
disarankan untuk memformatnya secara bersamaan.

## Instalasi

Jalankan perintah di bawah di folder aplikasi:

```
mim app install lib-formatter
```

## Konfigurasi

Semua konfigurasi formatter disimpan di module masing-masing dengan bentuk
seperti di bawah:

```php
return [
    // ...
    'libFormatter' => [
        'formats' => [
            '/format-name/' => [
                '/field-name/' => [
                    'type' => '/format-prop-type/',
                    'format' => '/other-format-name/'
                ],
                'id' => [
                    'type' => 'number'
                ],
                'about' => [
                    'type' => 'text'
                ]
            ]
        ]
    ]
    // ...
];
```

## Penggunaan

Untuk penggunaan yang lebih lengkap, silahkan mengacu pada dokumentasi di [sini](https://getmim.github.io/modules/lib-formatter/).