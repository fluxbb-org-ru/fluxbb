<?php

// Language definitions used in admin_bans.php
$lang_admin_bans = array(

'No user message'           =>  'Нет пользователя с таким именем. Если хотите добавить бан без привязки к имени, просто оставьте имя пользователя пустым.',
'No user ID message'        =>  'Нет зарегистрированного пользователя с таким ID.',
'User is admin message'     =>  '%s администратор и не может быть забанен. Если хотите забанить Админа, сначала разжалуйте его до Пользователя.',
'User is mod message'		=>	'%s модератор и не может быть забанен. Если хотите забанить Модератора, сначала разжалуйте его до Пользователи.',
'Must enter message'        =>  'Вы должны ввести имя пользователя, IP или email (хотя бы что-нибудь).',
'Cannot ban guest message'  =>  'Гостя нельзя забанить.',
'Invalid IP message'        =>  'Вы вели неверный IP или IP-диапазон.',
'Invalid e-mail message'    =>  'Email (т.е. user@domain.com) или доменная часть (т.е. domain.com) введена неверно.',
'Invalid date message'      =>  'Вы ввели неправильную дату окончания.',
'Invalid date reasons'      =>  'Дата должна быть в формате YYYY-MM-DD и должна быть не ранее, чем завтрашнее число.',
'Ban added redirect'        =>  'Бан добавлен. Переадресация …' ,
'Ban edited redirect'       =>  'Бан изменен. Переадресация …',
'Ban removed redirect'      =>  'Бан удален. Переадресация …',

'New ban head'              =>  'Новый бан',
'Add ban subhead'           =>  'Добавление бана',
'Username label'            =>  'Пользователь',
'Username help'             =>  'Имя пользователя (регистр неважен).',
'Username advanced help'    =>  'Имя пользователя (регистр неважен). На следующей странице вы сможете ввести IP и email. Если хотите забанить IP/IP-диапазон или email просто оставьте поле пустым.',

'Ban search head'           =>  'Поиск бана',
'Ban search subhead'        =>  'Введите критерии поиска',
'Ban search info'           =>  'Поиск бана по базе. Вы можете ввести одно или несколько условий. Возможны маски в виде звездочки (*). Чтобы увидеть все баны оставьте поля пустыми.',
'Date help'                 =>  '(yyyy-mm-dd)',
'Message label'             =>  'Сообщение',
'Expire after label'        =>  'Устаревает после',
'Expire before label'       =>  'Устаревает до',
'Order by label'            =>  'Сортировать по',
'Order by username'         =>  'Имени пользователя',
'Order by ip'               =>  'IP',
'Order by e-mail'           =>  'Email',
'Order by expire'           =>  'Дата устаревания',
'Ascending'                 =>  'По возрастанию',
'Descending'                =>  'По убыванию',
'Submit search'             =>  'Искать',

'E-mail label'              =>  'Email',
'E-mail help'               =>  'Email или целый домен, который вы будете банить (т.е. someone@somewhere.com или somewhere.com). См. опцию "Разрешить забаненные email" в разделе Права.',
'IP label'                  =>  'IP адрес/IP-подсеть',
'IP help'                   =>  'IP или IP-подсеть, которую вы будете банить (т.е. 150.11.110.1 или 150.11.110). Можно перечислить несколько адресов через пробел. Если поле IP уже заполнено, то это последний из использованных IP данного пользователя.',
'IP help link'              =>  'Кликните %s чтобы посмотреть статистику IP этого пользователя.',
'Ban advanced head'         =>  'Расширенные настройки',
'Ban advanced subhead'      =>  'Дополнительно банить IP и email',
'Ban message label'         =>  'Сообщение забаненному',
'Ban message help'          =>  'Этот текст будет показан забаненному пользователю, когда он/она посетит форум.',
'Message expiry subhead'    =>  'Сообщение и срок действия',
'Ban IP range info'         =>  'Будьте предельно осторожны с IP-диапазонами, потому что под это правило могут попасть несколько пользователей.',
'Expire date label'         =>  'Дата окончания',
'Expire date help'          =>  'Дата, когда бан автоматически снимется (формат: yyyy-mm-dd). Оставьте пустым если бан можно снять только вручную.',

'Results head'              =>  'Результаты поиска',
'Results username head'     =>  'Пользователь',
'Results e-mail head'       =>  'Email',
'Results IP address head'   =>  'IP/IP-диапазон',
'Results expire head'       =>  'Окончание',
'Results message head'      =>  'Сообщение',
'Results banned by head'    =>  'Кем забанен',
'Results actions head'      =>  'Действия',
'No match'                  =>  'Не найдено',
'Unknown'                   =>  'Неизвестно',

);
