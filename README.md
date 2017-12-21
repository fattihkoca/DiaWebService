# Dia Web Servisi (PHP)
Cari, stok, hizmet ve sipariş kartlarını ``Dia`` sunucusuna göndermek için kullanılan ``PHP`` tabanlı servistir.

### Hazırlıklar

```php
// DiaWebService sınıfının dosyası dahil edilir
include_once 'src/DiaWebService.php';

// Sınıf çağrılır
$dia = new DiaWebService;

// Oturum Açılır
$dia->login();

// Hesap adı belirlenir (Konfigürasyonlarda bu isimde bir hesap olmalıdır)
$dia->account_name = 'demo';
```

## Cari Kart Ekleme
* Aşağıdaki tüm parametrelerin örnekteki gibi gönderilmesi zorunludur.

```php
$data = [
    'id' => 123, // Kendi veritabanınızdaki müşteri (Üye) id numarası
    'firstname' => 'Pelin',
    'lastname' => 'Çalışkan',
    'email' => 'pelincaliskan1234@gmail.com',
    'address' => 'Mahalle Sokak Numara Posta Kodu',
    'city' => 'Eskişehir',
    'phone' => "5321234567",
    'tckn' => "12345678901" // TC kimlik numarası
];

// Cari kart ekleme isteği gönderilir
$send = $dia->insert('customer', $data);

// Sonuç başarılı ise ekrana bastırılır
if($send['success']) {
    echo 'Cari kart eklendi: #123';
} 
// Hata sonucu bastırılır
else {
    echo $send['message'];
}
```

## Cari kartları listeleme
* ``filters`` parametresinin altında en az 1 tane filtre parametresi parametresi göndermek zorunludur.

```php
$data = [
    'filters' => [
        [
            'field' => 'id',
            'operator' => '=',
            'value' => '123',
        ]
    ]
];

// Cari kartları listeleme isteği gönderilir
$send = $dia->fetch('customer', $data);

// Sonuç başarılı ise ekrana bastırılır
if($send['success']) {
    var_dump($send['result']);
} 
// Hata sonucu bastırılır
else {
    echo $send['message'];
}
```

## Cari kartı id üzerinden getirme
* Sadece ``id`` belirtilmesi (2. arguman) yeterlidir.

```php
$id = 123;

// Cari kartı id referansıyla getirme isteği gönderilir
$send = $dia->fetch_by_id('customer', $id);

// Sonuç başarılı ise ekrana bastırılır
if($send['success']) {
    var_dump($send['result']);
} 
// Hata sonucu bastırılır
else {
    echo $send['message'];
}
```

## Stok Kartı Ekleme
* ``barcode`` parametresi hariç geriye kalan tüm parametrelerin örnekteki gibi gönderilmesi zorunludur.

```php
$data = [
    'id' => '123', // ürün id'si
    'name' => 'Okul Çantası', // ürün adı
    'price' => 100, // ürün fiyatı
    'tax' => 8, // kdv oranı
    'tax_included' => true, // kdv dahil mi
    'is_promotion' => false, // hediye ürün mü
    'barcode' => '123456789000000' // [ZORUNLU DEĞİL]
];

// Stok kartı ekleme isteği gönderilir
$send = $dia->insert('stock', $data);

// Sonuç başarılı ise ekrana bastırılır
if($send['success']) {
    echo 'Stok kartı eklendi: #123';
} 
// Hata sonucu bastırılır
else {
    echo $send['message'];
}
```

## Stok kartlarını listeleme
* ``filters`` parametresinin altında en az 1 tane filtre parametresi parametresi göndermek zorunludur.

```php
$data = [
    'filters' => [
        [
            'field' => 'id',
            'operator' => '=',
            'value' => '123',
        ]
    ]
];

// Stok kartı listeleme isteği gönderilir
$send = $dia->fetch('stock', $data);

// Sonuç başarılı ise ekrana bastırılır
if($send['success']) {
    var_dump($send['result']);
} 
// Hata sonucu bastırılır
else {
    echo $send['message'];
}
```

