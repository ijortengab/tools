IjorTengab's Tools
==================

Collections of Library that doesn't need a standalone repository. The Swiss Army
Knife of IjorTengab.

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

  - Class ```IjorTengab\Tools\Functions\ArrayDimensional```
  - Class ```IjorTengab\Tools\Functions\CamelCase```
  - Class ```IjorTengab\Tools\Functions\FileName```
  - Trait ```IjorTengab\Tools\Traits\PropertyArrayManagerTrait```

### IjorTengab\Tools\Functions\ArrayDimensional

Menyediakan method static untuk mengubah array multidimensi menjadi satu dimensi
dan sebaliknya.

```php
ArrayDimensional::simplify();
ArrayDimensional::expand();
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

### IjorTengab\Tools\Traits\PropertyArrayManagerTrait

Memberikan method ```propertyArrayManager``` untuk menyederhanakan operasi CRUD
terhadap property (variable dalam class) bertipe array.
