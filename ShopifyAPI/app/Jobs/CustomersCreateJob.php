<?php namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use App\Models\MailChimpAPI;

class CustomersCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Shop's myshopify domain
     *
     * @var string
     */
    public $shopDomain;

    /**
     * The webhook data
     *
     * @var object
     */
    public $data;

    /**
     * Create a new job instance.
     *
     * @param string $shopDomain The shop's myshopify domain
     * @param object $webhook The webhook data (JSON decoded)
     *
     * @return void
     */
    public function __construct($shopDomain, $data)
    {
        $this->shopDomain = $shopDomain;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      // Monitor the notification infomation
      Log::info('Notification start and prepare for syncing the MailChimp.'. $this->shopDomain. ':' .json_encode($this->data));

      try {
        // update members of mailchimp
        $mailChimpApi = new MailChimpAPI();
        $result = $mailChimpApi->subscribeOrUpdate(json_decode(json_encode($this->data), true));
        if (!$result) {
          throw new \Exception('Subscribing Failed!');
        }
      } catch(\Exception $e) {
        Log::info('Notification suffered something wrong.... : '. $e->getMessage());
        // ToDo write this task into Redis or MQ
        //
      }
      Log::info('Notification done!');

    }
}
