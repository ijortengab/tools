# Action Wrapper

Package Action Wrapper terdiri dari:

  - Class ```IjorTengab\ActionWrapper\Action```
  - Class ```IjorTengab\ActionWrapper\Log```
  - Class ```IjorTengab\ActionWrapper\Modules```
  - Interface ```IjorTengab\ActionWrapper\ModuleInterface```

Requirement:
  - PHP > 5.4.0
  - Class ```IjorTengab\Logger\Log```

ActionWrapper berguna untuk membungkus semua class dalam menjalankan
satu/berbagai action untuk disederhanakan menjadi satu baris perintah
pemanggilan. Class tersebut disebut sebagai module.

```php
$result = Action::ClassA('doSomething');

$result = Action::ClassA('doSomething', $contextual_information);

$result = Action::ClassA('doAnotherThing');

$result = Action::ClassA('doAnotherThing', $contextual_information);
```

Class agar dapat dikenali sebagai module maka perlu meng-implements
ModuleInterface. Interface mendefinisikan 5 method yang standard untuk
menjalankan flow action.

```php
/**
 * @file ClassA.php
 */
namespace Vendor\Package;

use IjorTengab\ActionWrapper\ModuleInterface;

ClassA implements ModuleInterface
{
    public function setAction($action) {}
    public function setInformation($key, $value) {}
    public function runAction() {}
    public function getResult() {}
    public function getLog($level = null) {}
}
```

Setelah meng-implements ModuleInterface, maka class tersebut perlu mendaftarkan
diri sebagai module. Terdapat berbagai cara untuk mendaftarkan diri.

1. Menggunakan ```Modules::add()```
2. Membuat file dot module

Register class menggunakan ```Modules::add()``` dengan memberi argument bertipe
array sederhana satu dimensi, dimana ```key``` merupakan nama module dan
```value``` merupakan nama class lengkap dengan namespace.

```php
use IjorTengab\ActionWrapper\Modules;
use IjorTengab\ActionWrapper\Action;
use IjorTengab\ActionWrapper\Log;

include('ClassA.php');

Modules::add([
    'ClassA' => 'Vendor\Package\ClassA',
    'AliasName' => 'Vendor\Package\ClassA',
]);

Action::ClassA('print', ["value" => "Let's Get Merried"]);
Action::AliasName('print', ["value" => "Love"]);

print_r(Log::get());
```

File dot module akan menjadi feeder sebagai pengganti ```Modules::add()```.
Ketentuan file dot module adalah sebagai berikut:
 - nama file mewakili nama Class sekaligus nama module.
 - file merupakan bahasa PHP yang mereturn namespace dari Class tersebut.
 - file harus berada didalam direktori ```modules``` dengan maksimal kedalaman 5
   (lima) sub direktori.

```php
/**
 * @file ClassA.module
 */
namespace Vendor\Package;
return __NAMESPACE__;
```

Jika Class tidak mendukung autoload melalui ```Composer```, maka file Class
dapat menggunakan autoload ```Modules::autoload()``` dengan cara menempatkan
file Class satu direktori dengan file dot module.

```
ActionWrapper
│   Action.php
│   Log.php
│   ModuleInterface.php
│   Modules.php
│   README.md
└───modules
    │   ClassA.php
    │   ClassA.module
    ├───ClassB
    │   │   ClassB.php
    │   │   ClassB.module
    └───ClassC
    │   |   ClassC.module
```

Pada struktur direktori diatas ClassA.php dan ClassB.php akan autoload melalui
```Modules::autoload()``` sementara ClassC.php berada pada direktori lain dan
akan autoload menggunakan metode lain (misal Composer).

## IjorTengab\ActionWrapper\Action

Class utama untuk melakukan eksekusi dan me-return hasil. Class ```Action``` memanggil method static yang diasumsikan sebagai nama module. Argument yang diberikan minimal terdiri dari satu argument yakni nama aksi. Argument kedua bersifat optional dan jika ada maka dianggap sebagai array sederhana satu dimensi.
```php
Action::{module_name}({action_name}, {context});
```

## IjorTengab\ActionWrapper\Modules

Class yang meng-handle administrasi class-class lain yang akan menjadi module. Administrasi tersebut meliputi pendaftaran, listing, scanning, dan autoload.

## IjorTengab\ActionWrapper\Log

Class untuk keperluan log dan meng-implements PSR-3 (psr/log/LoggerInterface).
```php
$all = Log::get();
$error = Log::getError();
```
