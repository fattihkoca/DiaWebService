<?php

/**
 * Dia Web Servisi
 * Özelleştirmeler: $account_name, $configurations;
 */

class DiaWebService
{
    // Seçilen hesap adı (Konfigürasyonlarda "accounts" anahtarlarında olmak zorundadır)
    public $account_name = 'remzihoca.com';

    // Web servis yapılandırma ayarları
    private $configurations = array(
        // dia sunucusu tarafındaki kayıtlı hesap bilgileri
            'accounts' => array(
            // demo hesabı
                'demo' => array(
                    'account' => 'diademo', // tanımlanan sunucu kodu
                    'company' => 34, // firma kodu
                    'branch' => null, // şube kodu
                    'depository' => null, // depo kodu
                    'username' => 'ws',
                    'password' => 'ws',
                    'lang' => 'tr',
                    'disconnect_same_user' => true, // önceki oturumları kapatır
                ),

                'demo2' => array(
                    'account' => 'diademo', // tanımlanan sunucu kodu
                    'company' => 1, // firma kodu
                    'branch' => 1234, // şube kodu
                    'depository' => 4321, // depo kodu
                    'username' => 'ws',
                    'password' => 'ws',
                    'lang' => 'tr',
                    'disconnect_same_user' => true, // önceki oturumları kapatır
                ),
            ),

        // cari kart isimlendirme ayarları
            'customer' => array(
                'prefix' => 'CK', // önek
                'suffix' => '', // son ek
                'length' => 15 // karakter sayısı
            ),

        // stok kartı isimlendirme ayarları
            'stock' => array(
                'prefix' => 'SK', // önek
                'suffix' => '', // sonek
                'length' => 10 // karakter sayısı
            ),

        // hizmet kartı isimlendirme ayarları
            'service' => array(
                'prefix' => 'HK', // önek
                'suffix' => '', // sonek
                'length' => 10 // karakter sayısı
            ),

        // sipariş fişi isimlendirme ayarları
            'order' => array(
                'prefix' => 'SF', // önek
                'suffix' => '', // sonek
                'length' => 10 // karakter sayısı
            ),
        ),

    // Gönderilecek istek standartları
        $request = array(
            'url' => 'ws.dia.com.tr/api/v3/', // isteğin gönderileceği sunucu
            'method' => 'post', // veri gönderim metodu
            'format' => 'json', // veri alış-veriş formatı
            'secure' => true, // https bağlantısı
        ),

    // Cari, stok, hizmet, sipariş işlemlerinin alt parametreleri
        $parameters = array(
        // Cari kart
            'customer' => array(
                'module' => 'scf',
                'field' => 'carikartkodu',
                'fetch' => 'scf_carikart_listele',
                'insert' => 'scf_carikart_ekle',
                'update' => 'scf_carikart_guncelle',
                'delete' => 'scf_carikart_sil',
                'requireds' => array(
                    'id', 'firstname', 'lastname', 'email', 'address', 
                    'city', 'phone', 'tckn'
                ),
            ),

        // Stok kartı
            'stock' => array(
                'module' => 'scf',
                'field' => 'stokkartkodu',
                'fetch' => 'scf_stokkart_listele',
                'insert' => 'scf_stokkart_ekle',
                'update' => 'scf_stokkart_guncelle',
                'delete' => 'scf_stokkart_sil',
                'requireds' => array(
                    'id', 'name', 'price', 'tax', 'tax_included', 'is_promotion'
                ),
            ),

        // Servis kartı
            'service' => array(
                'module' => 'scf',
                'field' => 'hizmetkartkodu',
                'fetch' => 'scf_hizmetkart_listele',
                'insert' => 'scf_hizmetkart_ekle',
                'update' => 'scf_hizmetkart_guncelle',
                'delete' => 'scf_hizmetkart_sil',
                'requireds' => array(
                    'id', 'name', 'price', 'tax', 'tax_included', 'is_promotion'
                ),
            ),

        // Sipariş fişi
            'order' => array(
                'module' => 'scf',
                'field' => 'fisno',
                'fetch' => 'scf_siparis_listele',
                'insert' => 'scf_siparis_ekle',
                'update' => 'scf_siparis_guncelle',
                'delete' => 'scf_siparis_sil',
                'requireds' => array(
                    'id', 'customer_id', 'date', 'installment', 
                    'address', 'city', 'phone', 'is_eft', 'currency', 'products'
                ),
            ),
        ),

