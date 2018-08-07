# The accessment on dotdev about development of shopify app

## Reqirment-1: Sync any new Shopify customers to Mailchimp list: Shopify Testing
## Reqirment-2: Update any existing Shopify customers details to Mailchimp daily

### Controllers
> ShopifyAPI_Assessment/ShopifyAPI/app/Http/Controllers/ShopifyController.php is for creating webhook of customers creatation

### Models
> ShopifyAPI_Assessment/ShopifyAPI/app/Models/MailChimpAPI.php is the API Model of MailChimp
> ShopifyAPI_Assessment/ShopifyAPI/app/Models/ShopifyAPI.php is the API Model of Shopify App

### Jobs
> ShopifyAPI_Assessment/ShopifyAPI/app/Jobs/CustomersCreateJob.php is for handling webhook request after creating customers on shopify app

### Commands
> ShopifyAPI_Assessment/ShopifyAPI/app/Console/Commands/UpdateMembersToMailChimp.php is for daily updating MailChimp Members' detailed information

### Cron Job File
> ShopifyAPI_Assessment/ShopifyAPI/cronJob is the cron job for daily running UpdateMembersToMailChimp command

We should run the following command to set up a cron job
```
  $ crontab -e
    write the following code
  
    # update members' information of MailChimp on 02:00:00 of every day, the result of running will record into log file of laravel
    00 02 * * * /usr/bin/php /Users/JasonLee/Repository/ShopifyAPI_Assessment/ShopifyAPI/artisan command:UpdateMembersToMailChimp > /dev/null
```

#### _Note_: Due To the Sensitive Data, __.env__ file is not uploaded
