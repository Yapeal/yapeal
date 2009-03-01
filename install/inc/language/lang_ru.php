<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal installer - Language file English.
 *
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know as Yapeal.
 *  Yapeal is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Yapeal is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with Yapeal. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Claus Pedersen <satissis@gmail.com>
 * @author Michael Cummings <mgcummings@yahoo.com>
 * @copyright Copyright (c) 2008-2009, Claus Pedersen, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 */

/**
 * @internal Only let this code be included or required not ran directly.
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
}
/**
 * Default text
 */
define("CHOSELANGUAGE","Выбор языка");
define("NEXT","Следующий");
define("FAILED","Ошибка");
define("OK","Хорошо");
define("MISSING","скучая");
define("LOADED","Загружено");
define("YES","Да");
define("NO","нет");
define("SETUP","Настройка");
define("DATABASE","База данных");
define("HOST","Хозяин");
define("USERNAME","Имя пользователя");
define("PASSWORD","Password");
define("PREFIX","Пароль");
define("CONFIG","Конфигурация");
define("ERROR","Ошибка");
define("DONE","Готово");
define("CLOSED","Закрытые");
define("SELECTED","Отобранные");
define("CONNECTED","Подключен");
define("LOGIN","Войти");
define("UPDATE","Обновление");
define("ON","На");
define("OFF","Вне");
define("BACK","Back");
/**
 * YAPEAL INSTALLER TEXT'S
 */
define("NOIGB_HEADLINE","No IGB Support");
define("NOIGB_TEXT",'This setup can only be run in a normal browser and not the IGB.<br />' . PHP_EOL
                               .'Press the link and you will be popped out of EVE and this setup will re-openned in a normal browser.<br />' . PHP_EOL);
define("NOIGB_YAPEAL_SETUP","Yapeal Setup");
/**
 * YAPEAL INSTALLER TEXT'S
 */
define("INSTALLER_WELCOME","Добро пожаловать");
define("INSTALLER_WELCOME_TEXT",'<h3>Welcome to Yapeal Setup.</h3><br />' . PHP_EOL
                               .'This setup will make Yapeal EVE API Library run on your site.<br />' . PHP_EOL
                               .'<br />' . PHP_EOL);
define("INSTALLER_PHP_VERSION","PHP версии");
define("INSTALLER_PHP_EXT","PHP продлении");
define("INSTALLER_REQ_PHP_EXT",'Требуется расширение PHP ');
define("INSTALLER_IS_MISS"," не хватает!");
define("INSTALLER_HOST_NOT_SUPORTED",'Этот веб-хостинга не поддерживает Yapeal.<br />' . PHP_EOL
                                    .'Решение: Аренда веб-узел, который соответствует требованиям<br />' . PHP_EOL
                                    .'или, если это ваш собственный, а затем обновить / установить требования.');
define("INSTALLER_CHMOD_CHECK_FAIL",'Некоторые файлы или папки, не для записи.<br />' . PHP_EOL
                                   .'Chmod файла или папки, правильно!');
define("INSTALLER_FILE","Файл");
define("INSTALLER_FILE_TO","файл");
define("INSTALLER_DIR","режe");
define("INSTALLER_DIR_TO","реже на");
define("INSTALLER_REQ_CHECK","Требование проверки");
define("INSTALLER_REQUIRE","Требовать");
define("INSTALLER_RESULT","Результат");
define("INSTALLER_STATUS","Статус");
define("INSTALLER_WRITEABLE","Записи");
define("INSTALLER_CHK_FILE_DIR_WRITE_PREM","Проверка файлов и папок записи.");
define("INSTALLER_SETUP_HOW_YAPEAL","Настройка Yapeal сейчас должны вести себя");
define("INSTALLER_SAVE_XML_FILES","Сохранить XML файлов");
define("INSTALLER_SAVE_XML_DES",'      включается кэширование данных XML API к локальным файлам.<br />' . PHP_EOL
                               .'      "Нет" = Сохранить веб-пространства, но добавляет в базу данных.' . PHP_EOL);
