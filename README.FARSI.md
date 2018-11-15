# پکیج لاراول برای درگاه بانک ملت
این پکیج جهت ااتصال به درگاه بانک ملت ساخته شده است.

---

### طریقه نصب
1.  دستور زیر در قسمت ترمینال وارد نمایید.
```bash
composer require tohidplus/mellat
```

2. کد زیر را در آرایه **providers** فایل **config/app.php** اضافه کنید.
```php
'providers' => [
    Tohidplus\Mellat\MellatServiceProvider::class,
];
```

3. کد زیر را در آرایه **aliases** فایل **config/app.php** اضافه کنید.
```php
'aliases' => [
   'Mellat' => Tohidplus\Mellat\Facades\Mellat::class,
];
```

4. دستور زیر را در قسمت ترمینال وارد نمایید.
```bash
php artisan vendor:publish --provider=Tohidplus\Mellat\MellatServiceProvider
```

5. دیتابیس را migrate کنید.
```bash
php artisan migrate
```

6. اکنون خواهید دید که فایل **mellat.php** به پوشه **config** پروژه اضافه شده است. فایل را باز نمایید...
```php
<?php
return [
    'terminalId' => 'شماره-ترمینال',
    'username' => 'نام-کاربری',
    'password' => 'رمز-عبور',
    'callBackUrl' => 'http://yourwebsite.com/verifyPayment',
    'convertToRial' => true
];
```
مقادیر مربوط به هر بخش را به درستی وارد نمایید.
> توجه: شما میتوانید مقدار **callBackUrl** را در این قسمت خالی گذاشته و قبل از ارجاع کاربر به درگاه بصورت داینامیک با استفاده از متد **setCallBackUrl** مشخص نمایید.
7. اکنون باید **callBackUrl** را از میان افزار **verifyCsrfToken** خارج نمایید تا پس از برگشت از درگاه با خطا مواجه نشوید. بنابراین فایل **app/Http/Middleware/VerifyCsrfToken.php** را باز نموده و **callBackUrl** را داخل ارایه **except** وارد نمایید.
```php
protected $except = [
    '/verifyPayment'
];
```

---

### فایل mellat.blade.php 
هنگام اتصال به درگاه صفحه ای را خواهید دید که میگوید (در حال اتصال به درگاه) که ساده و طراحی نشده است. شما می توانید این فایل را که داخل  پوشه  **resources/views/vendor/mellat** قرار گرفته است را باز نموده و بصورت دلخواه طراحی نمایید.

---

### متدها
**setCallBackUrl**

*در صورتی که شما مقدار **callBackUrl** را در فایل کانفیگ **mellat.php** خالی گذاشته اید یا اینکه وبسایت شما بیشتر از یک بخش پرداخت دارد میتوانید با استفاده از این متد بصورت داینامیک **callBackUrl** را مشخص نمایید.*

پارامتر ها : 
- **callBackUrl** (الزامی)

**set**

*قبل از ارجاع کاربر به درگاه باید اطلاعات مربوط به پرداخت  را با استفاده ازین متد مقداردهی نمایید در غیر این صورت با خطا مواجه خواهید شد.*

پارامترها : 

- **amount** (مبلغ قابل پرداخت - الزامی می باشد در صورتی که در فایل کانفیگ مقدار تبدیل به ریال صحیح باشد مبلغ را به تومان در غیر این صورت به ریال وارد نمایید.)
- **orderId** (شماره سفارش - اختیاری)
- **payerId** (شماره پرداخت کننده - اختیاری)
- **additionalData** (اطلاعات اضافی - اختیاری)

**redirect**

*پس مقداردهی اطلاعات پرداخت با استفاده از این متد می توانید کاربر را به سمت درگاه ارسال نمایید.*

پارامترها : 

- این متد فقط یک پارامتر بصورت callback function میگیرد و در صورتی که هنگام ارجاع کاربر به درگاه خطایی رخ دهد این تابع فراخوانی می شود که پیغام خطا را از طریق پارامتر دریافت میکند.
**verify**

*با استفاده ازین متد می توان نتیجه تراکنش را بررسی کرد که آیا موفق بوده یا خیر.*

پارامترها :

- **success** (این پارامتر یک کالبک فانکشن است و درصورتی که پرداخت  با موفقیت انجام شود فراخوانی می شود و یک آبجکت از نوع لاگ بصورت پارامتر دریافت می کند که داخل این آبجکت  تمامی اطلاعات مربوط به پرداخت موجود است.)
- **error** (این پارامتر یک کالبک فانکشن است و درصورتی که  خطایی هنگام پرداخت رخ دهد یا  با موفقیت انجام نشود  فراخوانی می شود و یک آبجکت از نوع لاگ بصورت پارامتر دریافت می کند که داخل این آبجکت  تمامی اطلاعات مربوط به پرداخت موجود است.)
---

### مثال کامل
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
            // خطا هنگام ارجاع کاربر به درگاه بانکی
            //dd($message);
        });
    }

    public function verifyPayment(Request $request)
    {
        return Mellat::verify(
        function ($log){
            // تراکنش موفق 
            //dd($log);   
        },function ($log){
            // تراکنش ناموفق
            //dd($log);
        });
    }
}
```

---

### گزارش تراکنش ها
همه اطلاعات مربوط به تراکنش ها در جدول **mellat_logs** ذخیره میشود که این جدول مربوط به مدل **Tohidplus\Mellat\Models\MellatLog** می باشد.
```php
<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Tohidplus\Mellat\Models\MellatLog;

class MellatLogController extends Controller
{
    public function index()
    {
        // تراکنش های موفق
        $successfulTransactions = MellatLog::successful()->get();
        // تراکنش های ناموفق
        $unsuccessfulTransactions = MellatLog::unsuccessful()->get();
        // تراکنش های معلق
        $pendingTransactions = MellatLog::pending()->get();
    }
}
```












