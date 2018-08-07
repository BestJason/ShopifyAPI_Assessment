<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShopifyAPI;

class ShopifyController extends Controller
{
  public function createWebhookForCustomersCreate()
  {
    try {
      $code = -1;
      $shopifyApi = new ShopifyAPI();
      if (empty($shopifyApi)) {
        $msg = 'Initializing API failed';
        throw new \Exception($msg);
      }
      $webhooks = config('shopify-app.webhooks');
      if (empty($webhooks)) {
          $msg = 'The webhooks is missing, Please set up in config/shopify-app.php first';
          throw new \Exception($msg);
      }
      $topic = env('SHOPIFY_WEBHOOK_CUSTOMERS_CREATE_TOPIC');
      if (empty($topic)) {
        $msg = 'The topic is missing';
        throw new \Exception($msg);
      }
      $address = env('SHOPIFY_WEBHOOK_CUSTOMERS_CREATE_ADDRESS');
      if (empty($address)) {
        $msg = 'The address is missing';
        throw new \Exception($msg);
      }
      $result = $shopifyApi->installWebhook($topic, $address);
      $code = 1;
      $msg = 'ok';
      $response = [
        'code' => $code,
        'msg' => $msg,
        'reponse' => $result,
      ];
    } catch(\Exception $e) {
      $response = [
        'code' => $code,
        'msg' => $e->getMessage(),
      ];
    }
    return $response;
  }
}
