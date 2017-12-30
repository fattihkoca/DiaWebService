# Dia Web Servisi (PHP)
Cari, stok ve hizmet kartları ile sipariş fişlerini ``Dia`` sunucusuna göndermek için kullanılan ``PHP`` tabanlı servistir.

## Hazırlıklar

Öncelikle ``DiaWebService`` sınıfını çağırıp oturum açma isteği göndermeniz gerekmektedir. Sonrasında ``account_name`` belirtmelisiniz. Yani kullanacağınız hesabı seçmelisiniz. Ayrıca oturum sınıf konfigürasyonlarında seçeceğiniz isimde bir hesap olmalıdır.

```php
// DiaWebService sınıfının dosyası dahil edilir
include_once 'src/DiaWebService.php';

// Sınıf çağrılır
$dia = new DiaWebService;

// Oturum açılır
$dia->login();

// Hesap adı belirlenir
$dia->account_name = 'demo';
```

## Genel Bakış

``DiaWebService`` sınıfında tüm metod sonuçları dizi (Array) formatında dönmektedir. Sonuç içerisinde aşağıdaki parametreler bulunmaktadır:

* ``success``: Sonucun başarılı olup olmadığını belirtir. (``true`` veya ``false`` değerleri döner)
* ``message``: Sonuç ile ilgili bilgi mesajı belirtir. Sonuç başarılı ise "``OK``" mesajı döner.
* ``result``: Sonuç verilerini belirtir. (Genellikle ``Array`` formatındadır.)

Örneğin ``login()`` metodunda oturum açılmışsa ``result`` içerisinde ``session_id``, yani oturum ``id`` bilgisi döner.

## Verileri Getirme
Dia'daki ilgili servisten istenilen bilgileri ``fetch()`` metoduyla getirebilirsiniz. 

* İlk argüman servis türüdür. Bunlar:

    * ``customer``: Cari
    * ``stock``: Stok
    * ``service``: Hizmet
    * ``order``: Sipariş

* İkinci argüman gönderilecek veri dizisidir (Array). 

```php
$servis_turu = 'customer';

$send = $dia->fetch($servis_turu, $data);
```

Örnek kullanım:
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

#### 1. Filtreleme (filters)
``fetch()`` metodunda, veri içerisinde ``filters`` parametresi zorunludur ve altında en az 1 tane filtre parametre grubu (``field``, ``operator``, ``value``) göndermek zorunludur.

