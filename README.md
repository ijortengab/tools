IjorTengab's Tools
==================
Collections of Class that don't need a standalone repository.
The Swiss Army Knife of IjorTengab.

Requirement:
  - PHP > 5.4.0

List of Contains.

  - Class ```IjorTengab\Tools\Functions\ArrayDimensional```
  - Class ```IjorTengab\Tools\Functions\CamelCase```
  - Class ```IjorTengab\Tools\Functions\FileName```
  - Trait ```IjorTengab\Tools\Trait\PropertyArrayManagerTrait```

### IjorTengab\Tools\Functions\ArrayDimensional

Menyediakan method static untuk mengubah array multidimensi menjadi satu dimensi
dan sebaliknya.

```php
ArrayDimensional::simplify();
ArrayDimensional::expand(); // not yet implemented.
```

### IjorTengab\Tools\Functions\CamelCase

Menyediakan method static untuk convert string antara format underscore dan 
camel case.

```php
CamelCase::convertFromUnderScore();
CamelCase::convertToUnderScore();
```

### IjorTengab\Tools\Functions\FileName

Menyediakan method static ```::createUnique``` untuk mengantisipasi filename 
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
