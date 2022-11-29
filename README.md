# imagecreate
Генерация именных сертификатов, наложение текста на изображение


Для генерации именного сертификата (наложения текста на изображение) выполните следующие действия:
1. Загрузите файл на хостинг
2. Загрузите файлы необходимых Вам шрифтов в формате ttf на хостинг возле скрипта или в отдельную папку, которая находится возле скрипта
3. Добавьте в воронке внешний запрос на этот файл типом POST со следующим телом запроса:
```
{
"image":{
"url":"img.png"
},
"layers":[{
"type":"text",
"font":"/fonts/Montserrat-SemiBold.ttf",
"text":"Текст наложения",
"size":"50",
"x":"center",
"y":"700"
}]
}
```
4. На вкладке соответствия укажите в какую переменную сохранять ссылку на изображение $.image.url
Изображения сохраняются в папку images возле скрипта. Папка создается автоматически
5. Отправьте пользователю ссылку в тексте сообщения или само изображение с помощью API

Описание параметров тела запроса:
- `image` - массив с данными исходного изображения
- `image > url` - полная или относительная ссылка на изображение (гугл диск не подходит). В примере указана относительная ссылка, которая подразумевает. что изображение находится в той же папке, что и сам скрипт
- `layers` - массив с массивами информации о накладываемых слоях на изображение
- `layers >  > type` - тип накладываемого слоя (поддерживается только text)
- `layers >  > font` - путь к файлу шрифта относительно скрипта (должен начинатся на слеш (/)). В примере подразумевается, что возле скрипта имеется папка fonts, в которой находится файл шрифта Montserrat-SemiBold.ttf
- `layers >  > text` - накладываемый на изображение текст
- `layers >  > size` - размер шрифта накладываемого текста
- `layers >  > max_width` - максимальное количество символов в строке. При превышении произодится перенос в новую строку. Перенос производится по словам. Слово, которое не влезает полностью переносится на новую строку
- `layers >  > x` - отступ по горизонтали от левого края в пикселях (или "center" для центрирования)
- `layers >  > y` - отступ по вертикали от верхнего края в пикселях (или "center" для центрирования)
- `layers >  > angle` - угол наклона текста (0-360)
- `layers >  > color` - массив с информацией о цвете текста
- `layers >  > color > red` - интенсивность красного цвета (по цветовой схеме RGB)
- `layers >  > color > green` - интенсивность зеленого цвета (по цветовой схеме RGB)
- `layers >  > color > blue` - интенсивность синего цвета (по цветовой схеме RGB)
- `layers >  > color > alpha` - уровень прозрачности от 0 (непрозрачен) до 127 (полностью прозрачен)
