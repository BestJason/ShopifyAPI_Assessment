<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\MailChimpAPI;
use App\Models\ShopifyAPI;

class UpdateMembersToMailChimp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:UpdateMembersToMailChimp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command will update members\' profile of MailChimp';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      // count the number of success and failure
      $success = 0;
      $failure = 0;
      // get Members from Shopify
      $shopify = new ShopifyAPI();
      $customers = $shopify->getCustomers();
      // loop members and sync to MailChimp and update the latest profile into MailChimp
      if (!empty($customers)) {
        array_map(function($customer) use(&$success, &$failure){
          $mailChimp = new MailChimpAPI();
          if (!empty($customer) && is_array($customer)) {
            Log::info('Sync '. $customer['email'].' start ... : ');
            if($mailChimp->subscribeOrUpdate($customer)) { 
              Log::info('Sync '. $customer['email']. ' Successfully!');
              $success++;
            } else {
              Log::info('Sync '. $customer['email']. ' Failed!');
              $failure++;
            }
          }
        }, $customers);
      }
      Log::info('Completed! Success: '. $success. ' ; Failure: '. $failure. ' ;');
    }
}
