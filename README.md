
Инструкция для размещения кода на веб-площадках.
==========

Внимание! 
---------
Для подключения к системе необходимо, чтобы ваш веб-сервер поддерживал PHP версии 5.3 или выше с установленным расширением curl. Напоминаем, что сопровождение версий 5.2 и 4.х остановлено разработчиками PHP.

Инструкция по установке кода:
---------

 * Скачайте и разместите в корневой директории вашего сайта классы, необходимые для работы нашего кода.
 [WebWapInterfaceChooser](https://github.com/WapStart/plus1-web-sdk/blob/master/WebWapInterfaceChooser.class.php) [Plus1BannerAsker](https://github.com/WapStart/plus1-web-sdk/blob/master/Plus1BannerAsker.class.php) [WebPlus1BannerAsker](https://github.com/WapStart/plus1-web-sdk/blob/master/WebPlus1BannerAsker.class.php)

 * Разместите в начале каждой из страниц, на которых будет демонстрироваться реклама, код из блока 1. Этот код подключает класс для показа баннеров Plus1 WapStart и пытается установить пользователю cookie. Код должен стоять ДО любого вывода информации пользователю.

```php
    //Рекламная сеть Plus1 WapStart, тех поддержка clientsupport@co.wapstart.ru
    $Plus1Root=$_SERVER['DOCUMENT_ROOT'];
    $Plus1=$Plus1Root.'/Plus1BannerAsker.class.php';

    // Вызов класса, отвечающего за показ объявлений сети 'Plus1' Wapstart
    // Внимание: этот вызов должен идти ДО вывода
    // Показ рекламных объявлений сети 'Plus1' Wapstart
    // любой информации пользователю (отправки заголовков)
    require_once($Plus1);

    // Вызов класса, отвечающего за определение типа запроса (web/wap)
    $Plus1InterfaceChooser=$Plus1Root.'/WebWapInterfaceChooser.class.php';
    require_once($Plus1InterfaceChooser);

    // Вызов класса, отвечающего за показ объявлений сети 'Plus1' Wapstart
    $WebPlus1=$Plus1Root.'/WebPlus1BannerAsker.class.php';
    require_once($WebPlus1);
```

 * Разместите код показа рекламных объявлений (блок 2) в теле страниц в тех местах, на которых планируется показ рекламных объявлений.

```php
    // Показ рекламных объявлений сети 'Plus1' Wapstart
    echo WebPlus1BannerAsker::create() ->setId(3167) ->fetch();
```

