## IjorTengab\Timer\Timer

Class Timer berguna untuk menghitung waktu. Waktu yang di-return oleh method 
```::read()``` maupun ```::remaining()```
adalah dalam satuan milisecond.

Contoh count up:
```php
$time = new Timer;
sleep(1);
echo $time->read();
```

Contoh count down:
```php
$timeout = 5;
$time = new Timer($timeout);

while($time->remaining()) {
    // Execute anything untill time remaining finished.
}
```

Code terinspirasi daru Drupal 7.
