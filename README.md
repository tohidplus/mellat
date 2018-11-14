
[![GitHub issues](https://img.shields.io/github/issues/tohidplus/mellat.svg)](https://github.com/tohidplus/mellat/issues)
[![GitHub stars](https://img.shields.io/github/stars/tohidplus/mellat.svg)](https://github.com/tohidplus/mellat/stargazers)
[![Total Downloads](https://img.shields.io/packagist/dt/tohidplus/mellat.svg)](https://packagist.org/packages/tohidplus/mellat)
[![GitHub license](https://img.shields.io/github/license/tohidplus/mellat.svg)](https://github.com/tohidplus/mellat/blob/master/LICENSE.txt)


# Laravel package for Mellat bank
This package is built for connecting Iranian websites to Mellat bank gateway.

---

### Installation
1. Run the command below
```bash
composer require tohidplus/mellat
```

1. Add the following code to end of the **providers** array in **config/app.php** file.
```php
'providers'=>[
    Tohidplus\Mellat\MellatServiceProvider::class,
];
```

1. Add the following code to end of the **aliases** array in **config/app.php** file.
```php
'aliases' => [
   'Mellat'=>Tohidplus\Mellat\Facades\Mellat::class,
];
```

1. Run the command below
```bash
php artisan vendor:publish --provider=Tohidplus\Mellat\MellatServiceProvider
```

1. Migrate the database
```bash
php artisan migrate
```

1. Now you can see a new config file named **mellat.php** is added to config directory. So open the file...
```php
<?php
return [
'terminalId' => 'your-temrinalId',
'username' => 'your-username',
'password' => 'your-password',
'callBackUrl' => 'http://yourwebsite.com/verifyPayment',
'convertToRial' => true
];

```
Fill the array elements

> Notice: you can leave **callBackUrl** here blanked and initialize it dynamically by using **setCallBackUrl** method as we will explain

Now you have to exclude **callBackUrl** from **verifyCsrfToken** middleware.
Open the file **app/Http/Middleware/VerifyCsrfToken.php** and add the **callbackUrl** to **except** array.
```php
protected $except = [
    '/verifyPayment'
];
```

---

### **mellat.blade.php** file
While redirecting user to the bank you will see a simple page with no design which is located inside the **resources/views/vendor/mellat** directory. So you can open this file and design it as you wish.

---

### Methods
**setCallBackUrl**

*if you haven't  set **callBackUrl** in **mellat.php** config file you must set it using this method before redirecting user .* 

Parameters:
- **callBackUrl** (required)

**set**

*Before redirecting user to the gateway you have to initialize the fields using this method otherwise you will get an exception.*

Parameters:
- **amount** (Required - *if the value of **convertToRial** in **mellat.php** file is **true** you must pass the value in **Toman** otherwise pass it in **Rial**.*)
- **orderId** (Optional)
- **payerId** (Optional)
- **additionalData** (Optional)

**redirect**

*after initializing the fields you can redirect user to the bank by using this method.*

Parameters:

- It only accepts one parameter as a callback funtion and if there is an error while redirection the callback function will be triggered with error message as a parameter.

**verify** 

*This method indicates if transaction was successful or not.*


Parameters:
- **success**  is a callback function which will be triggered if the transaction was successful and accepts an instance of **Tohidplus\Mellat\Models\MellatLog**  as a parameter.
- **error** is a callback function which will be triggered if the transaction was unsuccessful and accepts an instance of **Tohidplus\Mellat\Models\MellatLog** as a parameter.

---

### Full example
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tohidplus\Mellat\Facades\Mellat;

class PaymentController extends Controller
{
    public function redirectUserToBank()
    {

        //Mellat::setCallBackUrl(url('/verifyPayment'));

        Mellat::set(100);

        
        return Mellat::redirect(function($message){
            // Do something if there was a problem while redirection
            //dd($message);
        });
    }

    public function verifyPayment(Request $request)
    {
        return Mellat::verify(
        function ($log){
            // The transaction is successfull 
            //dd($log);   
        },function ($log){
            // The trasnsaction is unsuccessful
            //dd($log);
        });
    }
}

```

---

### Transaction Logs
Simply all events are saved in **mellat_logs** table which is associated with **Tohidplus\Mellat\Models\MellatLog** model
.
```php
<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Tohidplus\Mellat\Models\MellatLog;

class MellatLogController extends Controller
{
    public function index()
    {
        $successfulTransactions = MellatLog::successful()->get();
        $unsuccessfulTransactions = MellatLog::unsuccessful()->get();
        $pendingTransactions = MellatLog::pending()->get();
    }
}

```
