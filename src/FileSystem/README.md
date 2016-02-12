# FileSystem

Package FileSystem terdiri dari:

  - Class ```IjorTengab\FileSystem\FileName```

Requirement:
  - PHP > 5.4.0


## IjorTengab\FileSystem\FileName

Menyediakan method createUnique untuk mengantisipasi filename yang telah exists
dengan cara menambahkan suffix integer autoincrement pada filename.

Contoh:
 - File.jpg
 - File_0.jpg
 - File_1.jpg
 - File_2.jpg
