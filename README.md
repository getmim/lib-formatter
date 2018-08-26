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

Module ini membuat satu library `LibFormatter\Library\Formatter` yang bisa
digunakan dari mana saja di aplikasi:

```php
use LibFormatter\Library\Formatter;

$options = []; // additional options

$object = (object)[...];
$result = Formatter::format('format-name', $object, $options);

$objects = [(object)[...], ...];
$result  = Formatter::formatMany('format-name', $objects, $options, $askey);
```

Library ini memiliki method sebagai berikut:

### format(string $name, object $object, array $options=[]): ?object

### formatMany(string $name, array $objects, array $options=[], string $askey=null) ?array

Secara umum, fungsi ini akan mengembalikan indexed array dari object yang dikirim, untuk membuat
salah satu properti object menjadi array key, isikan nilai properti tersebut pada parameter `$askey`.

## Custom Handler

Formatter memungkinkan menerima custom handler untuk memformat suatu properti, silahkan daftarkan
handler tersebut di konfigurasi module dengan bentuk seperti di bawah:

```php
return [
    // ...
    'libFormatter' => [
        'handlers' => [
            '/name/' => [
                'handler' => 'Class::method',
                'collective' => false // true,
                'field' => 'id', // for collective=true only
            ]
        ]
    ]
    // ...
];
```

Nilai `collective` menentukan apakah semua object dikirimkan sekaligus, atau di proses
satu per satu. Metode ini cocok untuk penanganan multiple object properti dalam satu
eksekusi seperti pengambilan data dari db untuk meminimalisir eksekusi query database.

### collective

Untuk type collective, nilai masing-masing object akan dikelompokan terlebih dahulu,
kemudian meneruskan ke handler dengan bentuk seperti di bawah:

```php
$result = Handler::method(array $values, string $field, array $object, object $format, mixed $options);
```

Nilai yang dikembalikan dari fungsi ini diharapkan array key-value dimana key array adalah
nilai lama object tersebut, dan value adalah nilai baru yang akan ditindihkan ke object.

Jika nilai yang dikembalikan adalah null, maka nilai object tidak akan diubah.

Parameter fungsi tersebut adalah:

1. `values` Array list semua nilai properti object.
1. `field` String nama field properti yang sedang di proses.
1. `object` Array object semua object yang sedang di proses.
1. `format` Object format yang sedang di implementasikan.
1. `options` Mixed opsi yang dikirimkan ke formatter tentang field ini.

Khusus untuk formatter `collective`, tambahan properti `field` dibutuhkan seperti pada
contoh di atas. Nilai dari properti ini yang akan dikelompokan dan dikirim ke handler.
Jika nilai `field` adalah `null`, nilai properti object yang sedang di format yang akan
dikelompokan.

### non collective

Untuk type non collective, masing-masing object dipanggile satu persatu. Handler
tersebut dipanggil dengan bentuk:

```php
$result = Handler::method(mixed $value, string $field, object $object, object $format, mixed $options);
```

Jika nilai yang dikembalikan adalah null, maka nilai object yang lama tidak akan diubah.
Callback ini dipanggil dengan parameter sebagai berikut:

1. `values` Mixed nilai properti object
1. `field` String nama field properti yang sedang di proses.
1. `object` Object yang sedang di proses.
1. `format` Object format yang sedang di implementasikan.
1. `options` Mixed opsi yang dikirimkan ke formatter tentang field ini.

Contoh di bawah adalah contoh handler yang digunakan untuk menambahkan prefix `_` pada
properti object.

```php
class CustomHandler
{
    static function addPrefixNonCollective($value, $field, &$object, $format, $options): void{
        return '_' . $value;
    }

    static function addPrefixForCollective($values, $field, &$object, $format, $options): void{
        $result = [];
        foreach($values as $val)
            $result[$val] = '_' . $val;
        return $resut;
    }
}
```

## Format Type

### boolean|bool

Mengubah nilai menjadi boolean.

```php
'field' => [
    'type' => 'boolean'
]
```

### clone

Mengambil nilai dari properti lain. Salah satu dari `source` atau `sources` harus
diisi. Perbedaan antar keduanya adalah, `source` hanya mengambil satu properti dan
nilai dari properti tersebut menjadi nilai dari field ini. Sementara penggunaan
`sources` akan menguban niai ini menjadi object dengan properti sesuai dengan yang
ditentukan.

