# Mission

Package Mission terdiri dari:

- Abstract ```IjorTengab\Mission\AbstractMission```
- Abstract ```IjorTengab\Mission\AbstractWebCrawler```
- Class Exception ```IjorTengab\Mission\Exception\ExecuteException```
- Class Exception ```IjorTengab\Mission\Exception\StepException```  
- Class Exception ```IjorTengab\Mission\Exception\VisitException```
- Trait ```IjorTengab\Mission\MissionTrait```

Mission berguna untuk melakukan eksekusi bertahap (*step by step*) dengan fokus 
tujuan sesuai target yang dituju.

Web Crawler adalah extends dari Mission yang fokus tujuannya adalah crawling
halaman web.

## Requirement
- php 5.4.0
- ijortengab/tools
