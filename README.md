# The accessment on dotdev about development of shopify app

### Controllers
> ShopifyAPI/app/Http/Controllers/ShopifyController.php 

`For creating webhook of customers creatation`

### Models
> ShopifyAPI/app/Models/MailChimpAPI.php 

`The API Model of MailChimp`

> ShopifyAPI/app/Models/ShopifyAPI.php 

`The API Model of Shopify App`

### Jobs
> ShopifyAPI/app/Jobs/CustomersCreateJob.php 

`For handling webhook request after creating customers on shopify app`

### Commands
> ShopifyAPI/app/Console/Commands/UpdateMembersToMailChimp.php 

`For daily updating MailChimp Members' detailed information`

### Cron Job File
> ShopifyAPI/cronJob is the cron job sample

`For daily running UpdateMembersToMailChimp command`

We should run the following command to set up a cron job
```
  $ crontab -e
  
    # update members' information of MailChimp on 02:00:00 of every day, the result of running will record into log file of laravel
    00 02 * * * /usr/bin/php /Users/JasonLee/Repository/ShopifyAPI_Assessment/ShopifyAPI/artisan command:UpdateMembersToMailChimp > /dev/null
```

#### _Note_: Due To the Sensitive Data, __.env__ file is not uploaded
