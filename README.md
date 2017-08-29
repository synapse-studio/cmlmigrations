# cmlmigrations

Миграции находятся в группе cml - её нужно создать перед просмором
 * /admin/structure/migrate/manage/cml/migrations
 * drush dre cmlmigrations -y (команда при изменении конфига)

## Посмтреть статус в консоли
drush ms

## Импортировать каталог
 * drush mi cmlmigrations_taxonomy_catalog
 * drush mi cmlmigrations_taxonomy_catalog --update (для обновления)

## Снести каталог
drush mr cmlmigrations_taxonomy_catalog

## Сбросить статус, если что-то сломалось
drush mrs cmlmigrations_taxonomy_catalog
