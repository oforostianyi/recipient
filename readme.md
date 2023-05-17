## ResipientService
### Allows you to check the mobile number for validity, as well as determine its extended attributes
Usage
```php
<?php
// Load composer
require __DIR__ . '/vendor/autoload.php';

$databaseConnection = new Oforostianyi\Recipient\DatabaseConnection('localhost', 'username', 'password', 'database');
$recipientService = new Oforostianyi\Recipient\RecipientService($databaseConnection);

$recipientInfo = $recipientService->check('380631234567');
$recipientInfo = $recipientService->check('38 (063) 123-45-67');
```

example_data.sql contains the schema and test data for the database.