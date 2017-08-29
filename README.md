# cmlmigrations

Миграции находятся в группе cml - её нужно создать перед просмором
http://887-m-hao.n3.s3dev.ru/admin/structure/migrate/manage/cml/migrations

## Посмтреть статус в консоли
drush ms

## Импортировать каталог
drush mi cmlmigrations_taxonomy_catalog

## Снести каталог
drush mr cmlmigrations_taxonomy_catalog

## Сбросить статус, если что-то сломалось
drush mrs cmlmigrations_taxonomy_catalog
