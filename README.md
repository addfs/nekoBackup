# nekoBackup 1.1

nekoBackup — утилита для создания регулярных бекапов данных на вашем сервере. На текущий момент поддерживаются следующие виды бекапов:

* простой архив директории,
* архивы поддиректорий в директории (до 2 уровней вложенности),
* базы данных mysql (с помощью mysqldump),
* базы данных postgres (с помощью pg_dump).

Бекапы можно сохранить:

* в файловой системе,
* в хранилище Amazon S3.

## Требования

* php-cli 5.2+
* python (для [s3cmd](https://github.com/s3tools/s3cmd))

## Установка

Поскольку скрипту нужен полный доступ ко всем директориям, указанным в конфиге, желательно запускать его от имени
суперпользователя.

## Настройка

Настройка осуществляется с помощью yaml-файлов в директории config.
Перед редактированием настроек советую прочитать о синтаксисе [языка разметки yaml](http://ru.wikipedia.org/wiki/YAML).

### Базовая настройка `config.yaml`

Скопируйте [config.yaml.example](https://github.com/druidvav/nekoBackup/blob/master/config/config.yaml.example) в
config.yaml и отредактируйте его в соответствии с комментариями.

### Amazon S3 `s3.yaml`

Скопируйте [s3.yaml.example](https://github.com/druidvav/nekoBackup/blob/master/config/s3.yaml.example) в
s3.yaml и отредактируйте его в соответствии с комментариями. Крайне важно, чтобы на файл s3.yaml стояли права
0600, а владельцем был пользователь, которым вы запускете скрипт.

Для создания бекапов используется утилита [s3cmd](https://github.com/s3tools/s3cmd), она идёт в комплекте с nekoBackup,
файл конфигурации для неё будет сгенерирован автоматически.

## Использование

`php nbackup.php`

* запускает архивирование данных в директорию storage в соответствии с текущей датой.
* `--driver=s3` запускает архивирование данных на Amazon S3, при этом директория storage используется для временных файлов.
* `--initial` запускает полный бекап всех данных, а не только назначенных на текущую дату расписанием.
* `--install` автоматически добавляет задачу архивирования с указанными параметрами в cron.