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
$_lang['mixedimage.remove_file'] = 'Удалять файл';
$_lang['mixedimage.remove_file_desc'] = 'ВНИМАНИЕ! При нажатии на кнопку Удалить - полностью удаляет файл с сервера';


$_lang['mixedimage.show_preview'] = 'Показать изображение';
$_lang['mixedimage.show_preview_desc'] = 'Отображать миниатюру изображений';
$_lang['mixedimage.prefix_filename'] = 'Использовать префикс как имя файла';
$_lang['mixedimage.resize'] = 'Параметры для ресайза изображений';
$_lang['mixedimage.resize_desc'] = 'Можно использовать параметры для phpthumb, например w=200&h=200&zc=1 <br> для наложения ватермарка: <br> <strong>fltr=wmt|Hello|60|C|ff0000| <br>fltr=wmi|/assets/wt.png|C|</strong>';

// TV Render
$_lang['mixedimage.upload_file'] = 'Загрузить файл';
$_lang['mixedimage.replace_file'] = 'Заменить файл';
$_lang['mixedimage.clear_file'] = 'Удалить';

// Errors
$_lang['mixedimage.error_tvid_ns'] = 'Ошибка: modTemplateVar ID не найден';
$_lang['mixedimage.error_tvid_invalid'] = 'Ошибка: неверное условие modTemplateVar';
$_lang['mixedimage.err_file_mime'] = 'Ошибка: неверный тип файла';

$_lang['mixedimage.error_remove'] = 'Ошибка при удалении (смотрите console)';

$_lang['mixedimage.err_file_ns'] = 'Ошибка: файл не был загружен';
$_lang['mixedimage.err_save_resource'] = 'Перед добавлением новых элементов, вам необходимо сохранить этот ресурс!';

// Settings
$_lang['setting_mixedimage.translit'] = 'Транслитерация файлов';
$_lang['setting_mixedimage.translit_desc'] = 'При включенной настройке, имена всех загружаемых файлов будут написаны транслитом. Настройка работает только при установленном дополнении "translit"';
$_lang['setting_mixedimage.check_resid'] = 'Загружать только при редактировании';
$_lang['setting_mixedimage.check_resid_desc'] = 'Пока ресурс не будет сохранен, файл не получится загрузить. Рекомендуется оставить включенным эту настройку. Иначе могут возникнуть проблемы при использовании плейсхолдеров {alias} и {palias} - у несохранных ресурсов они будут возвращать пустые значения.';
$_lang['setting_mixedimage.random_lenght'] = 'Длина строки для плейсхолдера {rand}';

// Trigger
$_lang['mixedimage.trigger_from_file_manager'] = 'Из уже загруженных';
$_lang['mixedimage.trigger_from_desktop'] = 'C вашего компьютера';
$_lang['mixedimage.trigger_clear'] = 'Очистить';
$_lang['mixedimage.trigger_remove'] = 'Очистить и удалить файл с сервера';


// success
$_lang['mixedimage.success_removed'] = 'Файл успешно удален';