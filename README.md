persistent-block-storage
==============

Описание
--------
Демоны, написанный на react-PHP, реализующие распределенное только дополняемое хранилище.

Установка
---------
1. Запустите ```composer create-project avallac/persistent-block-storage```
2. Отредактируйте persistent-block-storage/etc/config.yml:
```
coreUrl: http://127.0.0.1:8888.                           # URL координирующего процесса CORE
blockSize: 99999999999                                    # Размер блока
auth:
  username: <пользователь>|null
  password: <пароль>|null
core:                                                     # Блок необходим для работы координирующего процесса
  database:                                               # Необходимо для headerStorage = sql
    username: postgres
    password:
    dsn: pgsql:host=localhost;dbname=storage
  bind: 0.0.0.0:8888                                      # IP интерфейса:Номер слушающего порта , 0.0.0.0 для всех
  headerStorage: sql|memory                               # Тип хранения информации, memory - для тестов
  servers:                                                # Массив серверов
    0:                                                    # ID - сервера
      adminUrl: http://127.0.0.1:10700                    # URL админки данного сервера
      deliveryUrl: http://127.0.0.1:10701                 # URL раздающей части данного сервера
      volumes: 0
server:                                                   # Блок необходим для работы хранящего процесса
  bindAdmin: 0.0.0.0:10700
  bind: 0.0.0.0:10701
  volumes:                                                # Описание блоков
    0:
      path: /tmp/block
      hash:
```
