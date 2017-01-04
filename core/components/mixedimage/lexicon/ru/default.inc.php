<?php

$_lang['mixedimage'] = 'Смешанная загрузка файла';

// TV Input Properties
$_lang['mixedimage.save_path'] = 'Путь сохранения';
$_lang['mixedimage.save_path_desc'] = 'Путь для сохранения относительно корня медиа-ресурса';
$_lang['mixedimage.file_prefix'] = 'Префикс имени файла';
$_lang['mixedimage.file_prefix_desc'] = 'Дополнительный префикс имени файла для загрузки файлов. Например {y}-{m}-{d}-';
$_lang['mixedimage.mime_types'] = 'Принимаемые типы MIME';
$_lang['mixedimage.mime_types_desc'] = 'Дополнительный список разделенных запятыми типов MIME';
$_lang['mixedimage.show_value'] = 'Показывать значение TV';
$_lang['mixedimage.show_value_desc'] = 'Отображать путь к файлу';
$_lang['mixedimage.show_preview'] = 'Показать изображение';
$_lang['mixedimage.show_preview_desc'] = 'Отображать миниатюру изображений';
$_lang['mixedimage.prefix_filename'] = 'Использовать префикс как имя файла';

// TV Render
$_lang['mixedimage.upload_file'] = 'Загрузить файл';
$_lang['mixedimage.replace_file'] = 'Заменить файл';
$_lang['mixedimage.clear_file'] = 'Удалить';

// Errors
$_lang['mixedimage.error_tvid_ns'] = 'Ошибка: modTemplateVar ID не обеспечивается';
$_lang['mixedimage.error_tvid_invalid'] = 'Ошибка: неверное условие modTemplateVar';
$_lang['mixedimage.err_file_mime'] = 'Ошибка: неверный тип файла';

$_lang['mixedimage.err_file_ns'] = 'Ошибка: файл не был загружен';
$_lang['mixedimage.err_save_resource'] = 'Перед добавлением новых элементов, вам необходимо сохранить этот ресурс!';

// Settings
$_lang['setting_mixedimage.translit'] = 'Транслитерация файлов';
$_lang['setting_mixedimage.translit_desc'] = 'При включенной настройке, имена всех загружаемых файлов будут написаны транслитом. Настройка работает только при установленном дополнении "translit"';
$_lang['setting_mixedimage.check_resid'] = 'Загружать только при редактировании';
$_lang['setting_mixedimage.check_resid_desc'] = 'Пока ресурс не будет сохранен, файл не получится загрузить. Рекомендуется оставить включенным эту настройку. Иначе могут возникнуть проблемы при использовании плейсхолдеров {alias} и {palias} - у несохранных ресурсов они будут возвращать пустые значения.';

// Trigger
$_lang['mixedimage.trigger_from_file_manager'] = 'Из уже загруженных';
$_lang['mixedimage.trigger_from_desktop'] = 'C вашего компьютера';
$_lang['mixedimage.trigger_clear'] = 'Очистить';