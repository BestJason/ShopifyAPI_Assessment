<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \DrewM\MailChimp\MailChimp;
use Illuminate\Support\Facades\Log;
use Newsletter;

class MailChimpAPI extends Model
{

  // Getting and creating the merge_fields path
  const CREATE_MERGE_FIELDS_PATH = '/lists/{list_id}/merge-fields';

  // the fields that are need to be stored into the merge_fields
  const MERGE_FIELDS_MAPPING = [
    'last_name', 
    'first_name', 
    'accepts_marketing',
    'orders_count', 
    'total_spent', 
    'last_order_id', 
    'last_order_name', 
    'note', 
    'state',
    'verified_email', 
    'tags', 
    'phone', 
    'addresses'
  ];

  // the fields' Tag Name definition
  const MERGE_FIELDS_TAG_MAPPING = [
    'last_name' => 'LNAME',
    'first_name' => 'FNAME',
    'accepts_marketing' => 'AMARKETING',
    'orders_count' => 'OCOUNT',
    'total_spent' => 'TSPENT',
    'last_order_id' => 'OID',
    'last_order_name' => 'ONAME',
    'note' => 'NOTE',
    'state' => 'STATE',
    'verified_email' => 'VEMAIL',
    'tags' => 'TAGS',
    'phone' => 'PHONE',
    'addresses' => 'ADDRESSES',
  ];

  // MailChimp API Object
  protected $mailChimp;

  // API Key
  protected $apiKey;

  // ListId of MailChimp
  protected $listId;

  /**
   * The Constructor.
   *
   * @return void
   */
  public function __construct()
  {
    try {
      $this->apiKey = env('MAILCHIMP_APIKEY');
      if (empty($this->apiKey)) {
        throw new \Exception('API Key is Missing');
      }
      $this->listId = env('MAILCHIMP_LIST_ID');
      if (empty($this->listId)) {
        throw new \Exception('API ListID is Missing');
      }
      $mailChimp = new MailChimp($this->apiKey);
      if (empty($mailChimp)) {
        throw new \Exception('Creating API Object Failed!');
      }
      $this->mailChimp = $mailChimp;
    } catch (\Exception $e) {
      abort('400', $e->getMessage());
    }
  }

  /**
   * Identify the fields' type.
   *
   * @param array $fields the merge_fields that need to be verified the type
   * @return void
   */
  private function verifyFields(array $fields)
  {
    if (empty($fields['name'])) {
      throw new \Exception('Name is missing');
    }
    $types = [
      'text', 'number', 'address', 'phone', 'date', 'url', 'imageurl', 'radio', 'dropdown', 'birthday', 'zip'
    ];
    if (empty($fields['type']) || !in_array($fields['type'], $types)) {
      throw new \Exception('Type '. $fields['type'].' is missing or invalid');
    }
  }

  /**
   * Identify if the merge_fields is existing.
   *
   * @param array $fields merge_fields that need to be identified if it is existing
   * @return array/bool
   */
  public function mergeFieldsExists(array $fields)
  {
    try {
      $this->verifyFields($fields);
      $result = $this->mailChimp->get(
        str_replace('{list_id}', $this->listId, self::CREATE_MERGE_FIELDS_PATH)
      );
      // identify if the fields is existing
      foreach ($result['merge_fields'] as $field) {
        if ($field['name'] == $fields['name'] || $field['tag'] == $fields['tag']) {
          return $field;
        }
      }
      return false;
    } catch (\Exception $e) {
      abort('400', $e->getMessage());
      return true;
    }
  }

  /**
   * Create the merge fields.
   *
   * @param array $fields merge_fields
   * @return array/bool
   */
  public function createMergeFields(array $fields)
  {
    try {
      $this->verifyFields($fields);
      if ($field = $this->mergeFieldsExists($fields)) {
        return $field;
      }
      $result = $this->mailChimp->post(
        str_replace('{list_id}', $this->listId, self::CREATE_MERGE_FIELDS_PATH),
        $fields
      );
      return $result;
    } catch (\Exception $e) {
      abort('400', $e->getMessage());
      return false;
    }
  }

  /**
   * Storing the necessary fields in merge_fields.
   *
   * @param array $data member's data
   * @return array
   */
  private function mappingFields(array $data)
  {
    $fields = [];
    $type = 'text';
    foreach (self::MERGE_FIELDS_MAPPING as $field) {
      if (is_null($data[$field])) {
        continue;
      }
      if (is_string($data[$field])) {
        $type = 'text';
      }
      if (is_numeric($data[$field])) {
        $type = 'number';
      }
      if (is_bool($data[$field])) {
        $type = 'number';
      }
      if ($field == 'phone') {
        $type = 'phone';
      }
      if ($field == 'addresses') {
        $type = 'text';
      }
      $mFields = [
        'name' => $field,
        'type' => $type,
        'tag' => self::MERGE_FIELDS_TAG_MAPPING[$field],
      ];
      if ($result = $this->createMergeFields($mFields)) {
        if ($field == 'addresses') {
          $data[$field] = json_encode($data[$field]);
        }
        if ($field == 'accepts_marketing' || $field == 'verified_email') {
          $data[$field] = intval($data[$field]);
        }
        if (!empty($result['tag']) && !empty($data[$field])) {
          $fields[$result['tag']] = $data[$field];
        }
      }
    }
    return $fields;
  }

  /**
   * Subscribing the new member or updating the existing member.
   *
   * @param array $data member's data
   * @return array/bool
   */
  public function subscribeOrUpdate(array $data)
  {
    $fields = [];
    try {
      if (empty($data)) {
        throw new \Exception('Data is missing');
      }
      $email = $data['email'];
      if (empty($email)) {
        throw new \Exception('Email is missing');
      }
      $mFields = $this->mappingFields($data);
      Log::info("Subscribing.... :". json_encode($mFields));
      $result = Newsletter::subscribeOrUpdate($data['email'], $mFields);
      $reason = "Success";
      if (!$result) {
        $reason = Newsletter::getLastError();
      }
      Log::info("Subscribe Result : ". $reason);
      return $result;
    } catch(\Exception $e) {
      Log::info("Subscribing.... : ". $e->getMessage());
      return false;
    }
  }

}