    // Class ile ilgili bazı önemli değişkenler
        $conf, $session, $account, $company, $period, 
        $module, $branch, $depository, $exception_title;

    // İşlemlere başlamadan önce gerekli ekipmanlar tanımlanır
    public function __construct()
    {
        // Yukarıda yer alan konfigürasyonları farklı bir alana taşımak isterseniz 
        // $this->configurations değişkenini değiştirmeniz yeterlidir
        $this->conf = $this->configurations;

        // İstisnalar için standart başlık
        $this->exception_title = __class__ . ' Error:';
    }

    // İşlem sonucunu döndürme
    private function response_handle(
        $success = true, $message = 'OK', $result = array()
    )
    {
        return array(
            'success' => $success,
            'message' => $message,
            'result' => $result
        );
    }

    // İstisna döndürme aracı
    private function exception_handle(
        $message = 'An unknown error has occurred!', $result = array()
    )
    {
        throw new Exception(
            PHP_EOL . $this->exception_title . 
            PHP_EOL . $message . 
            PHP_EOL
        );
        return false;
    }

    /**
     * İstenen işleme (cari, stok, hizmet, sipariş) uygun parametreler
     *
     * @param string $type : Alan türü [customer|stock|service|order]
     * @return [module: Dia modülü, field: id yerine geçen field, 
     * fetch: getirme işlemi anahtarı, 
     * insert: ekleme işlemi anahtarı, 
     * update: güncelleme işlemi anahtarı, 
     * delete: silme işlemi anahtarı, 
     * requireds : commit işlemlerinde belirtilmesi zorunlu parametreler]
     */
    private function transaction_parameters($type = 'customer')
    {
        // Eğer uygun işlem türü seçilmemişse hata döndür
        if (!array_key_exists($type, $this->parameters)) {
            $message = 'Unknown transaction type: ' . $type;
            $this->exception_handle();
            return $this->response_handle(array(
                false,
                $message
            ));
        }

        $params = $this->parameters[$type];
        $params['type'] = $type;

        return $params;
    }

    // create, update, delete işlemlerinin ön kontrolü
    // zorunlu alan kontrolü + varlık kontrolü
    private function modification_controller(
        $type = 'customer', $transaction = 'insert', $data = array()
    )
    {
        if (!is_array($data)) {
            $message = 'The "data" argument must be array.';
            $this->exception_handle();
            return $this->response_handle(array(
                false,
                $message
            ));
        }

        // uygun parametreler getirilir
        $params = $this->transaction_parameters($type);

        // zorunlu alanlar getirilir
        $required_fields = $params['requireds'];

        $accepted = true;
        $message = null;

        // zorunlu alanlar kontrol edilir
        foreach ($required_fields as $field) {
            if (!$accepted) {
                break;
            }

            if (is_array($field)) {
                continue;
            }

            if (!array_key_exists($field, $data)) {    
                // zorunlu alanları haritala
                $fields_maps = print_r($required_fields, true);
                $message = 'Has missing fields. Required fields: ' . 
                    PHP_EOL . $fields_maps;
                $accepted = $this->exception_handle($message);
                break;
            }
        }

        // zorunlu alan kontrolüne takılmışsa
        if (!$accepted) {
            return $this->response_handle(array(
                false,
                $message
            ));
        }

        // id üzerinden veriye erişilir
        $exists = $this->fetch_by_id($type, $data['id']);

        // cari kart varken insert işlemi yapılmışsa içinde id olan sonucu döndür
        if ($transaction == 'insert' && $exists['success']) {
            return $this->response_handle(
                true,
                'OK',
                array(
                    'id' => $data['id']
                )
            );
        }

        // cari kart bulunamamış ve update veya delete yapılacaksa 
        if ($transaction != 'insert' && !$exists['success']) {
            // update işlemi ise işlem türünü insert olarak değiştir
            // amaç hata döndürmek yerine yeniden kaydetmek
            if ($transaction == 'update') {
                $transaction = 'insert';
            }

            // delete işlemi ise içinde id olan sonucu döndür
            elseif ($transaction == 'delete') {
                return $this->response_handle(
                    true,
                    'OK',
                    array(
                        'id' => $data['id']
                    )
                );
            }
        }

        // zorunlu parametreler hazırsa ve varlık kontrolü sağlanmışsa uygun metoda geç
        return call_user_func_array(
            array($this, $transaction . '_' . $type),
            array($data, $params)
        );
    }