## Stok kartını id üzerinden getirme
* Sadece ``id`` belirtilmesi (2. arguman) yeterlidir.

```php
$id = 123;

// Stok kartını id referansıyla getirme isteği gönderilir
$send = $dia->fetch_by_id('stock', $id);

// Sonuç başarılı ise ekrana bastırılır
if($send['success']) {
    var_dump($send['result']);
} 
// Hata sonucu bastırılır
else {
    echo $send['message'];
}
```

## Hizmet Kartı Ekleme
* Aşağıdaki tüm parametrelerin örnekteki gibi gönderilmesi zorunludur.

```php
$data = [
    'id' => '123', // hizmet id'si
    'name' => 'Online Ders', // hizmet adı
    'price' => 500, // hizmet fiyatı
    'tax' => 8, // kdv oranı
    'tax_included' => true, // kdv dahil mi
    'is_promotion' => false, // hediye ürün mü
];

// Hizmet kartı ekleme isteği gönderilir
$send = $dia->insert('service', $data);

// Sonuç başarılı ise ekrana bastırılır
if($send['success']) {
    echo 'Hizmet kartı eklendi: #123';
} 
// Hata sonucu bastırılır
else {
    echo $send['message'];
}
```

## Hizmet kartlarını listeleme
* ``filters`` parametresinin altında en az 1 tane filtre parametresi parametresi göndermek zorunludur.

```php
$data = [
    'filter' => [
        [
            'field' => 'id',
            'operator' => '=',
            'value' => '123',
        ]
    ]
];

// Hizmet kartı listeleme isteği gönderilir
$send = $dia->fetch('service', $data);

// Sonuç başarılı ise ekrana bastırılır
if($send['success']) {
    var_dump($send['result']);
} 
// Hata sonucu bastırılır
else {
    echo $send['message'];
}
```

## Hizmet kartını id üzerinden getirme
* Sadece ``id`` belirtilmesi (2. arguman) yeterlidir.

```php
$id = 123;

// Cari kartını id referansıyla getirme isteği gönderilir
$send = $dia->fetch_by_id('service', $id);

// Sonuç başarılı ise ekrana bastırılır
if($send['success']) {
    var_dump($send['result']);
} 
// Hata sonucu bastırılır
else {
    echo $send['message'];
}
```

## Sipariş fişi Ekleme
* ``note_*`` parametreleri ve ``address_2`` parametresi hariç geriye kalan tüm parametrelerin örnekteki gibi gönderilmesi zorunludur.

```php
$data = [
    'id' => '123', // sipariş id'si
    'customer_id' => '123', // müşteri id'si (Cari kart Dia'da kayıtlı olmalı)
    'installment' => 1, // taksit sayısı
    'date' => time(), // unix formatında sipariş tarihi
    'address' => 'Mahalle Sokak Kapı 3', // sevk adresi
    'address_2' => '', // [ZORUNLU DEĞİL]
    'city' => 'Eskişehir',
    'phone' => '05321234567', // alıcı telefon
    'is_eft' => false, // eft/havale ödemesi mi
    'currency' => 'TRY', // para birimi (TRY, USD, EUR)
    'products' => array( // sipariş kalemleri tanımlanır (Ürünler Dia'da kayıtlı olmalı)
        array(
            'id' => '123', // 1. ürün id.si
            'price' => 100, // 1. ürün için ödenen tutar
            'tax' => 8, // kdv
            'tax_included' => true, // kdv dahil mi
            'quantity' => 1, // satılan ürün adedi
            'discount' => 0, // indirim oranı (yüzde)
            'is_physical' => true, // fiziksel ürün (STOK - MALZEME)
            'note_1' => 'Ürün notu 1',
            'note_2' => 'Ürün notu 2',
        ),
        array(
            'id' => '123', // 2. ürün id.si
            'price' => 900, // 2. ürün için ödenen tutar
            'tax' => 8, // kdv
            'tax_included' => true, // kdv dahil mi
            'quantity' => 1, // satılan ürün adedi
            'discount' => 5, // indirim oranı (yüzde)
            'is_physical' => false, // fiziksel ürün değil (HİZMET)
            'note_1' => 'Hizmet notu 1', // [ZORUNLU DEĞİL]
            'note_2' => 'Hizmet notu 2', // [ZORUNLU DEĞİL]
        )
    ),
    'note_1' => 'Sipariş notu 1', // [ZORUNLU DEĞİL]
    'note_2' => 'Sipariş notu 2', // [ZORUNLU DEĞİL]
    'note_3' => 'Sipariş notu 3', // [ZORUNLU DEĞİL]
];

// Sipariş fişi ekleme isteği gönderilir
$send = $dia->insert('order', $data);

// Sonuç başarılı ise ekrana bastırılır
if($send['success']) {
    echo 'Sipariş kartı eklendi: #123';
} 
// Hata sonucu bastırılır
else {
    echo $send['message'];
}
```

