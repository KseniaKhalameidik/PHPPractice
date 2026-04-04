# *Используемые на каждом занятии команды*

---

## Занятие 5

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
php bin/console doctrine:migrations:migrate

## Создание формы для Post
php bin/console make:form PostType Post

php bin/console make:migration 
php bin/console doctrine:migrations:migrate (дальше enter)
php bin/console make:form PostType Post
