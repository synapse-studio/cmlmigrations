# 1. Установка:
## 1.1 Добавляем 2 модуля, они в зависимостях:
 * `composer require drupal/migrate_tools`
 * `composer require drupal/migrate_plus`

## 1.2 Качаем модуль и включаем
 * `cd /var/www/html/modules/custom`
 * `git clone https://github.com/synapse-studio/cmlmigrations`
 * `drush en cmlmigrations`

## 1.3 Проверяем работоспособрость
 * `drush ms` - статус текущих миграций в консоли.
 * в веб интерфейсе `/admin/structure/migrate` , можно сразу `/admin/structure/migrate/manage/cml/migrations`

# 2. Работаем:

## 2.1 Доступные миграции
 * `drush mi cmlmigrations_taxonomy_catalog` - Каталог
 * `drush mi cmlmigrations_commerce_product_variation` - Вариации
 * `drush mi cmlmigrations_node_tovar` - Товары (node_tovar)

## 2.2 Работа с миграцией на примере каталога
 * `drush mi cmlmigrations_taxonomy_catalog` - импорт
 * `drush mi cmlmigrations_taxonomy_catalog --update` обновить, если что-то поменялось в выгрузке
 * `drush mr cmlmigrations_taxonomy_catalog` - снести если что-то пошло не так
 * `drush mrs cmlmigrations_taxonomy_catalog` - сбросить стаутс если что-то сломалось

## 2.3 Изменение миграций, на примере товара
```
У тебя посути 3 плагина источника, они тут:
/var/www/html/modules/custom/cmlmigrations/src/Plugin/migrate/source
(один из файлов лишний - CmlProductMigrationPlugin.php)
Назначение данных (в какое поле положить) настраивается в конфиг-файлах. После правки конфиг файла модуль нужно переустановить.
```
 * миграция называется `drush mi cmlmigrations_node_tovar`
 * Смотрим конфиг `/config/install/migrate_plus.migration.node_tovar.yml`
   * Источник данных берётся из source:plugin:cml_tovar
   * Плагин источника тут: `/src/Plugin/migrate/sourceCmlTovarMigrationPlugin.php`
   * Добавляем какое поле заполнить это поле, напр process:title:title
 * Переустанавливаем модуль `drush dre cmlmigrations -y`

### Мульти поле field_tovar_variation:
Сейчас реализауется отедельным плагином:
```
  field_tovar_variation:
    -
      plugin: skip_on_empty
      method: process
      source: variations
    -
      plugin: multival
      source: variations
      target_id: target_id
```
## 2.4 Мега-комбо
Обновить всё: `drush mi --group=cml --update`.
```
drush mi cmlmigrations_taxonomy_catalog --update && drush mi cmlmigrations_commerce_product_variation --update && drush mi cmlmigrations_node_tovar --update
```


# Хак для импорта вариаций
/modules/contrib/commerce/modules/product/src/Entity/ProductVariation.php

* 327 строчка
```
  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    /** @var \Drupal\commerce_product\Entity\ProductVariationTypeInterface $variation_type */
    $variation_type = $this->entityTypeManager()
      ->getStorage('commerce_product_variation_type')
      ->load($this->bundle());

    $title_import = $this->title->value;
    if ($variation_type->shouldGenerateTitle()) {
      $title = $this->generateTitle();
      $this->setTitle($title);
    }
+   if (!$title) {
+     $this->setTitle($title_import);
+   }
  }
```

* 350 строчка
```
  protected function generateTitle() {
    if (!$this->getProductId()) {
      // Title generation is not possible before the parent product is known.
      return '';
    }

+   if (!is_object($this->getProduct())) {
+     return '';
+   }

    $product_title = $this->getProduct()->getTitle();
```
 
