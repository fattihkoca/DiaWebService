<?php

// Hizmet kartı (satılacak hizmet) ekleme işlemi

// DiaWebService sınıfının dosyası dahil edilir
include_once 'src/DiaWebService.php';

// Class çağrılır
$dia = new DiaWebService;

// Oturum açılır
$dia->login();

// Hesap adı belirlenir
$dia->account_name = 'demo';

// Gönderilecek veriler hazırlanır (Aşağıdaki tüm parametrelerin örnekteki gibi gönderilmesi zorunludur.)
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
    echo 'Servis kartı eklendi: #123';
} 
// Hata sonucu bastırılır
else {
    echo $send['message'];
}