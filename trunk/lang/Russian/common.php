<?php

/*
// Determine what locale to use
switch (PHP_OS)
{
	case 'WINNT':
	case 'WIN32':
		$locale = 'russian';
		break;

	case 'FreeBSD':
	case 'NetBSD':
	case 'OpenBSD':
		$locale = 'ru_RU.UTF-8';
		break;

	default:
		$locale = 'ru_RU';
		break;
}

// Attempt to set the locale
setlocale(LC_CTYPE, $locale);
*/

// Language definitions for frequently used strings
$lang_common = array(

// Text orientation and encoding
'lang_direction'		=>	'ltr',	// ltr (Left-To-Right) or rtl (Right-To-Left)

// Number formatting
'lang_decimal_point'				=>	'.',
'lang_thousands_sep'				=>	',',

// Notices
'Bad request'			=>	'Неверный запрос. Ссылка ошибочная или устарела.',
'No view'				=>	'У вас нет прав на просмотр этого форума.',
'No permission'			=>	'У вас нет прав на просмотр этой страницы.',
'Bad referrer'			=>	'Плохой HTTP_REFERER. Вы перешли на эту страницу из неавторизованного источника. Если проблема постоянная, убедитесь, что \'Base URL\' верно прописан в Admin/Options и что вы посещаете форум именно по такому URL. Дополнительную информацию вы можете получить из документации FluxBB.',

// Topic/forum indicators
'New icon'				=>	'Нет новых сообщений',
'Normal icon'			=>	'<!-- -->',
'Closed icon'			=>	'Эта тема закрыта',
'Redirect icon'			=>	'Переадресованный форум',

// Miscellaneous
'Announcement'			=>	'Объявление',
'Options'				=>	'Параметры',
'Actions'				=>	'Действия',
'Submit'				=>	'Отправить',	// "name" of submit buttons
'Ban message'			=>	'Вы забанены.',
'Ban message 2'			=>	'Бан заканчивается',
'Ban message 3'			=>	'Админ или модератор забанили вас с такой формулировкой:',
'Ban message 4'			=>	'Все вопросы отправляйте администратору форума по адресу',
'Never'					=>	'Никогда',
'Today'					=>	'Сегодня',
'Yesterday'				=>	'Вчера',
'Info'					=>	'Инфо',		// a common table header
'Go back'				=>	'Назад',
'Maintenance'			=>	'Обслуживание',
'Redirecting'			=>	'Перенаправление',
'Click redirect'		=>	'Кликните здесь если вы не желаете ждать (или ваш браузер не поддерживает перенаправление)',
'on'					=>	'вкл',		// as in "BBCode is on"
'off'					=>	'выкл',
'Invalid e-mail'		=>	'Вы ввели неправильный e-mail.',
'required field'		=>	'необходимое поле в этой форме.',	// for javascript form validation
'Last post'				=>	'Последнее сообщение',
'by'					=>	'от',	// as in last post by someuser
'New posts'				=>	'Новые&nbsp;сообщения',	// the link that leads to the first new post (use &nbsp; for spaces)
'New posts info'		=>	'Перейти к новому сообщению в этой теме.',	// the popup text for new posts links
'Username'				=>	'Имя',
'Password'				=>	'Пароль',
'E-mail'				=>	'E-mail',
'Send e-mail'			=>	'Отправить e-mail',
'Moderated by'			=>	'Модерируется',
'Registered'			=>	'Здесь с',
'Subject'				=>	'Заголовок темы',
'Message'				=>	'Сообщение',
'Topic'					=>	'Тема',
'Forum'					=>	'Форум',
'Posts'					=>	'Сообщений',
'Replies'				=>	'Ответов',
'Author'				=>	'Автор',
'Pages'					=>	'Страницы',
'BBCode'				=>	'BBCode',	// You probably shouldn't change this
'img tag'				=>	'[img] tag',
'Smilies'				=>	'Смайлики',
'and'					=>	'и',
'Image link'			=>	'картинка',	// This is displayed (i.e. <image>) instead of images when "Show images" is disabled in the profile
'wrote'					=>	'пишет',	// For [quote]'s
'Code'					=>	'Код',		// For [code]'s
'Mailer'				=>	'Отправитель',	// As in "MyForums Mailer" in the signature of outgoing e-mails
'Important information'	=>	'Важная информация',
'Write message legend'	=>	'Введите сообщение и нажмите Отправить',
'Previous'				=>	'Назад',
'Next'					=>	'Вперед',
'Spacer'				=>	'&hellip;', // Ellipsis for paginate

// Title
'Title'					=>	'Титул',
'Member'				=>	'Участник',	// Default title
'Moderator'				=>	'Модератор',
'Administrator'			=>	'Администратор',
'Banned'				=>	'Забанен',
'Guest'					=>	'Гость',

// Stuff for include/parser.php
'BBCode error 1'		=>	'тег [/%1$s] присутствует без парного [%1$s]',
'BBCode error 1'		=>	'Пропущен открывающий тег для [/quote].',
'BBCode error 2'		=>	'Пропущен закрывающий тег для [code].',
'BBCode error 3'		=>	'Пропущен открывающий тег для [/code].',
'BBCode error 4'		=>	'Пропущен по крайней мере один закрывающий тег для [quote].',
'BBCode error 5'		=>	'Пропущен по крайней мере один открывающий тег для [/quote].',
'BBCode nested list'	=>	'теги [list] не могут быть вложенными',
'BBCode code problem'	=>	'Проблемы с вашими тегами [code]',

// Stuff for the navigator (top of every page)
'Index'					=>	'Список',
'User list'				=>	'Пользователи',
'Rules'					=>  'Правила',
'Search'				=>  'Поиск',
'Register'				=>  'Регистрация',
'Login'					=>  'Вход',
'Not logged in'			=>  'Вы не вошли.',
'Profile'				=>	'Профиль',
'Files'					=>	'Файлы',
'Logout'				=>	'Выход',
'Logged in as'			=>	'Вошли как',
'Admin'					=>	'Администрирование',
'Last visit'			=>	'Последний визит',
'Show new posts'		=>	'Новые сообщения',
'Mark all as read'		=>	'Пометить всё как прочтённое',
'Mark forum read'		=>	'Пометить форум как прочтённый',
'Link separator'		=>	'',	// The text that separates links in the navigator

// Stuff for the page footer
'Board footer'			=>	'Подвал форума',
'Search links'			=>	'Поисковые ссылки',
'Show recent posts'		=>	'Активные темы',
'Show unanswered posts'	=>	'Темы без ответов',
'Show your posts'		=>	'Показать ваши сообщения',
'Show subscriptions'	=>	'Показать темы из вашей подписки',
'Jump to'				=>	'Перейти',
'Go'					=>	' Иди ',		// submit button in forum jump
'Moderate topic'		=>	'Модерировать тему',
'Move topic'			=>  'Перенести тему',
'Open topic'			=>  'Открыть тему',
'Close topic'			=>  'Закрыть тему',
'Unstick topic'			=>  'Отклеить тему',
'Stick topic'			=>  'Приклеить тему',
'Moderate forum'		=>	'Модерировать форум',
'Delete posts'			=>	'Удалить несколько сообщений', // Deprecated
'Powered by'			=>	'Powered by %s',

// Debug information
'Debug table'			=>	'Отладочная информация',
'Querytime'				=>	'Generated in %1$s seconds, %2$s queries executed',
'Query times'			=>	'Time (s)',
'Query'					=>	'Query',
'Total query time'		=>	'Total query time: %s',

// Email related notifications
'New user notification'				=>	'Оповещение - Новая регистрация',
'New user message'					=>	'Пользователь \'%s\' зарегистрировался на форуме в %s',
'Banned email notification'			=>	'Оповещение - Обнаружен забаненный e-mail',
'Banned email register message'		=>	'Пользователь \'%s\' зарегистрировался с забаненным e-mail: %s',
'Banned email change message'		=>	'Пользователь \'%s\' сменил e-mail на забаненный адрес: %s',
'Duplicate email notification'		=>	'Оповещение - Обнаружен повторяющийся e-mail',
'Duplicate email register message'	=>	'Пользователь \'%s\' зарегистрировался с e-mail, который также принадлежит: %s',
'Duplicate email change message'	=>	'Пользователь \'%s\' сменил e-mail на адрес, который уже принадлежит: %s',
'Report notification'				=>	'Сигнал(%d) - \'%s\'',
'Report message 1'					=>	'Пользователь \'%s\' оставил следующий сигнал: %s',
'Report message 2'					=>	'Причина: %s',

'User profile'						=>	'Профиль пользователя: %s',
'Email signature'					=>	'Почтовый робот'."\n".'(Не отвечайте на это сообщение)',

// For extern.php RSS feed
'RSS description'					=>	'Самые свежие темы на %s.',
'RSS description topic'				=>	'Самые свежие сообщения в %s.',
'RSS reply'							=>	'Re: '	// The topic subject will be appended to this string (to signify a reply)

);