define("INSTALLER_GET_ACCOUNT_INFO","Получить учетной записи");
define("INSTALLER_GET_ACCOUNT_DES","Save characters from API Account info to database");
define("INSTALLER_GET_CHAR_INFO","Получить Характер информации");
define("INSTALLER_GET_CHAR_DES","Сохранить Характер информации в базу данных");
define("INSTALLER_GET_CORP_INFO","Получить Corp Инфо");
define("INSTALLER_GET_CORP_DES","Сохранить Corp информации в базу данных");
define("INSTALLER_GET_EVE_INFO","Получить Ева Инфо");
define("INSTALLER_GET_EVE_DES","Сохранить Ева информация в базу данных");
define("INSTALLER_GET_MAP_INFO","Получить карты Инфо");
define("INSTALLER_GET_MAP_DES","Сохранить Карта информация в базу данных");
define("INSTALLER_API_SETUP","API установки");
define("INSTALLER_GET_API_INFO_HERE","Вы можете получить API информация здесь");
define("INSTALLER_EVE_API_CENTER","EVE API центр");
define("INSTALLER_API_USERID","API ID пользователя");
define("INSTALLER_API_LIMIT_KEY","Ограниченная ключ API");
define("INSTALLER_API_FULL_KEY","Полный API ключа");
define("INSTALLER_SETUP_PASS","Установка пароля");
define("INSTALLER_SETUP_PASS_DES","Это пароль, которые вы можете использовать, если нужно внести изменения в настройки, когда вы завершили эту настройку.");
define("INSTALLER_CHAR_SELECT","Выберите Символ");
define("INSTALLER_ERROR_API_SERVER_OFFLINE","Ошибка<br>EVE API сервера, если в оффлайне. Пожалуйста, попробуйте позже.");
define("INSTALLER_RUN_SETUP","Запуск установки");
define("INSTALLER_ERROR_NO_API_INFO","Вы должны предоставить API Инфо");
define("INSTALLER_PROGRESS","Прогресс");
define("INSTALLER_SETUP_DONE",'<h2>Настройка выполняется.</h2>' . PHP_EOL
                             .'<br />' . PHP_EOL
                             .'Теперь вы можете настроить на Cronjob yapeal.php кэшировал всех данных.<br />' . PHP_EOL
                             .'<h3>NOTICE: yapeal.php может\'т работать в веб-браузере.</h3>' . PHP_EOL);
define("INSTALLER_SETUP_FAILED",'<h2>Настройка не была завершена.</h2>' . PHP_EOL
                               .'<br />' . PHP_EOL
                               .'Вы, возможно, некоторые опечатки информация.<br />' . PHP_EOL);
define("INSTALLER_CREATE_FILE","Создание файла");
define("INSTALLER_CREATE_ERROR","не созданы или не является действительным INI-файл");
define("INSTALLER_CONNECT_TO","Подключение к");
define("INSTALLER_SELECT_DB","Выбор базы данных");
define("INSTALLER_CREATE_TABLE","Создать Стол");
define("INSTALLER_INSERT_INTO","Включить В");
define("INSTALLER_DROP_TABLE","Ронять Стол");
define("INSTALLER_CLOSE_CONNECTION","Закрыть Соединение");
define("INSTALLER_WAS_NOT_FOUND","не найден");
define("INSTALLER_SELECT_CHAR","Select CharacterВыберите Символ");
define("INSTALLER_NO_C_ACTION","Doing Nothing!");
define("INSTALLER_NO_C_ACTION_DES","You need to select another option than \"Do Nothing\"");
define("INSTALLER_MOVE_OLD_DATA","Move Old Data To");
define("INSTALLER_REMOVE_OLD_TABLES","Remove Old Tables");
define("INSTALLER_FROM_REVISION","From Revision: ");
/**
 * Yapeal Config Editor
 */
define("ED_UPDATE_DB","Обновление базы данных настроек");
define("ED_ACTION","Действий");
define("ED_DO_NOTHING","Ничего не делать");
define("ED_CLEAN_SETUP","Чистая установка");
define("ED_CLEAN_SETUP_DES","NOTICE:<br />Чтобы изменить имя базы данных или Префикс вам нужно использовать <font class=\"warning\">\"Чистая установка\"</font>.<br />Это будет allso удалить все данные!");
define("ED_ACCOUNT_INFO","Информация об аккаунте");
define("ED_CHAR_INFO","Характер информации");
define("ED_CORP_INFO","Corp информация");
define("ED_EVE_INFO","Ева Инфо");
define("ED_MAP_INFO","Карта информация");
define("ED_GET_INFO","Получить данные");
define("ED_DISABLE","Инвалиды");
define("ED_REMOVE_ALL_DATA","Отключение и удаление данных");
define("ED_DEBUGING","Отладка");
define("ED_ONLY_CHANGE_IF","Изменить только если вам нужно новоеe");
define("ED_UPDATE_CONFIG_TABLE","Обновление конфигурации");
define("ED_UPDATE_FILE","Обновление файловe");
define("ED_UPDATING_DONE",'<h2>Обновление сделано..</h2><br />');
define("ED_UPDATING_FAILED",'<h2>Данное обновление не было завершено..</h2><br />' . PHP_EOL
                           .'Вы, возможно, некоторые опечатки информация<br />');
define("ED_GO_TO_CONFIG","Перейти Config");
/**
 * Yapeal Config Editor
 */
define("UPD_NEW_UPDATE","New Update");
define("UPD_NEW_UPDATE_DES","There is a new update for your database.<br />" . PHP_EOL);
?>
