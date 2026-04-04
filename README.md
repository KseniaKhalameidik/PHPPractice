# *Используемые на каждом занятии команды*

---

## Занятие 5

php bin/console make:controller ProfileController (дальше enter)
php bin/console make:form ProfileType Profile 
php bin/console make:entity Post 
title-string-64-no
description-string-1024-no
Profile-relation-Profile-ManyToOne-yes-yes-posts
Потом в Post.php и Profile.php все Profile поменять на profile
php bin/console make:controller PostController (дальше enter)
php bin/console make:migration 
php bin/console doctrine:migrations:migrate (дальше enter)
php bin/console make:form PostType Post
