# ObjectHelper

Package ObjectHelper terdiri dari:

  - Class ```IjorTengab\ObjectHelper\CamelCase```
  - Class ```IjorTengab\ObjectHelper\FlatyArray```
  - Trait ```IjorTengab\ObjectHelper\PropertyArrayManagerTrait```

Requirement:
  - PHP > 5.4.0

## IjorTengab\ObjectHelper\CamelCase

Menyediakan method static untuk convert dari dan ke underscore.

```php
CamelCase::convertFromUnderScore();
CamelCase::convertToUnderScore();
```

## IjorTengab\ObjectHelper\ArrayDimensional

Mengubah array multidimensi menjadi satu dimensi dan sebaliknya.

```php
ArrayDimensional::simplify();
ArrayDimensional::expand(); // not yet implemented.
```

## IjorTengab\ObjectHelper\PropertyArrayManagerTrait

Menyederhanakan operasi CRUD terhadap property (variable dalam class) bertipe
array.
