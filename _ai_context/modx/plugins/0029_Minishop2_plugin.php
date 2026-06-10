<?php
$event = $modx->event->name;
switch ($event) {
    case 'msOnBeforeCreateOrder';
        $key = 'region';
        $region = $_POST['region'];
        if (array_key_exists($key, $_POST)) {
            if (empty($region)) {
                /*$modx->event->output('Пожалуйста, введите ИНН');*/
            } elseif (!preg_match('/^\d{10}(\d{2})?$/', $region)) {
                $modx->event->output('Введите в поле субпартнер 10 или 12 цифр ИНН.');
            }
        }
        break;
    case 'msOnCreateOrder':
        
        $modx->log(1, '[msOnCreateOrder $_POST] '. print_r($_POST,1));
        
        $message = '';
        $msDelivery = $msOrder->get('delivery');
        $msPayment = $msOrder->get('payment');
        
        $msAddress = $msOrder->getOne('Address');
        $msInn = $msAddress->get('country');
        $mssubpartner = $msAddress->get('region');
        if (preg_match('/[a-zA-Zа-яА-Я]/', $mssubpartner)) {
            $mssubpartner = '';
        }
        $msReceiver = $msAddress->get('receiver');
        $msEmail = $msAddress->get('email');
        $products = $msOrder->getMany('Products');
        $status = [
            'status' => ['nopay', 'noshipped'],
            'inn' => $msInn,
        ];
        $msOrder->set('properties', $status);
        $msOrder->save();
        $message = $msInn .';'. $msEmail .';'. $mssubpartner ."\n";
        foreach ($products as $product) {
            $options = $product->get('options');
            $mids = explode(',', $options['mid']);
            foreach ($mids as $item) {
                $modification = $modx->getObject('msopModification', ['id' => $item]);
                $message .= $modification->get('name'). ';' .$product->get('count') ."\n";
            }
        }
        
        $modx->log(1, '[msOnCreateOrder $message] '. print_r($message,1));
        
        if (!empty($message)) {
            $from  = $modx->getOption('emailsender');
            $emails = 'lic@data-mobile.ru';
            $modx->getService('mail', 'mail.modPHPMailer');
            
    		$modx->mail->set(modMail::MAIL_FROM, $from);
    		$modx->mail->set(modMail::MAIL_FROM_NAME, 'Автозаказ '. $msEmail);
    		
    		$emails = explode(",",$emails);
    		foreach($emails as $te){
    			$modx->mail->address('to', trim($te));
    		}
    		
    		$modx->mail->set(modMail::MAIL_SUBJECT, 'Автозаказ с партнерского раздела DataMobile');
            $modx->mail->set(modMail::MAIL_BODY, $message);
            
            $modx->mail->setHTML(false);
            if (!$modx->mail->send()) {
    			$modx->log(1,'[Письмо не отправлено по причине:] '.$modx->mail->mailer->ErrorInfo);
    		} else {
    		    $modx->log(1, '[msOnCreateOrder mail-send] '. print_r($emails,1));
    		}
    		$modx->mail->reset();
        }
        break;
    case 'msOnChangeInCart':
        $user_id = $modx->user->id;
        $res = $modx->getObject('modResource', 16);
        $array_sale = json_decode($res->getTVValue('partner'), true);
        $sale = [];
        foreach ($array_sale as $item) {
            $sale[$item['level']] = $item['sale'];
        }
        $user = $modx->getObject('modUser', array('id' => $user_id));
        
        if (is_object($user)) {
            $profile = $user->getOne('Profile');
            $inn = $profile->get('field_list_inn');
            if (!empty($inn)) {
                $inn = explode(';', $inn);
                foreach ($inn as $item) {
                    $inn_sale = explode(':', $item);
                    $sale_price = $profile->get('field_iudiscount') ?: $sale[$inn_sale[1]];
                }
            }
        }
        break;
    case 'msOnAddToCart':
        $products = $cart->get();
        $res = $modx->getObject('modResource', 16);
        $array_sale = json_decode($res->getTVValue('partner'), true);
        $sale = [];
        foreach ($array_sale as $item) {
            $sale[$item['level']] = $item['sale'];
        }
        
        $user_id = $modx->user->id;
        $user = $modx->getObject('modUser', array('id' => $user_id));
        if (is_object($user)) {
            $profile = $user->getOne('Profile');
            $inn = $profile->get('field_list_inn');
            if (!empty($inn)) {
                $inn = explode(';', $inn);
                foreach ($inn as $item) {
                    $inn_sale = explode(':', $item);
                    $sale_price = $profile->get('field_iudiscount') ?: $sale[$inn_sale[1]];
                }
            }
        }
        foreach ($products as $key => $product) {
            $currentProduct = $product;
            $price = 0;
            
           if ($product['options']['mid']) {
                $opts = explode(',', $product['options']['mid']);
            } else {
                $opts = explode(',', $product['options']['modification']);
            }
            
            foreach ($opts as $item) {
                $mod = $modx->getObject('msopModification', ['id' => $item, 'active' => 1]);
                if (is_object($mod)) {
                    $price += $mod->get('price');
                }
            }
            $productData = $modx->getObject('msProduct', ['id' => $currentProduct['id']]);
            if (!empty($productData)) {
                $currentProduct['old_price'] = $price;
                if ($sale_price > 0) {
                    $price = ceil($price - ($price * $sale_price / 100));
                }
                $currentProduct['price'] = $price;
                $products[$key] = $currentProduct;
            }
        }
        $cart->set($products);
        break;
    case 'msOnBeforeAddToCart':
        $values = & $modx->event->returnedValues;
        $products = $cart->get();
        $res = $modx->getObject('modResource', 16);
        $array_sale = json_decode($res->getTVValue('partner'), true);
        $sale = [];
        foreach ($array_sale as $item) {
            $sale[$item['level']] = $item['sale'];
        }
        $user_id = $modx->user->id;
        $user = $modx->getObject('modUser', array('id' => $user_id));
        if (is_object($user)) {
            $profile = $user->getOne('Profile');
            $inn = $profile->get('field_list_inn');
            if (!empty($inn)) {
                $inn = explode(';', $inn);
                foreach ($inn as $item) {
                    $inn_sale = explode(':', $item);
                    $sale_price = $profile->get('field_iudiscount') ?: $sale[$inn_sale[1]];
                }
            }
        }
        if (!empty($products)) {
            foreach ($products as $key => $product) {
                $currentProduct = $product;
                $price = 0;
                if ($product['options']['mid']) {
                    $opts = explode(',', $product['options']['mid']);
                } else {
                    $opts = explode(',', $product['options']['modification']);
                }
                foreach ($opts as $item) {
                    $mod = $modx->getObject('msopModification', ['id' => $item, 'active' => 1]);
                    if (is_object($mod)) {
                        $price += $mod->get('price');
                    }
                }
                $productData = $modx->getObject('msProduct', ['id' => $currentProduct['id']]);
                if (!empty($productData)) {
                    $currentProduct['old_price'] = $price;
                    if ($sale_price > 0) {
                        $price = ceil($price - ($price * $sale_price / 100));
                    }
                    $currentProduct['price'] = $price;
                    $products[$key] = $currentProduct;
                }
                
            }
            $cart->set($products);  
        }
        break;
    
}