```php
'field' => [
    'type' => 'clone',
    'source' => [
        'field' => 'user.name.first',
        'type' => 'text'        // optional
    ],
    'sources' => [
        'name' => [
            'field' => 'user.name.first',
            'type' => 'text'    // optional
        ],
        'age' => [
            'field' => 'user.age',
            'type' => 'number'  // optional
        ]
    ]
]
```

### date

Mengubah nilai properti object menjadi `Date`. Jika nilai `timezone`
tidak diisi, maka nilai dari yang sedang berjalan di aplikasi akan 
digunakan.

```php
'field' => [
    'type' => 'date',
    'timezone' => 'UTC', // Asia/Jakarta // optional
]
```

Object ini kemudian memiliki properti/method sebagai berikut:

```php
$date->format($format);
$date->timezone;
$date->time;
$date->value;
$date->{DateTime fuctions}();
```

### delete

Menghapus properti

```php
'field' => [
    'type' => 'delete'
]
```

### embed

Mengubah nilai properti menjadi URL suatu embed HTML code.

```php
'field' => [
    'type' => 'embed'
]
```

Object ini memiliki beberapa properti sebagai berikut:

```php
$embed->url; // string
$embed->provider; // string
$embed->html; // string
```

### custom

Mengubah nilai menggunakan custom handler.

```php
'field' => [
    'type' => 'custom',
    'handler' => 'Class::method'
]
```

Custom handler akan di panggil dengan bentuk seperti di bawah:

```php
Class::method($value, $field, $object, $format, $options);
```

Parameter yang dikirimkan sama persis dengan custom handler di atas.

### location

Mengubah nilai properti object menjadi object location.

```php
'field' => [
    'type' => 'location'
]
```

Object ini sekarang memiliki properti berikut:

```php
$loc->long;
$loc->lat;
$loc->embed->google(string $apikey);
```

Nilai yang diharapkan yang bisa diubah menjadi lokasi adalah dalam format
`lat,long`.

### multiple-text

Mengubah nilai properti menjadi array multiple text object. Silahkan
mengacu pada type text untuk detail masing-masing text object.

```php
'field' => [
    'type' => 'multiple-text',
    'separator' => ',' // PHP_EOL, |
]
```

### number

Mengubah nilai menjadi object number.

```php
'field' => [
    'type' => 'number',
    'decimal' => 2  // optional
]
```

Object ini kemudian memiliki properti/method sebagai berikut:

```php
$num->value;
$num->format([$decimal=0, [$decimal_sep=',', [$thousand_sep='.']]]);
```

### text

Menguban nilai menjadi object text

```php
'field' => [
    'type' => 'text'
]
```

Object ini kemudian memiliki properti/method sebagai berikut:

```php
$text->chars($len);
$text->words($len);
$text->safe;
$text->clean;
$text->value;
```

Properti safe dan clean akan mengembalikan object dengan type `text`.

### json

Mengubah nilai properti yang adalah text menjadi object dengan fungsi
`json_decode`.

```php
'field' => [
    'type' => 'json'
]
```

### join

Menggabungkan nilai properti object atau text menjadi nilai properti ini

```php
'field' => [
    'type' => 'join',
    'fields' => ['My', 'name', 'is', '$name.first'],
    'separator' => ' '
]
```

Untuk mengambil niali properti object, gunakan prefix `$`. Untuk mendapatkan
nilai dari sub-object, masing-masing properti dipisakan dengan titik.

### rename

Mengubah nama properti menjadi sesuatu yang lain:

```php
'field' => [
    'type' => 'rename',
    'to' => 'newfield'
]
```

### router

Opsi untuk mengubah atau membuat nilai properti menjadi url dari router

```php
'field' => [
    'type' => 'router',
    'router' => [
        'name' => 'routerName',
        'params' => [
            'id' => '$id',
            'name' => 'post',
            'slug' => '$user.name'
        ]
    ]
]
```

Nilai `params` akan dikirimkan ke router builder dimana nilai dari array params
tersebut diambil dari object yang sedang di format jika di awali dengan `$`,
atau string.