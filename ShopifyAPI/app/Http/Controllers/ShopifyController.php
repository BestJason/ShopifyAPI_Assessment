<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShopifyAPI;

class ShopifyController extends Controller
{

  /**
   * Create Webhook for customers create
   *
   * @return array
   */
  public function createWebhookForCustomersCreate()
  {
    try {

      // default code value
      $code = -1;

      // get api object of shopify app
      $shopifyApi = new ShopifyAPI();
      if (empty($shopifyApi)) {
        $msg = 'Initializing API failed';
        throw new \Exception($msg);
      }

      // Need to config webhook first
      $webhooks = config('shopify-app.webhooks');
      if (empty($webhooks)) {
          $msg = 'The webhooks is missing, Please set up in config/shopify-app.php first';
          throw new \Exception($msg);
      }

      // get webhook topic from .env file
      $topic = env('SHOPIFY_WEBHOOK_CUSTOMERS_CREATE_TOPIC');
      if (empty($topic)) {
        $msg = 'The topic is missing';
        throw new \Exception($msg);
      }

      // get webhook address from .env file
      $address = env('SHOPIFY_WEBHOOK_CUSTOMERS_CREATE_ADDRESS');
      if (empty($address)) {
        $msg = 'The address is missing';
        throw new \Exception($msg);
      }

      // install webhook
      $result = $shopifyApi->installWebhook($topic, $address);

      // return standard API data format
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
