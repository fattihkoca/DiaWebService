<?php

// Stok kartı (satılacak ürün - malzeme) ekleme işlemi

// DiaWebService sınıfının dosyası dahil edilir
include_once 'src/DiaWebService.php';

// Class çağrılır
$dia = new DiaWebService;

// Oturum Açılır
$dia->login();

// Hesap adı belirlenir
$dia->account_name = 'demo';

// Gönderilecek veriler hazırlanır ("barcode" parametresi hariç geriye kalan tüm parametrelerin örnekteki gibi gönderilmesi zorunludur.)
$data = [
    'id' => '123', // ürün id'si
    'name' => 'Okul Çantası', // ürün adı
    'price' => 100, // ürün fiyatı
    'tax' => 8, // kdv oranı
    'tax_included' => true, // kdv dahil mi
    'is_promotion' => false, // hediye ürün mü
    'barcode' => '123456789000000'
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