# *Используемые на каждом занятии команды*

---

# Команды, используемые на занятии 5

## Создание контроллера профиля
php bin/console make:controller ProfileController (дальше enter)

## Создание формы профиля
php bin/console make:form ProfileType Profile

## Создание сущности Post
php bin/console make:entity Post

## Параметры для Post (вводить по запросам):
title - string - 64 - no
description - string - 1024 - no
Profile - relation - Profile - ManyToOne - yes - yes - posts

## Замена всех Profile на profile в файлах Post.php и Profile.php
(выполнить вручную в редакторе кода)

## Создание контроллера постов
php bin/console make:controller PostController (дальше enter)

## Создание миграции
php bin/console make:migration

## Выполнение миграции
php bin/console doctrine:migrations:migrate (дальше enter)

## Создание формы для Post
php bin/console make:form PostType Post



## Занятие 6

PS C:\dev\PHP\symfony-project> php bin/console make:entity Comment

 New property name (press <return> to stop adding fields):
 > author

 Field type (enter ? to see all types) [string]:
 > ?

 Field type (enter ? to see all types) [string]:
 > relation

 What class should this entity be related to?:
 > Profile

 Relation type? [ManyToOne, OneToMany, ManyToMany, OneToOne]:
 > ManyToOne

 Is the Comment.author property allowed to be null (nullable)? (yes/no) [yes]:
 > no

 Do you want to add a new property to Profile so that you can access/update Comment objects from it - e.g. $profile->getComments()? (yes/no) [yes]:
 > no

 Add another property? Enter the property name (or press <return> to stop adding fields):
 > createdAt

 Field type (enter ? to see all types) [datetime_immutable]:
 > ?

 Field type (enter ? to see all types) [datetime_immutable]:
 > datetime_immutable

 Can this field be null in the database (nullable) (yes/no) [no]:
 > no

 Add another property? Enter the property name (or press <return> to stop adding fields):
 > content

 Field type (enter ? to see all types) [string]:
 > string

 Field length [255]:
 > 1024

 Can this field be null in the database (nullable) (yes/no) [no]:
 > no

 Add another property? Enter the property name (or press <return> to stop adding fields):
 > post

 Field type (enter ? to see all types) [string]:
 > relation

 What class should this entity be related to?:
 > Post

 Relation type? [ManyToOne, OneToMany, ManyToMany, OneToOne]:
 > ManyToOne

 Is the Comment.post property allowed to be null (nullable)? (yes/no) [yes]:
 > no

 Do you want to add a new property to Post so that you can access/update Comment objects from it - e.g. $post->getComments()? (yes/no) [yes]:
 > yes

 New field name inside Post [comments]:
 > comments

 Do you want to automatically delete orphaned App\Entity\Comment objects (orphanRemoval)? (yes/no) [no]:
 > yes

 Add another property? Enter the property name (or press <return> to stop adding fields):
 > 
 
PS C:\dev\PHP\symfony-project> php bin/console make:migration

PS C:\dev\PHP\symfony-project> php bin/console doctrine:migrations:migrate

 WARNING! You are about to execute a migration in database "app" that could result in schema changes and data loss. Are you sure you wish to continue? (yes/no) [yes]:
 > yes                                                                

PS C:\dev\PHP\symfony-project> php bin/console make:crud Comment

 Choose a name for your controller class (e.g. CommentController) [CommentController]:
 > CommentController

 Do you want to generate PHPUnit tests? [Experimental] (yes/no) [no]:
 > no

PS C:\dev\PHP\symfony-project> php bin/console make:controller StatisticsController

 Do you want to generate PHPUnit tests? [Experimental] (yes/no) [no]:
 > no