    // insert, update, delete verileri Dia'ya gönderilir
    private function modification_send($data, $params, $id)
    {
        // yeni cari kart isteği gönderilir
        $response = $this->send_request($params['module'], $data);

        if (!isset($response['code']) || $response['code'] != 200) {
            if (isset($response['msg'])) {
                $this->exception_handle($response['msg']);

                return $this->response_handle(
                    false,
                    $response['msg']
                );
            }

            $msg = 'The ' . $params['type'] . ' could not be inserted.' . 
                PHP_EOL . 'Response msg: ' . print_r($response, true);

            // Hata döndürülür
            $this->exception_handle($msg);
            return $this->response_handle(
                false,
                $msg
            );
        }

        // sonuç döndürülür
        return $this->response_handle(
            true,
            'OK',
            array(
                'id' => $id
            )
        );
    }

    // getirme işlemlerinde parametre kontrolü yapma ve veriyi gönderme
    private function fetch_send($type = 'customer', $data = array())
    {
        $error = null;

        if (empty($data) || !is_array($data)) {
            $error = 'The first argument must be array.';
        } elseif (!isset($data['filters']) || !is_array($data['filters'])) {
            $error = 'filters parameter (Array) must be specified.';
        } elseif (!isset($data['filters'][0]) || !is_array($data['filters'][0])) {
            $error = 'filters parameter\'s first parameter must be array. 
                ["filters" => [["field" => ...]]';
        }

        // hata varsa döndür
        if (!empty($error)) {
            $this->exception_handle($error);
            return $this->response_handle(
                false,
                $error
            );
        }

        // uygun parametreler alınır
        $params = $this->transaction_parameters($type);

        // bazı alanlar yeniden derlenir
        foreach ($data['filters'] as &$filter) {
            // eğer id şeklinde alan belirtilmişse kodu dia formatında yapılandır
            if (array_key_exists('field', $filter)) {
                if ($filter['field'] == 'id') {
                    $filter['field'] = $params['field'];
                    $filter['value'] = $this->format_id($type, $filter['value']);
                }
            }
        }

        // limit verisi düzenlenir
        $data['limit'] = !isset($data['limit']) || !is_numeric($data['limit']) 
            ? 1 : $data['limit'];

        // sıralama işlemi düzenlenir
        $data['sorts'] = isset($data['sorts']) && is_array($data['sorts']) 
            && isset($data['sorts'][0]) && is_array($data['sorts'][0]) 
            ? $data['sorts'] : array(array(
                'field' => $params['field'],
                'sorttype' => 'DESC'
            ));

        // ---------

        // veriler yeniden derlenir
        $data = array(
            $params['fetch'] => array(
                'session_id' => $this->session,
                'firma_kodu' => $this->company,
                'donem_kodu' => $this->period,
                'filters' => $data['filters'],
                'limit' => $data['limit']
            )
        );

        // getirme isteği Dia'ya gönderilir
        $response = $this->send_request($params['module'], $data);

        if (!isset($response['code']) || $response['code'] != 200) {
            if (isset($response['msg'])) {
                $this->exception_handle($response['msg']);

                // hata döndürülür
                return $this->response_handle(
                    false,
                    $response['msg']
                );
            }

            $msg = 'There was an error when checking ' . $type . ' entity.';

            // hata döndürülür
            $this->exception_handle($msg);
            return $this->response_handle(
                false,
                $msg
            );
        }

        // Dia'dan dönen sonuç bilgisi alınır
        $result = is_array($response) && !empty($response['result']) 
            && is_array($response['result']) && count($response['result']) 
            ? $response['result'] : false;

        // sonuç dönmemişse
        if (!$result) {
            // hata döndürülür
            return $this->response_handle(
                false,
                'NOT FOUND'
            );
        }
        
        // Dia'dan alın doğrulanmış sonuç döndürülür
        return $this->response_handle(
            true,
            'OK',
            $result
        );
    }

    /**
     * Yeni veri kaydı ekleme isteği gönderme
     *
     * @param string $type : Alan türü [customer|stock|service|order]
     * @param array $data
     * @return void
     */
    public function insert($type = 'customer', $data = array())
    {
        return $this->modification_controller($type, __FUNCTION__, $data);
    }

    /**
     * Veri kaydı güncelleme isteği gönderme
     *
     * @param string $type : Alan türü [customer|stock|service|order]
     * @param array $data
     * @return void
     */
    public function update($type = 'customer', $data = array())
    {
        return $this->modification_controller($type, __FUNCTION__, $data);
    }

    /**
     * Veri kaydı silme isteği gönderme
     *
     * @param string $type : Alan türü [customer|stock|service|order]
     * @param array $data
     * @return void
     */
    public function delete($type = 'customer', $data = array())
    {
        return $this->modification_controller($type, __FUNCTION__, $data);
    }

    /**
     * Filtre, sıralama, vs isteklere göre verileri getirir
     *
     * @param string $type Alan türü [customer|stock|service|order]
     * @param array $data array('filters' => [['id' => 123]], 'limit' => 1)
     * @return void
     */
    public function fetch($type = 'customer', $data = array())
    {
        return $this->fetch_send($type, $data);
    }

    /**
     * Id üzerinden veri getirilir
     *
     * @param string $type : Alan türü [customer|stock|service|order]
     * @param void $id
     * @return boolean
     */
    public function fetch_by_id($type = 'customer', $id = null)
    {
        $error = null;

        // eksik parametre uyarıları
        if (empty($type)) {
            $error = '$type must be specified.';
        } elseif (empty($id)) {
            $error = '$id must be specified.';
        }

        // hata mevcutsa hata döndür
        if (!empty($error)) {
            $this->exception_handle($msg);
            return $this->response_handle(
                false,
                $error
            );
        }

        // uygun parametreler getirilir
        $params = $this->transaction_parameters($type);

        // kontrol edilecek verinin referans bilgileri hazırlanır
        $data = array(
            'filters' => array(
                array(
                    'field' => $params['field'],
                    'operator' => '=',
                    'value' => $this->format_id($type, $id),
                )
            ),
            'limit' => 1
        );

        return $this->fetch_send($type, $data);
    }

    // cari kart ekleme
    private function insert_customer($data, $params)
    {        
        // döndürülecek id
        $id = $data['id'];

        // id formatı kod formatına ayarlanır
        $format_code = $this->format_id('customer', $id);

        // gönderilecek veriler hazırlanır
        $data = array(
            $params['insert'] => array(
                'session_id' => $this->session,
                'firma_kodu' => $this->company,
                'donem_kodu' => $this->period,
                'kart' => array(
                    $params['field'] => $format_code,
                    'unvan' => strval($data['firstname'] . ' ' . $data['lastname']),
                    'm_adresler' => array(
                        array(
                            'adres1' => strval($data['address']),
                            'adres2' => strval($data['city']),
                            'anaadres' => 1,
                            'telefon1' => strval($data['phone']),
                            'ceptel' => strval($data['phone'])
                        )
                    ),
                    'il' => strval($data['city']),
                    'eposta' => strval($data['email']),
                    'tckimlikno' => strval($data['tckn']),
                    'kisaaciklama' => '-',
                ),
            )
        );

        // veriler gönderilir
        return $this->modification_send($data, $params, $id);
    }

    // stok kartı ekleme
    private function insert_stock($data, $params)
    {
        // döndürülecek id
        $id = $data['id'];

        // id formatı kod formatına ayarlanır
        $format_code = $this->format_id('stock', $id);

        // YMML: Yarı mamul
        $stock_type = $data['is_promotion'] ? 'YMML' : 'TCR';

        // Barkod belirtilmişse ekle
        $barcode = isset($data['barcode']) ? $data['barcode'] : '';

        // Kdv dahil mi hariç mi
        $tax_included = $data['tax_included'] ? 'D' : 'H';

        // gönderilecek veriler hazırlanır
        $data = array(
            $params['insert'] => array(
                'session_id' => $this->session,
                'firma_kodu' => $this->company,
                'donem_kodu' => $this->period,
                'kart' => array(
                    $params['field'] => $format_code,
                    'aciklama' => $data['name'],
                    'kdvalis' => $this->format_number($data['tax']),
                    'kdvsatis' => $this->format_number($data['tax']),
                    'kdvsatistoptan' => $this->format_number($data['tax']),
                    'stokkartturu' => $stock_type,
                    'm_birimler' => array(
                        array(
                            'fiyat1' => intval($this->format_number($data['price'])),
                            'kdvdurumu1' => $tax_included,
                            '_key_sis_stok_birim_listesi' => array(
                                'birimadi' => 'ADET'
                            ),
                            'm_barkodlar' => array(
                                array(
                                    'barkod' => $barcode
                                )
                            ),
                        )
                    ),
                ),
            )
        );

        // veriler gönderilir
        return $this->modification_send($data, $params, $id);
    }

    // hizmet kartı ekleme
    private function insert_service($data, $params)
    {
        // döndürülecek id
        $id = $data['id'];

        // id formatı kod formatına ayarlanır
        $format_code = $this->format_id('service', $id);

        // kdv dahil mi hariç mi
        $tax_included = $data['tax_included'] ? 'D' : 'H';

        $service_type = 'V';

        $data = array(
            $params['insert'] => array(
                'session_id' => $this->session,
                'firma_kodu' => $this->company,
                'donem_kodu' => $this->period,
                'kart' => array(
                    $params['field'] => $format_code,
                    'aciklama' => $data['name'],
                    'kdvalis' => $this->format_number($data['tax']),
                    'kdvsatis' => $this->format_number($data['tax']),
                    'kdvsatistoptan' => $this->format_number($data['tax']),
                    'hizmetkartturu' => $service_type,
                    'm_birimler' => array(
                        array(
                            'fiyat1' => intval($this->format_number($data['price'])),
                            'kdvdurumu1' => $tax_included,
                            '_key_sis_stok_birim_listesi' => array(
                                //'birimkod' => 'ADET',
                                'birimadi' => 'ADET'
                            ),
                        )
                    ),
                ),
            )
        );

        // veriler gönderilir
        return $this->modification_send($data, $params, $id);
    }

    // sipariş fişi ekleme
    private function insert_order($data, $params)
    {
        // Döndürülecek id
        $id = $data['id'];

        // id formatı kod formatına ayarlanır
        $format_code = $this->format_id('order', $id);

        // Sipariş tarihi
        $order_date = date('Y-m-d', $data['date']);

        // Sipariş saati
        $order_hour = date('H:i:s', $data['date']);

        // Havale/EFT ödemesi mi?
        $is_eft = $data['is_eft'] ? 'E' : 'H';

        // Para birimi
        $currency = $data['currency'] == 'TRY' || $data['currency'] == 'YTL' 
            ? 'TL' : $data['currency'];

        $total_price = 0;

        $items = array();

        // Sipariş kalemleri hazırlanır
        foreach ($data['products'] as $item) {
            if (!isset($item['id']) || !isset($item['price']) || 
                !isset($item['quantity']) || !isset($item['discount'])) {
                $msg = 'Has missing fields on "product" parameter. 
                    Required fields: "id", "price", "quantity", "discount"';
                $this->exception_handle($msg);

                return $this->response_handle(
                    false,
                    $msg
                );

                break;
            }

            $item_key = $item_unit_key = null;

            // KDV dahil mi?
            $tax_included = isset($item['tax_included']) ? 'D' : 'H';

            // Ürün tipi
            $item_type = isset($item['is_physical']) && $item['is_physical'] 
                ? 'MLZM' : 'HZMT';

            //$is_promotion = isset($item['is_promotion']) ? 0 : 0;
            $is_promotion = 0;

            // ürün türü malzeme ise stoklarda ara
            if ($item_type == 'MLZM') {
                // ürünün detaylarına id.si üzerinden erişilir
                $item_detail = $this->fetch_by_id('stock', $item['id']);

                if (!$item_detail['success'] || !isset($item_detail['result']) 
                    || !isset($item_detail['result'][0])) {
                    $msg = '#' . $item['id'] . ' stock id not found.';
                    $this->exception_handle($msg);
                    return $this->response_handle(
                        false,
                        $msg
                    );
                }

                $item_detail = $item_detail['result'][0];

                if (isset($item_detail['_key'])) {
                    $item_key = $item_detail['_key'];
                }

                if (isset($item_detail['birimkeyleri'])) {
                    $item_unit_key = $item_detail['birimkeyleri'];
                }

                if (empty($item_key) || empty($item_unit_key)) {
                    $msg = $item['id'] . ' stock code not exists or didn\'t found.';
                    $this->exception_handle($msg);
                    return $this->response_handle(
                        false,
                        $msg
                    );
                    break;
                }
            } 
            // ürün türü malzeme değilse hizmetlerde ara
            else {
                // ürünün detaylarına id.si üzerinden erişilir
                $item_detail = $this->fetch_by_id('service', $item['id']);

                if (!$item_detail['success'] || !isset($item_detail['result']) 
                    || !isset($item_detail['result'][0])) {
                    $msg = $item_service_code . ' service code not found.';
                    $this->exception_handle($msg);
                    return $this->response_handle(
                        false,
                        $msg
                    );
                }

                $item_detail = $item_detail['result'][0];

                if (isset($item_detail['_key'])) {
                    $item_key = $item_detail['_key'];
                }

                if (isset($item_detail['birimkeyleri'])) {
                    $item_unit_key = $item_detail['birimkeyleri'];
                }

                if (empty($item_key) || empty($item_unit_key)) {
                    $msg = $item_service_code . ' service code not exists or didn\'t found.';
                    $this->exception_handle($msg);
                    return $this->response_handle(
                        false,
                        $msg
                    );
                    break;
                }
            }

            $note_1 = isset($item['note_1']) ? $item['note_1'] : '';
            $note_2 = isset($item['note_2']) ? $item['note_2'] : '';

            // Sipariş kalemi tanımlanır
            $items[] = array(
                '_key_kalemturu' => $item_key,
                '_key_scf_kalem_birimleri' => $item_unit_key,
                'birimfiyati' => $this->format_number($item['price']),
                'tutari' => $this->format_number($item['price']),
                'sonbirimfiyati' => $this->format_number($item['price']),
                'yerelbirimfiyati' => $this->format_number($item['price']),
                'kdv' => $this->format_number($item['tax']),
                'kdvdurumu' => $tax_included,
                'miktar' => $this->format_number($item['quantity']),
                'anamiktar' => $this->format_number($item['quantity']),
                'kalemturu' => $item_type,
                'indirim1' => $this->format_number($item['discount']),
                '_key_sis_doviz' => array(
                    'adi' => $currency
                ),
                '_key_scf_promosyon' => $is_promotion,
                'dovizkuru' => $this->format_number(1),
                'note' => $note_1,
                'note2' => $note_2,
                'onay' => 'KABUL',
            );

            $total_price += ($item['price'] - ($item['price'] * $item['discount'] / 100) 
                * $item['quantity']);
        }

        // Sipariş notları
        $note_1 = isset($data['note_1']) ? $data['note_1'] : '';
        $note_2 = isset($data['note_2']) ? $data['note_2'] : '';
        $note_3 = isset($data['note_3']) ? $data['note_3'] : '';

        // Adres 2 slotu doldurulmuşsa ekle
        $address2 = isset($data['address_2']) ? $data['address_2'] : '';

        // Sipariş verileri derlenir
        $data = array(
            $params['insert'] => array(
                'session_id' => $this->session,
                'firma_kodu' => $this->company,
                'donem_kodu' => $this->period,
                'kart' => array(
                    $params['field'] => $format_code,
                    'belgeno' => $format_code,
                    '_key_scf_carikart' => array(
                        'carikartkodu' => $this->format_id('customer', $data['customer_id'])
                    ),
                    'm_kalemler' => $items, // kalemler
                    'tarih' => $order_date,
                    'saat' => $order_hour,
                    'toplam' => $this->format_number($total_price),
                    'havaleliodeme' => $is_eft,
                    'sevkadresi1' => $data['address'],
                    'sevkadresi2' => $address2,
                    'sevkadresi3' => $data['city'],
                    'teslimat_telefon' => $data['phone'],
                    'aciklama1' => $note_1,
                    'aciklama2' => $note_2,
                    'aciklama3' => $note_3,
                    'turu' => '2',
                    'onay' => 'KABUL',
                    '_key_sis_sube_source' => array(
                        '_key' => $this->branch
                    ),
                    '_key_sis_depo_source' => array(
                        '_key' => $this->depository
                    ),
                    '_key_sis_doviz' => array(
                        'adi' => $currency
                    ),
                    '_key_sis_doviz_raporlama' => array(
                        'adi' => $currency
                    ),
                    'dovizkuru' => $this->format_number(1),
                    'raporlamadovizkuru' => $this->format_number(1),
                    'kdvduzenorani' => '-'
                ),
            )
        );

        // veriler gönderilir
        return $this->modification_send($data, $params, $id);
    }

    // Dönem kodu alma ve şube ile depo kodu kontrolleri
    // Cari işlemleri ve hareketleri için dönem kodunu ayarlamak gerekiyor
    public function set_period_code()
    {
        $module = 'sis';

        $data = array(
            'sis_yetkili_firma_donem_sube_depo' => array(
                'session_id' => $this->session
            )
        );

        $response = $this->send_request($module, $data);

        if (!isset($response['code']) || $response['code'] != 200 || !isset($response['result'])) {
            if (isset($response['msg'])) {
                $this->exception_handle($response['msg']);
                return $this->response_handle(
                    false,
                    $response['msg']
                );
            } else {
                $msg = 'There was an error when fetching the last period code.';
                $this->exception_handle($msg);
                return $this->response_handle(
                    false,
                    $msg
                );
            }
        }

        $companies = $response['result'];

        // son dönem kodu
        $last_period = 0;

        // son şube kodu
        $last_branch = 0;

        // son depo kodu
        $last_depository = 0;

        // eşleşen firmanın en büyük dönem kodu alınır
        foreach ($companies as $company) {
            if (!isset($company['firmakodu']) || $company['firmakodu'] != $this->company) {
                continue;
            }

            if (!isset($company['donemler']) || !is_array($company['donemler'])) {
                break;
            }

            $periods = $company['donemler'];

            // dönem kodu en büyük olanı al
            foreach ($periods as $period) {
                if (isset($period['donemkodu']) && $period['donemkodu'] > $last_period) {
                    $last_period = $period['donemkodu'];
                }
            }

            // şube kodu girilmemişse ön tanımlı şube kodu tanımlanır
            if (empty($this->branch)) {
                $this->branch = isset($company['ontanimli__key_sis_sube']) 
                    ? $company['ontanimli__key_sis_sube'] : null;
            }

            // depo kodu girilmemişse ön tanımlı depo kodu tanımlanır
            if (empty($this->depository)) {
                $this->depository = isset($company['ontanimli__key_sis_depo']) 
                    ? $company['ontanimli__key_sis_depo'] : null;
            }
        }

        $this->period = $last_period;

        if (!$this->period) {
            $msg = 'There was an error when fetching the last period code.';
            $this->exception_handle($msg);
            return $this->response_handle(
                false,
                $msg
            );
        }

        // oturum açılınca success döndür
        return $this->response_handle(
            true,
            'OK'
        );
    }

    // Verilen id değeri, Dia için özel kod haline getirilir
    private function format_id($type = 'customer', $id)
    {
        switch ($type) {
            case 'stock': // stok kartı
                $conf = $this->conf['stock'];
                break;

            case 'service': // servis kartı
                $conf = $this->conf['service'];
                break;

            case 'order': // sipariş fişi
                $conf = $this->conf['order'];
                break;

            default: // cari kart
                $type = 'customer';
                $conf = $this->conf['customer'];
                break;
        }

        // konfigürasyonlardan ön ek, son ek, karakter sayısı alınır
        $prefix = $conf['prefix'];
        $suffix = $conf['suffix'];
        $length = $conf['length'];

        $char_size = strlen($prefix) + strlen($suffix);
        $char_size = $length - $char_size;

        // eksik karakter sayısı kadar "0" ekle
        $code = sprintf('%0' . $char_size . 's', $id);

        // kod, önek ve soneklerle son halini alır
        return $prefix . $code . $suffix;
    }

    // Verilen değeri virgülden sonra en az iki hane olacak şekilde ayarla
    private function format_number($value = 0)
    {
        return is_float($value) ? $value : number_format($value, 2, '.', '');
    }

    // Oturum açma ve session_id alma
    public function login()
    {
        $module = 'sis';

        if (!array_key_exists($this->account_name, $this->conf['accounts'])) {
            $msg = 'Your "account_name" is not found in "accounts" configuration.';
            $this->exception_handle($msg);
            return $this->response_handle(
                false,
                $msg
            );
        }

        $data = $this->conf['accounts'][$this->account_name];

        // sunucu kodu
        $this->account = $this->conf['accounts'][$this->account_name]['account'];

        // firma kodu
        $this->company = $this->conf['accounts'][$this->account_name]['company'];

        // şube kodu
        $this->branch = $this->conf['accounts'][$this->account_name]['branch'];

        // depo kodu
        $this->depository = $this->conf['accounts'][$this->account_name]['depository'];

        // girişte kullanılmayacak veriler silinir
        unset($data['account'], $data['company'], $data['branch'], $data['depository']);

        $data = array(
            'login' => $data
        );

        // Oturum açma isteği gönderilir
        $response = $this->send_request($module, $data);

        // Hatalar döndürülür
        if (isset($response['code']) && isset($response['msg'])) {
            if ($response['code'] != 200) {
                return $this->exception_handle($response['msg']);
            }
            $this->session = $response['msg'];
        } else {
            $msg = 'There was an error when signing in. Response msg: ' . 
                PHP_EOL . print_r($response, true);
            $this->exception_handle($msg);
            return $this->response_handle(
                false,
                $msg
            );
        }

        // Güncel dönem kodu ayarlanır
        // Şube ve depo kodları kontrol edilir
        $this->set_period_code();

        // Sonuç döndürülür
        return $this->response_handle(
            true,
            'OK',
            array(
                'session' => $response['msg']
            )
        );
    }

    // Oturumu kapatma
    public function logout()
    {
        $module = 'sis';

        $data = array(
            'logout' => array(
                'session_id' => $this->session
            )
        );

        $response = $this->send_request($module, $data);

        if (isset($response['code']) && $response['code'] == 200 
            && isset($response['msg'])) {
            $this->session = null;
        } else {
            $msg = 'There was an error when signing out.';
            $this->exception_handle($msg);
            return $this->response_handle(
                false,
                $msg
            );
        }

        return $response;
    }

    // Curl ile Dia tarafına istek gönderme
    private function send_request($module = null, $data = array())
    {
        // Data uygun formatta değilse hata döndür
        if (!is_array($data)) {
            $msg = 'Request not sent. Module is "' . $module . '"';
            $this->exception_handle($msg);
            return $this->response_handle(
                false,
                $msg
            );
        }

        // Get, Post
        $method = strtoupper($this->request['method']);

        // Json, another
        $format = $this->request['format'];

        // Protokol tanımlanır
        $protocol = $this->request['secure'] ? 'https' : 'http';
        $protocol = $protocol . '://';

        // Sunucu kodu tanımlanır
        $account = rtrim($this->account, '.') . '.';

        // Sunucu adresi tanımlanır
        $url = rtrim($this->request['url'], '/') . '/';

        // İşlem yapılacak Dia modülü tanımlanır
        $module = rtrim($module, '/') . '/';

        // İsteğin gönderileceği url son halini alır
        $url = $protocol . $account . $url . $module . $format;

        if ($format == 'json') {
            // Gönderilecek veriler JSON olarak kodlanır
            $built_data = json_encode($data);
        } else {
            $built_data = http_build_query($data);

            // Url devamına query parametreler eklenir
            if ($method == "GET") {
                $url = rtrim($url, '/');
                $qs_mark = strpos($url, '?') !== false ? '&' : '?';
                $url = $url . '/' . $qs_mark . $built_data;
            }
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($format == 'json') {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($built_data)
            ));
        }

        if ($method != 'GET') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $built_data);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        if ($format == 'json') {
            $response_decode = json_decode($response, true);

            // Dönen JSON verisi çözülemezse hata ver
            if ($response_decode === false) {
                $msg = 'The response data could not be converted to json format. 
                    Module is "' . $module . '"';
                $this->exception_handle($msg);
                return $this->response_handle(
                    false,
                    $msg
                );
            }

            return $response_decode;
        }

        if (is_array($response)) {
            $response['DiaWebService'] = array(
                'module' => $module,
                'data' => $data
            );
        }

        return $response;
    }

    public function __destruct()
    {
        // İşlemler bitince oturum kapatılır
        $this->logout();
    }
}

?>