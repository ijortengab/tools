IjorTengab's Tools
==================

The Swiss Army Knife of IjorTengab. Collection of common useful script.

## Requirement
  - PHP > 5.4.0

## Repository

Tambahkan code berikut pada composer.json jika project anda membutuhkan library
ini. Perhatikan _trailing comma_ agar format json anda tidak rusak.

```json
{
    "require": {
        "ijortengab/tools": "master"
    },
    "minimum-stability": "dev",
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/ijortengab/tools"
        }
    ]
}
```

## List of Collections.

  - Abstract ```IjorTengab\Tools\Abstracts\AbstractAnalyzeCharacter```
  - Class static function ```IjorTengab\Tools\Functions\ArrayHelper::*```
  - Class static function  ```IjorTengab\Tools\Functions\CamelCase::*```
  - Class static function  ```IjorTengab\Tools\Functions\FileName::*```
  - Function ```IjorTengab\Override\PHP\VarDump\var_dump```
  - Function ```IjorTengab\Override\PHP\TmpFile\tmpfile```

### IjorTengab\Tools\Abstracts\AbstractAnalyzeCharacter

Abstract untuk memudahkan analisis karakter satu per satu pada sebuah string.


### IjorTengab\Tools\Functions\ArrayHelper

```php
ArrayHelper::propertyEditor();
ArrayHelper::dimensionalSimplify();
ArrayHelper::dimensionalExpand();
ArrayHelper::filterKeyInteger();
ArrayHelper::filterKeyPattern();
ArrayHelper::filterChild();
```

### IjorTengab\Tools\Functions\CamelCase

Menyediakan method static untuk convert string antara format underscore dan
camel case.

```php
CamelCase::convertFromUnderScore();
CamelCase::convertToUnderScore();
```

### IjorTengab\Tools\Functions\FileName

Menyediakan method static ```::uniquify``` untuk mengantisipasi filename
yang telah exists dengan cara menambahkan suffix integer autoincrement pada
filename.

Contoh:
 - File.jpg
 - File_0.jpg
 - File_1.jpg
 - File_2.jpg