## Sipariş fişlerini listeleme
* ``filters`` parametresinin altında en az 1 tane filtre parametresi parametresi göndermek zorunludur.

```php
$data = [
    'filter' => [
        [
            'field' => 'id',
            'operator' => '=',
            'value' => '123',
        ]
    ]
];

// Sipariş fişi listeleme isteği gönderilir
$send = $dia->fetch('order', $data);

// Sonuç başarılı ise ekrana bastırılır
if($send['success']) {
    var_dump($send['result']);
} 
// Hata sonucu bastırılır
else {
    echo $send['message'];
}
```

## Sipariş fişini id üzerinden getirme
* Sadece ``id`` belirtilmesi (2. arguman) yeterlidir.

```php
$id = 123;

// Sipariş fişini id referansıyla getirme isteği gönderilir
$send = $dia->fetch_by_id('order', $id);

// Sonuç başarılı ise ekrana bastırılır
if($send['success']) {
    var_dump($send['result']);
} 
// Hata sonucu bastırılır
else {
    echo $send['message'];
}
```

## Dia için farklı disiplinlerde ``id`` formatı oluşturmak
``Dia`` için verdiğiniz ``id`` değerine önek, sonek ve karakter sabitleme yapabilirsiniz. 
Bu işlem için ``DiaWebService`` sınıfında yer alan ``$configurations`` değişkeninindeki bazı parametreleri değiştirebilirsiniz.

### Cari kart kodu için;
```php
'customer' => array(
    'prefix' => 'CK', // önek
    'suffix' => '', // son ek
    'length' => 15 // karakter sayısı
)
```

### Stok kart kodu için;
```php
'stock' => array(
    'prefix' => 'CK', // önek
    'suffix' => '', // son ek
    'length' => 15 // karakter sayısı
)
```

### Servis kart kodu için;
```php
'service' => array(
    'prefix' => 'CK', // önek
    'suffix' => '', // son ek
    'length' => 15 // karakter sayısı
)
```

### Sipariş fiş kodu için;
```php
'order' => array(
    'prefix' => 'CK', // önek
    'suffix' => '', // son ek
    'length' => 15 // karakter sayısı
)
```

## Konfigürasyonları farklı bir yere taşımak
``DiaWebService`` sınıfında yer alan ``$configurations`` değişkenini farklı bir yere taşıyabilir. Örneğin Laravel'de env dosyası içerisine yerleştirmek isteyebilirsiniz. 

Bu değişkenin değerlerini taşıdıktan sonra ``__construct()`` metodunda ``$this->conf`` değişkeninin değerini taşıdığınız konfigürasyonların verilerini çağırarak değiştirebilirsiniz.

Örneğin: (DiaWebService.php)
```php
public function __construct()
{
    .
    .
    $this->conf = \config('DiaWebService');
    .
    .
}
```
