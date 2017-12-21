<?php

// Cari kart (müşteri hesabı) ekleme işlemi

// DiaWebService sınıfının dosyası dahil edilir
include_once 'src/DiaWebService.php';

// Class çağrılır
$dia = new DiaWebService;

// Oturum Açılır
$dia->login();

// Hesap adı belirlenir
$dia->account_name = 'demo';

// Gönderilecek veriler hazırlanır (Tamamı zorunlu alanlar)
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