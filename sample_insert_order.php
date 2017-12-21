<?php

// Sipariş fişi ekleme işlemi

// DiaWebService sınıfının dosyası dahil edilir
include_once 'src/DiaWebService.php';

// Class çağrılır
$dia = new DiaWebService;

// Oturum Açılır
$dia->login();

// Hesap adı belirlenir
$dia->account_name = 'demo';

// Gönderilecek veriler hazırlanır ("note_*" ve "address_2" parametreleri hariç geri kalan tüm parametreler zorunlu)
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
    echo 'Sipariş fişi eklendi: #123';
} 
// Hata sonucu bastırılır
else {
    echo $send['message'];
}