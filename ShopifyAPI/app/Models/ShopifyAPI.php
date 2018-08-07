<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OhMyBrew\BasicShopifyAPI as BasicApi;

class ShopifyAPI extends Model
{

  // webhook path of Shopify Api
  const WEBHOOK_PATH = '/admin/webhooks.json';

  // The webhooks ready to create
  protected $webhooks;

  // The Api Key
  protected $apiKey;

  // The Api Password
  protected $apiPassword;

  // The Api Domain
  protected $apiDomain;

  // The Api Object
  protected $api;

  /**
   * The Constructor.
   *
   * @return void
   */
  public function __construct()
  {
    $this->initializeOptions();
    $this->initializeApi();
  }
  
  /**
   * Initialize all Api Options.
   *
   * @return void
   */
  public function initializeOptions()
  {
    try{

      $this->webhooks = setWebhooks(config('shopify-app.webhooks'));

      $this->setApiKey(config('shopify-app.api_key'));
      if (empty($this->apiKey)) {
        throw new \Exception("API Key is missing");
      }

      $this->setApiPassword(config('shopify-app.api_password'));
      if (empty($this->apiPassword)) {
        throw new \Exception("API Password is missing");
      }

      $this->setApiDomain(config('shopify-app.myshopify_domain'));
      if (empty($this->apiDomain)) {
        throw new \Exception("API Domain is missing");
      }

    } catch(\Exception $e) {
      // return 403 http code
      abort(403, $e->getMessage());

    }
  }

   /**
   * Initialize all Api Object.
   *
   * @return void
   */
  public function initializeApi()
  {
    $api = new BasicApi(true);
    $api->setShop($this->shop);
    $api->setApiKey($this->apiKey);
    $api->setApiPassword($this->apiPassword);
    $this->api = $api;
  }
 
  /**
   * Set Api Key.
   *
   * @return void
   */
  public function setApiKey($key)
  {
    $this->apiKey = $key;
  }
 
  /**
   * Set Api Doamin.
   *
   * @return void
   */
  public function setApiDomain($domain)
  {
    $this->apiDomain = $domain;
  }

  /**
   * Set Api Password.
   *
   * @return void
   */
  public function setApiPassword($password)
  {
    $this->apiPassword = $password;
  }

  /**
   * Set Webhooks.
   *
   * @return void
   */
  public function setWebhooks(array $webhooks)
  {
    $this->webhooks = $webhooks;
  }
  
  /**
   * Identify if the webhook is existing.
   *
   * @param array $shopWebhooks webhooks in Shopify currently
   * @param array $webhook webhook ready to add
   * @return boolean
   */
  protected function webhookExists(array $shopWebhooks, array $webhook)
  {
    foreach ($shopWebhooks as $shopWebhook) {
      if ($shopWebhook->address === $webhook['address']) {
        // Found the webhook in our list
        return true;
      }
    }
    return false;
  }

  /**
   * Install a new webhook.
   *
   * @param string $topic the topic of webhook
   * @param string $address the address of webhook
   * @param string $format the format of webhook
   * @return array
   */
  public function installWebhook($topic, $address, $format = "json")
  {
    try {
      $created = [];
      
      if (empty($topic)) {
        throw new \Exception('Topic is missing');
      }

      if (empty($address)) {
        throw new \Exception('Address is missing');
      }

      if (empty($format)) {
        throw new \Exception('Format is missing');
      }

      $webhook = [
        "topic" => $topic,
        "address" => $address,
        "format" => $format
      ];
      // Get all webhooks in Shopify
      $shopWebhooks = $this->api->rest(
        'GET',
        self::WEBHOOK_PATH,
        ['limit' => 250, 'fields' => 'id,address']
      )->body->webhooks;

      // Identify if the webhook is existing
      foreach($this->webhooks as $webhook) {
        if (!$this->webhookExists($shopWebhooks, $webhook)) {
          $this->api->rest('POST', self::WEBHOOK_PATH, ['webhook' => $webhook]);
          $created[] = $webhook;
        }
      }
      return $created;
    } catch(\Exception $e) {
      
      abort('400', $e->getMessage());

    }
  }



}