* ``field``: Filtrelenecek alanın adı. (mysql'de sütun veya column adı.)
* ``operator``: Filtre türü. Şu operatörler kullanılır: '``<``', '``>``', '``<=``', '``>=``', '``!``', '``=``', '``IN``', '``NOT IN``'
* ``value``: Filtre değeri.

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

// Stok kartlarını getirme isteği gönderilir
$send = $dia->fetch('stock', $data);
```

Ayrıca "``id`` değerleri içinde 'ABC' geçenleri getir" demek istediğimizde şunu kullanabilirsiniz:

```php
$data = [
    'filters' => [
        [
            'field' => 'id',
            'value' => 'ABC',
        ]
    ]
];

// Servis kartlarını getirme
$dia->fetch('service', $data);
```

#### 2. Sıralama (sorts)
Listenin belirli bir sırada gelmesi isteniyorsa kullanılır. ``sorts`` parametresinin altında en az 1 tane sıralama grubu (``field``, ``sorttype``) göndermelisiniz.

* ``field``: Sıralanması istenen alan adı.
* ``sorttype``: Sıralama türü (ASC: düz sıralı, DESC: ters sıralı)

```php
$data = [
    'filters' => [
        [
            'field' => 'id',
            'sorttype' => 'DESC',
        ]
    ]
];
```

### İstenen Bir Veriyi ``ID`` Üzerinden Getirme
Dia'daki ilgili servisten kendi veri tabanınızda ve Dia'da aynı id ile kayıtlı bir veriyi ``fetch_by_id()`` metoduyla getirebilirsiniz. Eğer bir işleminizde farklı filtre kombinasyonlarını kullanmayacaksanız bu metod oldukça kullanışlıdır.

* İlk argüman servis türüdür. Bunlar:

    * ``customer``: Cari
    * ``stock``: Stok
    * ``service``: Hizmet
    * ``order``: Sipariş

* İkinci argüman gönderilecek ``id`` değeridir. 

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

```php
$id = 123;

// Sipariş fişini "id" referansıyla getirme
$dia->fetch_by_id('order', $id);
```

## Yeni Veri Ekleme
Dia'daki ilgili servise yeni veri eklemek için ``insert()`` metodu kullanılır.

* İlk argüman servis türüdür. Bunlar:

    * ``customer``: Cari
    * ``stock``: Stok
    * ``service``: Hizmet
    * ``order``: Sipariş

* İkinci argüman gönderilecek veri dizisidir (Array).

```php
// Cari kart ekleme
$dia->insert('customer', $data);

// Stok kartı ekleme
$dia->insert('stock', $data);

// Hizmet kartı ekleme
$dia->insert('service', $data);

// Sipariş fişi ekleme
$dia->insert('order', $data);
```

Şimdi tek tek servisleri eklerken göndereceğimiz verilere bakalım:

### 1. Cari kart ekleme
Aşağıdaki tüm parametrelerin örnekteki gibi gönderilmesi zorunludur.

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

### 2. Stok kartı ekleme
``barcode`` parametresi hariç geri kalan tüm parametrelerin örnekteki gibi gönderilmesi zorunludur.

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

### 3. Hizmet kartı ekleme
Aşağıdaki tüm parametrelerin örnekteki gibi gönderilmesi zorunludur.

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

### 4. Sipariş fişi ekleme
``note_*`` parametreleri ve ``address_2`` parametresi hariç geri kalan tüm parametrelerin örnekteki gibi gönderilmesi zorunludur.

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

        // Örnek stok ürünü seçelim (Fiziksel ürün)
        array(
            'is_physical' => true, // fiziksel ürün (STOK - MALZEME)
            'id' => '123', // 1. ürün id.si
            'price' => 100, // 1. ürün için ödenen tutar
            'tax' => 8, // kdv
            'tax_included' => true, // kdv dahil mi
            'quantity' => 1, // satılan ürün adedi
            'discount' => 0, // indirim oranı (yüzde)
            'note_1' => 'Ürün notu 1', // [ZORUNLU DEĞİL]
            'note_2' => 'Ürün notu 2', // [ZORUNLU DEĞİL]
        ),

        // Örnek hizmet ürünü seçelim (Fiziksel ürün değil)
        array(
            'is_physical' => false, // fiziksel ürün değil (HİZMET)
            'id' => '123', // 2. ürün id.si
            'price' => 900, // 2. ürün için ödenen tutar
            'tax' => 8, // kdv
            'tax_included' => true, // kdv dahil mi
            'quantity' => 1, // satılan ürün adedi
            'discount' => 5, // indirim oranı (yüzde)
            'note_1' => 'Hizmet notu 1', // [ZORUNLU DEĞİL]
            'note_2' => 'Hizmet notu 2', // [ZORUNLU DEĞİL]
        )
    ),

    // Dilerseniz sipariş notlarını gönderebilirsiniz
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

## Dia'daki Hesap Bilgilerini Özelleştirme
Bu işlem için ``DiaWebService`` sınıfında yer alan ``$configurations`` değişkenindeki bazı parametreleri değiştirmelisiniz.

```php
.
.
.
'accounts' => array(
    .
    .
    'hesabim' => array(
        'account' => 'sunucu_kodu', // tanımlanan sunucu kodu
        'company' => 1, // firma kodu
        'branch' => 1234, // şube kodu
        'depository' => 4321, // depo kodu
        'username' => 'ws',
        'password' => '123456',
        'lang' => 'tr',
        'disconnect_same_user' => true, // önceki oturumları kapatır
    ),
    .
    .
),
.
.
.
```

## Dia İçin ``ID`` Formatlarını Özelleştirme
``Dia`` için verdiğiniz ``id`` değerine önek, sonek ve karakter sabitleme yapabilirsiniz. 
Bu işlem için ``DiaWebService`` sınıfında yer alan ``$configurations`` değişkenindeki bazı parametreleri değiştirebilirsiniz.

#### 1. Cari kart kodu için;
```php
'customer' => array(
    'prefix' => 'CK', // önek
    'suffix' => '', // sonek
    'length' => 15 // karakter sayısı
)
```

#### 2. Stok kart kodu için;
```php
'stock' => array(
    'prefix' => 'CK', // önek
    'suffix' => '', // sonek
    'length' => 15 // karakter sayısı
)
```

#### 3. Servis kart kodu için;
```php
'service' => array(
    'prefix' => 'CK', // önek
    'suffix' => '', // sonek
    'length' => 15 // karakter sayısı
)
```

#### 4. Sipariş fiş kodu için;
```php
'order' => array(
    'prefix' => 'CK', // önek
    'suffix' => '', // sonek
    'length' => 15 // karakter sayısı
)
```

## Konfigürasyonları Farklı Bir Yere Taşıma
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
