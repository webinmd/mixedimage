{literal}
<ul style="margin-top: -15px">
    <li><span>clear</span>    - очистка поля. Если указан параметр "Удалять файл", то помимо очистки поля, происходит удаление файла с сервера</li>
    <li><span>manager</span>  - позволяет выбрать файл из файлового менеджера MODx</li>
    <li><span>pc</span>  - позволяет загружать файл сразу с компьютера пользователя, миную файловый менеджер MODx</li>
    <li><span>url</span> - позволяет загружать файл по ссылке</li>
</ul>
<br><br>

<h3>Динамические параметры маршрутов</h3>
<h4>Использование замещающегося текста (Placeholders)</h4>
<p>Путь к сохраненному файлу и дополнительный префикс имени файла могут быть настроены динамически с несколькими заполнителями:
    <ul>
        <li><span>{id}</span>     - ID ресурса</li>
        <li><span>{pid}</span>    - ID ресурса родителя</li>
        <li><span>{alias}</span>  - Алиас ресурса</li>
        <li><span>{palias}</span> - Алиас ресурса родителя</li>
        <li><span>{context}</span>- Ключ контекста (context_key)</li>
        <li><span>{tid}</span>    - ID доп. поля (tv)</li>
        <li><span>{uid}</span>    - ID юзера</li>
        <li><span>{rand}</span>   - Случайная строка (количество символов указывается в системынх настройках)</li>
        <li><span>{t}</span>      - Время в формате timestamp</li>
        <li><span>{y}</span>      - Год</li>
        <li><span>{m}</span>      - Месяц</li>
        <li><span>{d}</span>      - День</li>
        <li><span>{h}</span>      - Час</li>
        <li><span>{i}</span>      - Минута</li>
        <li><span>{s}</span>      - Секунда</li>
    </ul>
</p>
<h4>Ограничение типов файлов, используя MIME</h4>
<p>Опишите через запятую MIME-типы, которые могут быть загружены.</p>
<p>Например, <code>image/jpeg, image/png, application/pdf</code></p>
<p>Полный список MIME-типов можно найти <a href="http://webdesign.about.com/od/multimedia/a/mime-types-by-file-extension.htm" target="_blank">здесь</a>.</p>
{/literal}