<?php

// Language definitions used in db_update.php

$lang_update = array(

'Update'						=>	'Обновить FluxBB',
'Update message'				=>	'Ваша база FluxBB устарела и должна быть обновлена перед тем как продолжить. Если вы Админ этого форума, пожалуйста следуйте инструкциям ниже чтобы завершить обновление.',
'Note'							=>	'Замечание:',
'Members message'				=>	'Это действие только для администраторов. Если вы простой пользователь, не волнуйтесь - форум вскоре продолжит работу!',
'Administrator only'			=>	'Это действие только для администраторов!',
'Database password info'		=>	'Чтобы провести обновление базы пожалуйста введите пароль с которым FluxBB был установлен. Если не помните, он сохранен в \'config.php\'.',
'Database password note'		=>	'Если вы работаете под SQLite (и пароль не используется) пожалуйста введите путь до базы. Он должен в точности совпасть с тем, что хранится в вашем файле конфигурации.',
'Database password'				=>	'Пароль базы',
'Next'							=>	'Далее',

'You are running error'			=>	'Вы работаете на %1$s версия %2$s. FluxBB %3$s необходим хотябы %1$s %4$s для корректной работы. Вам необходимо обновить установку %1$s чтобы продолжить.',
'Version mismatch error'		=>	'Версия не совпадает. База \'%s\' кажется не содержит схему FluxBB с которой может работать этот скрипт.',
'Invalid file error'			=>	'Неправильное имя файла. When using SQLite the database file name must be entered exactly as it appears in your \'%s\'',
'Invalid password error'		=>	'Invalid database password. To upgrade FluxBB you must enter your database password exactly as it appears in your \'%s\'',
'No password error'				=>	'No database password provided',
'Script runs error'				=>	'It appears the update script is already being ran by someone else. If this is not the case, please manually delete the file \'%s\' and try again',
'No update error'				=>	'Your forum is already as up-to-date as this script can make it',

'Intro 1'						=>	'This script will update your forum database. The update procedure might take anything from a second to hours depending on the speed of the server and the size of the forum database. Don\'t forget to make a backup of the database before continuing.',
'Intro 2'						=>	'Did you read the update instructions in the documentation? If not, start there.',
'No charset conversion'			=>	'<strong>IMPORTANT!</strong> FluxBB has detected that this PHP environment does not have support for the encoding mechanisms required to do UTF-8 conversion from character sets other than ISO-8859-1. What this means is that if the current character set is not ISO-8859-1, FluxBB won\'t be able to convert your forum database to UTF-8 and you will have to do it manually. Instructions for doing manual charset conversion can be found in the update instructions.',
'Enable conversion'				=>	'<strong>Enable conversion:</strong> When enabled this update script will, after it has made the required structural changes to the database, convert all text in the database from the current character set to UTF-8. This conversion is required if you\'re upgrading from version 1.2.',
'Current character set'			=>	'<strong>Current character set:</strong> If the primary language in your forum is English, you can leave this at the default value. However, if your forum is non-English, you should enter the character set of the primary language pack used in the forum. <em>Getting this wrong can corrupt your database so don\'t just guess!</em> Note: This is required even if the old database is UTF-8.',
'Charset conversion'			=>	'Charset conversion',
'Enable conversion label'		=>	'<strong>Enable conversion</strong> (perform database charset conversion).',
'Current character set label'	=>	'Current character set',
'Current character set info'	=>	'Accept default for English forums otherwise the character set of the primary language pack.',
'Start update'					=>	'Start update',
'Error converting users'		=>	'Error converting users',
'Error info 1'					=>	'There was an error converting some users. This can occur when converting from FluxBB v1.2 if multiple users have registered with very similar usernames, for example "bob" and "bГ¶b".',
'Error info 2'					=>	'Below is a list of users who failed to convert. Please choose a new username for each user. Users who are renamed will automatically be sent an email alerting them of the change.',
'New username'					=>	'New username',
'Required'						=>	'(Required)',
'Correct errors'				=>	'The following errors need to be corrected:',
'Rename users'					=>	'Rename users',
'Successfully updated'			=>	'Your forum database was successfully updated. You may now %s.',
'go to index'					=>	'go to the forum index',

'Unable to lock error'			=>	'Unable to write update lock. Please make sure PHP has write access to the directory \'%s\' and no-one else is currently running the update script.',

'Converting'					=>	'Converting %s вЂ¦',
'Converting item'				=>	'Converting %1$s %2$s вЂ¦',
'Preparsing item'				=>	'Preparsing %1$s %2$s вЂ¦',
'Rebuilding index item'			=>	'Rebuilding index for %1$s %2$s',

'ban'							=>	'ban',
'categories'					=>	'categories',
'censor words'					=>	'censor words',
'configuration'					=>	'configuration',
'forums'						=>	'forums',
'groups'						=>	'groups',
'post'							=>	'post',
'ranks'							=>	'ranks',
'report'						=>	'report',
'topic'							=>	'topic',
'user'							=>	'user',
'signature'						=>	'signature',

'Username too short error'		=>	'Usernames must be at least 2 characters long. Please choose another (longer) username.',
'Username too long error'		=>	'Usernames must not be more than 25 characters long. Please choose another (shorter) username.',
'Username Guest reserved error'	=>	'The username guest is reserved. Please choose another username.',
'Username IP format error'		=>	'Usernames may not be in the form of an IP address. Please choose another username.',
'Username bad characters error'	=>	'Usernames may not contain all the characters \', " and [ or ] at once. Please choose another username.',
'Username BBCode error'			=>	'Usernames may not contain any of the text formatting tags (BBCode) that the forum uses. Please choose another username.',
'Username duplicate error'		=>	'Someone is already registered with the username %s. The username you entered is too similar. The username must differ from that by at least one alphanumerical character (a-z or 0-9). Please choose a different username.',

'JavaScript disabled'			=>	'JavaScript seems to be disabled. %s.',
'Click here to continue'		=>	'Click here to continue',
'Required field'				=>	'is a required field in this form.'

);
